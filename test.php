<?PHP

// From wp-config.php
$wpea_db_server = 'localhost';          // DB_HOST
$wpea_db_name = 'wp';                   // DB_NAME
$wpea_db_user = 'root';                 // DB_USER
$wpea_db_password = 'root';             // DB_PASSWORD
$wpea_db_prefix = 'wp_ztxn_';           // $table_prefix
$wpea_auth_key = 'dummy-auth-key';      // AUTH_KEY
$wpea_auth_salt = 'dummy-auth-salt';    // AUTH_SALT

require_once('wp-emember-auth.php');

$cookie_name = wpea_auth_cookie_name();
$user = 'BillSC';
$pass = wpea_get_user_password($user);
$hmac = wpea_user_password_hmac($user, '1474327520');

wpea_close_db();

echo "cookie: $cookie_name, password: $pass, hmac: $hmac\n";
