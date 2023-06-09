<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'feriorco_wp6891' );

/** Database username */
define( 'DB_USER', 'feriorco_wp6891' );

/** Database password */
define( 'DB_PASSWORD', '19FeriorcowP6891' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

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
define( 'AUTH_KEY',         'cu2zwuqcreezgfnevq48sm67lbmedfnphq6mros42xhvyvmbz96xkr4289bu80qn' );
define( 'SECURE_AUTH_KEY',  'iwr9xpkbcu3z48qdgzqqeygunistozui5gffnffk6nfdamzyahlgqh2oqwkzv32a' );
define( 'LOGGED_IN_KEY',    'wnys345xjjvbfc2xolw6bsfpwablmc3nutakamozpnhrszaqfyf4t63xwvbdpjhl' );
define( 'NONCE_KEY',        'g5o6sghogpazv13vnm6wp0wd8cebnqjv8xdlwixblcamd1avruqhbffx0rydro72' );
define( 'AUTH_SALT',        'cpabc6xdxt4mul07q8jhylzf3mxfilqdyyxdnj7y7gl6z2zjwhly31txvbdhsy0e' );
define( 'SECURE_AUTH_SALT', 'my0s0yate2dvzmtotwaofux0zzv6p0qkr4bw1hmtqs1xyoym41om44yxopcejvjh' );
define( 'LOGGED_IN_SALT',   '9h8zgjortzrkg6h8khvhkgnwxgbpipmjtmvznk4pyqiyilxg9sud3b2ugfwppo4b' );
define( 'NONCE_SALT',       'pwbdyb0vyaxp0yrazxb61txxwspg8dnpscnvpkqndrrcjiqwa5ffpcnr3i7qipja' );


// Multisite
define( 'WP_ALLOW_MULTISITE', true );
define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', true );
define( 'DOMAIN_CURRENT_SITE', 'ferior.com.ua' );
define( 'PATH_CURRENT_SITE', '/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wpfe_';

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
