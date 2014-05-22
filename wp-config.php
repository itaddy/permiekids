<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'permie');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         '{$1EyZj+O)-WD547%fCq!^Ky*/O^%|T=W-.|aTgGmWp67f@>|Fa%2YC2gngM-ht-');
define('SECURE_AUTH_KEY',  '/71ES(?Lu_|Z[}1p@m32i<sQ2sgi3ajjl-Nb0&GR~SK5bcnZIh-e&2wL+,M|yA*h');
define('LOGGED_IN_KEY',    '@.XA?W*N[d+1Q@;4~w(0t|mHLthlh_.[VSJfL*s)g|CMlAs|[H1,)0=5pt^*Hw@?');
define('NONCE_KEY',        '&C5_j_@Fy7k1rGv:wLa8v<.NKvl#5N;BAH-Gt:InW2q_X%_W~mpBN)5o^Bqd?KjC');
define('AUTH_SALT',        '%+{/BZ<=hvEAL_2Us`S[Il/!4 SaLblybJuGtMSLsl_ zd3y1.~Ub,Z|<+~p2jM]');
define('SECURE_AUTH_SALT', 'K-+qJo+J^pc}~+o8C-L7<l-zdE40-(*$$iP7G0aejKRn<.!Ui1cqY3Tt:t,xBuKF');
define('LOGGED_IN_SALT',   's3RghAWuM[M?TnCTZt*yib+hw8_1owSs0EI_VH /fQFx$Md%FDB*ia9H7KN|f+xB');
define('NONCE_SALT',       'bFG=eN^Uo.QjB@-(O[o9X;/HvUt;FWH>nP!Y?={+-LJ$v!nf![cAptt 4EZtnZ=I');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
