<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'closure_lwp-trial-one_wordpress');

/** MySQL database username */
define('DB_USER', 'homestead');

/** MySQL database password */
define('DB_PASSWORD', 'secret');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '=aT_Ez60PfwR:,U`;e`3s-xJLK9%i*HLP6:$m0@5@UR;& cvx@z<#<+Ijz_?C5:u');
define('SECURE_AUTH_KEY',  ',Lc}aIx&Q0pb<5bI7|$c|z~`BfZiGLeHXC!5C`]>}l]5X!AQC:PLkY/(r`>Bz|2 ');
define('LOGGED_IN_KEY',    'zl)*o$JR}[&bE&a({hv7Y<C,5,Bl@K7{` 7-B:5g8[S)G8JmoJ,6AbkG]BX]}KN.');
define('NONCE_KEY',        'uW{Dk6(w,@3?(_jp!Fjh9*jh9P3xb%t{TGdr|.!3mP)`lUwX7o?]Dg>G0!<$t|Iy');
define('AUTH_SALT',        ',q?xS%I^2?sqNbBCYA.m~1g_o)%>?a]3z}IA#N5Y{3.CbdVgovBrK{Bf}Y&#s@#D');
define('SECURE_AUTH_SALT', '^tCUTB5gLgDj$AN8q}>``}i?=oixNi2nT6SBEe64{fkv$$>YrBkp+eDXP/m:@7TG');
define('LOGGED_IN_SALT',   'Tu>n,i>vDoTP.oY&ti8PwrO=FV&]sfJr&cx6>ZpyLo&:wI/wEILI@9Dc0^_ed#99');
define('NONCE_SALT',       '.QM?aCJDh+`X6Dr0*9iwznb8M|Z3,CH^pPblkI`*baRuX)Vkm5^2>GOD1dO-rcc:');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
