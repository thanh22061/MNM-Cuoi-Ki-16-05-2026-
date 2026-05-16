<?php
/**
 * Early page cache for anonymous BeautyStore traffic.
 *
 * WordPress loads this file before plugins when WP_CACHE is enabled.
 */

defined( 'ABSPATH' ) || exit;

if ( PHP_SAPI === 'cli' ) {
	return;
}

if ( ! function_exists( 'beautystore_cache_ignored_query_params' ) ) {
	function beautystore_cache_ignored_query_params(): array {
		return array(
			'utm_source',
			'utm_medium',
			'utm_campaign',
			'utm_term',
			'utm_content',
			'utm_id',
			'utm_name',
			'gclid',
			'fbclid',
			'msclkid',
			'ttclid',
			'yclid',
			'igshid',
			'mc_cid',
			'mc_eid',
			'ref',
			'ref_src',
			'srsltid',
		);
	}
}

if ( ! function_exists( 'beautystore_cache_strip_ignored_query_params' ) ) {
	function beautystore_cache_strip_ignored_query_params( array &$query ): void {
		foreach ( beautystore_cache_ignored_query_params() as $param ) {
			unset( $query[ $param ] );
		}
	}
}

if ( ! function_exists( 'beautystore_cache_normalize_request_uri' ) ) {
	function beautystore_cache_normalize_request_uri( string $request_uri ): string {
		$parts = explode( '?', $request_uri, 2 );
		$path   = $parts[0] ?: '/';

		if ( ! isset( $parts[1] ) || '' === $parts[1] ) {
			return $path;
		}

		$query = array();
		parse_str( $parts[1], $query );
		beautystore_cache_strip_ignored_query_params( $query );

		if ( empty( $query ) ) {
			return $path;
		}

		ksort( $query );

		return $path . '?' . http_build_query( $query, '', '&', PHP_QUERY_RFC3986 );
	}
}

if ( ! function_exists( 'beautystore_advanced_cache_try_serve' ) ) {
	function beautystore_advanced_cache_try_serve(): void {
		if ( ! beautystore_advanced_cache_is_public_request() ) {
			return;
		}

		$file = beautystore_advanced_cache_file();
		if ( ! is_file( $file ) ) {
			return;
		}

		$ttl = 600;
		$mtime = filemtime( $file );
		if ( false === $mtime || $mtime + $ttl < time() ) {
			return;
		}

		$html = file_get_contents( $file );
		if ( false === $html || '' === $html ) {
			return;
		}

		$html = str_replace( '__BEAUTYSTORE_CLIENT_IP__', beautystore_advanced_cache_client_ip(), $html );

		if ( ! headers_sent() ) {
			header( 'Content-Type: text/html; charset=UTF-8' );
			header( 'X-BeautyStore-Cache: HIT' );
			header( 'Cache-Control: public, max-age=300' );
		}

		if ( 'HEAD' !== ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) {
			echo $html;
		}

		exit;
	}

	function beautystore_advanced_cache_is_public_request(): bool {
		$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
		if ( 'GET' !== $method && 'HEAD' !== $method ) {
			return false;
		}

		if ( beautystore_advanced_cache_has_private_cookie() ) {
			return false;
		}

		$uri = $_SERVER['REQUEST_URI'] ?? '';
		if ( preg_match( '#/(wp-admin|wp-login\.php|wp-cron\.php|xmlrpc\.php|wp-json|wp-admin/admin-ajax\.php)#i', $uri ) ) {
			return false;
		}

		$query_string = $_SERVER['QUERY_STRING'] ?? '';
		if ( '' === $query_string ) {
			return true;
		}

		parse_str( $query_string, $query );
		beautystore_cache_strip_ignored_query_params( $query );

		if ( empty( $query ) ) {
			return true;
		}

		$blocked = array( 'add-to-cart', 'remove_item', 'apply_coupon', 'coupon_code', 'wc-ajax', 'rest_route', 'preview', 'customize_changeset_uuid', 's', 'feed' );
		foreach ( $blocked as $key ) {
			if ( array_key_exists( $key, $query ) ) {
				return false;
			}
		}

		$allowed = array( 'page_id', 'p', 'post_type', 'product', 'product_cat', 'product_tag', 'cat', 'paged' );
		foreach ( array_keys( $query ) as $key ) {
			if ( ! in_array( $key, $allowed, true ) ) {
				return false;
			}
		}

		if ( isset( $query['post_type'] ) && 'product' !== $query['post_type'] ) {
			return false;
		}

		foreach ( array( 'page_id', 'p', 'cat', 'paged' ) as $numeric_key ) {
			if ( isset( $query[ $numeric_key ] ) && ! ctype_digit( (string) $query[ $numeric_key ] ) ) {
				return false;
			}
		}

		return true;
	}

	function beautystore_advanced_cache_has_private_cookie(): bool {
		foreach ( array_keys( $_COOKIE ) as $name ) {
			if ( preg_match( '/^(wordpress_logged_in_|wordpress_sec_|wp-postpass_|comment_author_|woocommerce_items_in_cart|woocommerce_cart_hash|wp_woocommerce_session_)/', $name ) ) {
				return true;
			}
		}

		return false;
	}

	function beautystore_advanced_cache_file(): string {
		$host = strtolower( $_SERVER['HTTP_HOST'] ?? 'localhost' );
		$uri  = beautystore_cache_normalize_request_uri( $_SERVER['REQUEST_URI'] ?? '/' );
		$key  = hash( 'sha256', $host . '|' . $uri );

		return dirname( __FILE__ ) . '/cache/beautystore-page-cache/' . $key . '.html';
	}

	function beautystore_advanced_cache_client_ip(): string {
		if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			return (string) $_SERVER['HTTP_CF_CONNECTING_IP'];
		}

		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$parts = explode( ',', (string) $_SERVER['HTTP_X_FORWARDED_FOR'] );
			return trim( $parts[0] );
		}

		return (string) ( $_SERVER['REMOTE_ADDR'] ?? '' );
	}
}

beautystore_advanced_cache_try_serve();
