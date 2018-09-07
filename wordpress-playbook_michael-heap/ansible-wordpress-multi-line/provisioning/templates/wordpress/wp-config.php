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
define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'wordpress');

/** MySQL database password */
define('DB_PASSWORD', 'bananas');

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
define('AUTH_KEY',         'W;n[+eJu7?j66Sb:/DyCi(G*]l9+;DF#lF3+v!gYUET`,5>fsvRjs9ec|BvH`f1K');
define('SECURE_AUTH_KEY',  '[:II*PKs;6/RO?WxdYIw+45caffB+*tWuo>;Z/*g%c_U)+N[/VRmE8I~:#p&sXB}');
define('LOGGED_IN_KEY',    '3X8X#,o*w`LsYRs }B>A(3+~7HZz+5:GKpr*EB%Y;&A*<4W1>IZA@I7<[&>olCvh');
define('NONCE_KEY',        '8pgR=oO6LRBWPOjrt[)0YqW@<!!f(?/sYtqX<!s3ogIYz|`7i5EGT#j9iNK+KVxm');
define('AUTH_SALT',        'jbEH0W6vX7BIhniugk!bHvGuyBKs=_lS%4A9v`;uT>V))6`SY;9#N={(Lrg%!grv');
define('SECURE_AUTH_SALT', 'x__..L.I2P~5<;sf5+P`}+ XT>x?@9] Qd>e9$@i5 AfWcxdmGaeFF7>a^I(`c<W');
define('LOGGED_IN_SALT',   'E)W07PNCQ.Lh_7V}ub#b$T0Vx^a=#j~Tbg_LX4#oGuuAR2=.x!.D+sPkw q+,0Z=');
define('NONCE_SALT',       'F+.xL,W|_l+yVmM-S<5R1_2.i6Jq~G2ezfgcOvU FAtO?r8NW&ms}ZZwm4~z]fbm');

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


