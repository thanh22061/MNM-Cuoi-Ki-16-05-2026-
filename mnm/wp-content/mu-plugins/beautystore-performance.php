<?php
/**
 * Front-end performance tweaks for BeautyStore.
 *
 * This MU plugin keeps small WordPress defaults from loading on public pages.
 */

defined( 'ABSPATH' ) || exit;

const BEAUTYSTORE_PAGE_CACHE_TTL = 600;

add_filter( 'woocommerce_admin_disabled', '__return_true' );

add_filter(
	'heartbeat_settings',
	function ( array $settings ): array {
		$settings['interval'] = 60;

		return $settings;
	}
);

add_action(
	'init',
	function () {
		if ( is_admin() ) {
			return;
		}

		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );

		add_filter( 'emoji_svg_url', '__return_false' );
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( is_admin() ) {
			return;
		}

		wp_dequeue_script( 'wp-embed' );
		wp_deregister_script( 'wp-embed' );
		wp_dequeue_script( 'heartbeat' );
		wp_deregister_script( 'heartbeat' );

		if ( ! is_user_logged_in() ) {
			wp_dequeue_style( 'dashicons' );
			wp_deregister_style( 'dashicons' );
		}

		if (
			function_exists( 'is_woocommerce' )
			&& ! is_cart()
			&& ! is_checkout()
			&& ! is_account_page()
		) {
			wp_dequeue_script( 'wc-cart-fragments' );
			wp_deregister_script( 'wc-cart-fragments' );
		}
	},
	100
);

add_action(
	'template_redirect',
	function () {
		if ( ! beautystore_page_cache_is_cacheable() ) {
			return;
		}

		ob_start( 'beautystore_page_cache_store' );
	},
	0
);

function beautystore_page_cache_is_cacheable(): bool {
	if ( is_admin() || is_user_logged_in() ) {
		return false;
	}

	$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
	if ( 'GET' !== $method && 'HEAD' !== $method ) {
		return false;
	}

	if ( beautystore_page_cache_has_private_cookie() || defined( 'DONOTCACHEPAGE' ) ) {
		return false;
	}

	if ( is_404() || is_search() || is_preview() || is_feed() || is_robots() || post_password_required() ) {
		return false;
	}

	if (
		function_exists( 'is_cart' )
		&& ( is_cart() || is_checkout() || is_account_page() )
	) {
		return false;
	}

	$query = $_GET;
	if ( function_exists( 'beautystore_cache_strip_ignored_query_params' ) ) {
		beautystore_cache_strip_ignored_query_params( $query );
	}
	foreach ( array( 'add-to-cart', 'remove_item', 'apply_coupon', 'coupon_code', 'wc-ajax', 'rest_route', 'preview', 'customize_changeset_uuid', 's', 'feed' ) as $key ) {
		if ( array_key_exists( $key, $query ) ) {
			return false;
		}
	}

	return true;
}

function beautystore_page_cache_has_private_cookie(): bool {
	foreach ( array_keys( $_COOKIE ) as $name ) {
		if ( preg_match( '/^(wordpress_logged_in_|wordpress_sec_|wp-postpass_|comment_author_|woocommerce_items_in_cart|woocommerce_cart_hash|wp_woocommerce_session_)/', $name ) ) {
			return true;
		}
	}

	return false;
}

function beautystore_page_cache_store( string $html ): string {
	if ( headers_sent() || http_response_code() !== 200 || strlen( $html ) < 2048 || false === stripos( $html, '</html>' ) ) {
		return $html;
	}

	$cache_html = preg_replace( '/("user_ip"\s*:\s*")[^"]*(")/', '$1__BEAUTYSTORE_CLIENT_IP__$2', $html );
	if ( ! is_string( $cache_html ) ) {
		$cache_html = $html;
	}

	$dir = beautystore_page_cache_dir();
	if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
		return $html;
	}

	$file = beautystore_page_cache_file();
	$tmp  = $file . '.' . getmypid() . '.tmp';

	if ( false !== file_put_contents( $tmp, $cache_html, LOCK_EX ) ) {
		rename( $tmp, $file );
	}

	if ( ! headers_sent() ) {
		header( 'X-BeautyStore-Cache: MISS' );
	}

	return $html;
}

function beautystore_page_cache_dir(): string {
	return WP_CONTENT_DIR . '/cache/beautystore-page-cache';
}

function beautystore_page_cache_file(): string {
	$host = strtolower( $_SERVER['HTTP_HOST'] ?? 'localhost' );
	$uri  = $_SERVER['REQUEST_URI'] ?? '/';
	if ( function_exists( 'beautystore_cache_normalize_request_uri' ) ) {
		$uri = beautystore_cache_normalize_request_uri( $uri );
	}
	$key  = hash( 'sha256', $host . '|' . $uri );

	return beautystore_page_cache_dir() . '/' . $key . '.html';
}

function beautystore_page_cache_purge(): void {
	$dir = beautystore_page_cache_dir();
	if ( ! is_dir( $dir ) ) {
		return;
	}

	foreach ( glob( $dir . '/*.html' ) ?: array() as $file ) {
		if ( is_file( $file ) ) {
			unlink( $file );
		}
	}
}

add_action( 'save_post', 'beautystore_page_cache_purge' );
add_action( 'deleted_post', 'beautystore_page_cache_purge' );
add_action( 'created_term', 'beautystore_page_cache_purge' );
add_action( 'edited_term', 'beautystore_page_cache_purge' );
add_action( 'delete_term', 'beautystore_page_cache_purge' );
add_action( 'customize_save_after', 'beautystore_page_cache_purge' );
add_action( 'switch_theme', 'beautystore_page_cache_purge' );
add_action( 'upgrader_process_complete', 'beautystore_page_cache_purge' );
add_action( 'woocommerce_update_product', 'beautystore_page_cache_purge' );
add_action( 'woocommerce_delete_product_transients', 'beautystore_page_cache_purge' );
