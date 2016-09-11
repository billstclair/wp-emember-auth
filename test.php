<?PHP

// From wp-config.php
/*
$wpea_db_server = 'localhost';          // DB_HOST
$wpea_db_name = 'wp';                   // DB_NAME
$wpea_db_user = 'root';                 // DB_USER
$wpea_db_password = 'root';             // DB_PASSWORD
$wpea_db_prefix = 'wp_ztxn_';           // $table_prefix
// AUTH_KEY
$wpea_auth_key = 'dummy-auth-key';
// AUTH_SALT
$wpea_auth_salt = 'dummy-auth-salt';
*/

// Map WP eMember username to SMF member name
// Useful for adding WP eMember authentication to an existing SMF forum.
$wpea_smf_member_names = array('BillSC' => 'Bill St. Clair'
			       );			       

// Set if you want new members to be put into a group
$wpea_smf_member_group_id = 9;

// These are set in the SMF Settings.php file. Need them here for testing.
$db_type = 'mysql';
$db_server = 'localhost';
$db_name = 'smf';
$db_user = 'root';
$db_passwd = 'root';
$db_prefix = 'smf_';

require_once('wp-emember-auth.php');

$cookie_name = wpea_auth_cookie_name();
$user = 'BillSC';  // 'billstclair';
$pass = wpea_get_user_password($user);
$time = '1474327520';  //'1474369886';
$hmac = wpea_user_password_hmac($user, $time);

// Fake the cookie
$cookie = "$user|$time|$hmac";
$_COOKIE[wpea_auth_cookie_name()] = $cookie;

$logged_in_user = wpea_logged_in_username();
$smf_user_id = wpea_integrate_verify_user();

wpea_close_db();
wpea_close_smf_db();

$member_name = wpea_username_to_smf_member_name($user);
$other_name = wpea_username_to_smf_member_name('frobulate');
echo "member_name: $member_name, other_name: $other_name\n";

echo "logged in user: $logged_in_user, smf_user_id: $smf_user_id\n";
