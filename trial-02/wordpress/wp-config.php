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
define('DB_NAME', 'closure_lwp-trial-two_wordpress');

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
define('AUTH_KEY',         'uZX]Bf:Pc![-FT=Z/4LsG/cNjS?@elYDsl3z@P)*_2[Nk{Aka0;aKvM4*xG2,T|r');
define('SECURE_AUTH_KEY',  '1a(mQVUbuF&wR]~i*b)>k~h:%V&Y`<9-[%Z*:-G@w/e!0`1M :q|JXcUx7+0qGb2');
define('LOGGED_IN_KEY',    'Z#)n:x44W)lb]#eH3C]%7-_E=44@f(1R]`Hzqa.elgVQwq7zS=^,<N$,R]ciK<9:');
define('NONCE_KEY',        'F%]A:fR.=RTBS^w@os:Ojte:KIh*0.S7w=Ob(+5>$z#8XMD[$f+,8 W-(<0`YcR9');
define('AUTH_SALT',        '+:dRF|Eyj|4R5*a%gl>qDq1NG3@2; !+]v6**YXLFE)i8u/3Rzg!r,jn=aQ456DS');
define('SECURE_AUTH_SALT', 'kR3Y[Zo-N@oBo.zt`=%,BIV^+Quy~VfcR#Kxb!y[RG*S~!O/ .AYnx,;AJA;!u= ');
define('LOGGED_IN_SALT',   'kQ nR?a5`oGBs|R8 X~G4LTF%S/>F}p4eDqF~FaUxaY/#-[Ozh!}z*IaVL],y|D]');
define('NONCE_SALT',       '19fVj9(PeppB ]-IM^Bn5DU^B#`kx*Ti#Z6BjL&#TkdEk5eyLvyB~75BB}<91cX$');

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
