<?PHP

// From wp-config.php
$wpea_db_server = 'localhost';          // DB_HOST
$wpea_db_name = 'wp';                   // DB_NAME
$wpea_db_user = 'root';                 // DB_USER
$wpea_db_password = 'root';             // DB_PASSWORD
$wpea_db_prefix = 'wp_ztxn_';           // $table_prefix
// AUTH_KEY
$wpea_auth_key = 'dummy-auth-key';
// AUTH_SALT
$wpea_auth_salt = 'dummy-auth-salt';

require_once('wp-emember-auth.php');

$cookie_name = wpea_auth_cookie_name();
$user = 'BillSC';
$pass = wpea_get_user_password($user);
$time = '1474327520';
$hmac = wpea_user_password_hmac($user, $time);

// Fake the cookie
$cookie = "$user|$time|$hmac";
$_COOKIE[wpea_auth_cookie_name()] = $cookie;

$logged_in_user = wpea_logged_in_username();

wpea_close_db();

echo "logged in user: $logged_in_user\n";
