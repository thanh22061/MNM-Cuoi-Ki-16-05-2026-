<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'myweb' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '/b8|[eO M!S661!n;6z?>;}8~]F@DC0JhXl^+&&#s]O6zkkd#^pnAb[kqX,9a.)Y' );
define( 'SECURE_AUTH_KEY',  'x)=}e]qYSLdA+c76_Rs2J[vZweD.2O.2w:>NDc1:4*$PL~<6rD[7&|zya23Vo^ke' );
define( 'LOGGED_IN_KEY',    'B5#!+=+{pyoM$5DY 9}:}X){l.7Kc4j9AU4ULloK//?-p?r_C1:Xiq1k6L?Vi>]W' );
define( 'NONCE_KEY',        '!h`i,}$hFmQ`5oTKOQ2n+55SX{&l~g]1/XlIr]4:a]l;)$kwb%[Fz4wx%7Xz h,O' );
define( 'AUTH_SALT',        '>[/l]vxP@Bk![*Z!()~Sf<{j[%a Q{$s#JC{Ml/1fa13W2>++090U+;D#t``n1q+' );
define( 'SECURE_AUTH_SALT', 'Ie~ry Aq,mmqYhZqhz+@f%;=S hq_g&_wJTk1o9Du3i#MzN+?tN,))8O=H=>Y])Q' );
define( 'LOGGED_IN_SALT',   '/~-/G[h.CVp1ek~Sr.<V.Tx$]*^HfWN8A[BIa.`heW0!`OKr]HnKCKjY/oAFE-V.' );
define( 'NONCE_SALT',       '-kBk%gN&3si-Fe#H9eA>O8dy=ePXQ7W[9o:_z/@lv0B&y$G<xRey E<:U+mY^!ia' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain_name = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
define( 'WP_HOME', $protocol . $domain_name . '/myweb' );
define( 'WP_SITEURL', $protocol . $domain_name . '/myweb' );
define( 'WP_CACHE', true );
define( 'WP_MEMORY_LIMIT', '192M' );
define( 'WP_MAX_MEMORY_LIMIT', '256M' );
define( 'DISABLE_WP_CRON', true );
define( 'AUTOSAVE_INTERVAL', 120 );
define( 'WP_POST_REVISIONS', 3 );
define( 'EMPTY_TRASH_DAYS', 3 );
define( 'WP_CRON_LOCK_TIMEOUT', 60 );


/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
