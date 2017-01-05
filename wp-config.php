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
define('DB_NAME', 'up');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

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
define('AUTH_KEY',         'GJK}U,<FzMx:UXF=7>I;E_,GNvu6-gOFu!^~Vr*mA3ond.6j&hs0.V+rpPD$vTQD');
define('SECURE_AUTH_KEY',  'w;y9hc[M^q=gIF,3pVC|d?6HzBkCe1_ZZ2^#sJpeOm<kTg3S_C([RbhDd|IHb-k_');
define('LOGGED_IN_KEY',    '}n*4,bB1a,pz+ps+95[nfxY[9E%S^dV2JHw~%OYo(+G&.F+z/z1 ,`3~|vFZaUy,');
define('NONCE_KEY',        '`@Fb.0[CJ3?Myji8H9I)Gn:J6tXwJWqzY?KIk!rlC461vHu}t&,8t(<i[J^CsT{-');
define('AUTH_SALT',        'erf|l!XjZz`o!jB}.%/Vjik3Ab@kp`az9!<rgU<&q1j+jm`rRn)5^W,hc4IQ~+>2');
define('SECURE_AUTH_SALT', 'W8p&_H_u^rj(:aUP>[DfKuvZh&?(AxTJdO_H?L-Oih{Sm:q4rR[Nb_`d8cX(E@TK');
define('LOGGED_IN_SALT',   '@19e%c[%l8J$~sf]6K.OE4~OT1;}Qk-nBXmw!d2)fBHDLcb=SyuE)f_1JHdK[h`M');
define('NONCE_SALT',       '*}`@<,~ow^H&(fHy%QqdGBq?pjiX_UOD*g.q4;-J+!&QNH%yz$AnI{i$F/zoQi<E');

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
