<?php
/** Enable W3 Total Cache */
define('WP_CACHE', true); // Added by W3 Total Cache


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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'axe01942_givehada_cloud' );

/** Database username */
define( 'DB_USER', 'axe01942_givehada_cloud' );

/** Database password */
define( 'DB_PASSWORD', 'NYRCru6ANkqh6KxWjtGT' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          '_PG8g3$Lm|K|tnk%ntxc;l2o(CP$il=PJ$sZ[p%=q<|n3]O^8bU6%sE96!+|Xja_' );
define( 'SECURE_AUTH_KEY',   'JA^&jf]x?QYmZw7Aou+VUPHTD5^+F<WhDY>iH@*TX3!.2(z3&O/dEB`=[)H>7MRP' );
define( 'LOGGED_IN_KEY',     'T/*H(aKCy<.wrTDGG;c39w.F98mgnC5:sdPfrid*G}m9Lpo/)^c}YPm^IRP%YROg' );
define( 'NONCE_KEY',         'B1O`!yen9IN6?JnRo(>Hd+,~4Z<_[vuccsCr@!ZbUXe/r*EyRMwho=uKSNoz6~p!' );
define( 'AUTH_SALT',         '*eDC!A(Zch~RuOpJ4F~L,QHm,a gczN4e2}H{wzJ`S.E3d4~?cN8;[>O_@CK$M,4' );
define( 'SECURE_AUTH_SALT',  'pc)R <BO!F4SJW{49%&N-w*S pv~8_Su1{R|z)v=vN:m8i7r5qGcW~]xiO%?gX>E' );
define( 'LOGGED_IN_SALT',    'Q4s! qXji5nj(ds+,BD,+79+3/ma[5fv--gNl.3 bpH%jIUOX|6M}f$d(jE|AOZW' );
define( 'NONCE_SALT',        'TvBP[5H|sc1MH:oc7Gc&@!v9ZlV6NA^R^z!5_oJ 52Z]jOm=?YR23tWp^If/[SW@' );
define( 'WP_CACHE_KEY_SALT', 'yzC!X%!!S*SP^Q5ZuT<B?Y*()EU[/U^lY$}uwKN~G$E3WQ=^2w70(x#NDGcav-Af' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define('WP_DEBUG', false);
	// define( 'WP_DEBUG', true );
	// define( 'WP_DEBUG_LOG', true );
	// define( 'WP_DEBUG_DISPLAY', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
