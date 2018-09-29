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
define('DB_NAME', 'closure_lwp-trial-four_wordpress');

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
define('AUTH_KEY',         'NliHe^XWg3SOarVq jvJS;Zm$Iy<~t@$id|d][n|&@gP}a2]=6 >:E9=_p*`7*{#');
define('SECURE_AUTH_KEY',  'mi&oT)Ew9]bp^^hbpV;8mpCM`pqtbri6SY.7{j2BF2_2}#ll%f*mSBVY/CNK*+uo');
define('LOGGED_IN_KEY',    '}NK4cv/6V)ulV*]p]/i;+`H?L)x,:eh$9|Hl0GRDx=Dj@Qy`N^>Ei|cD:.b{.WmE');
define('NONCE_KEY',        'POOGY/G@dfi4iYPLLM4Ji=mLou. hu]>2Mg8JHeWP)CSr/M+#R*F&v>Sh]N1=A 6');
define('AUTH_SALT',        'K@4kDI-jP9m7G]CpeN0:S_?;1OC[5N(o[a`[Am7=I7}E@x:[9}(3,{[j37|w|KQ/');
define('SECURE_AUTH_SALT', '/rjVt=_7r8-KQQt_]LRivBKUWHsbGRZ%1DQVKQASp5_Q6l(U&f,qC2<5dys|]LyA');
define('LOGGED_IN_SALT',   ';yI;uHgSWe&a0${5+{ik20JL@Y:f2W4(/NzVW4h=_A4T Ld11HZ|a>*`Nd)*?# 8');
define('NONCE_SALT',       'R;`1Yw>tvDpiK[ C/%*62>?VBKI9JR^g9F~`+n2rZ]<teW],>+8>d.Yeq8xnGu)p');

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
