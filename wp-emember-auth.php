<?PHP

// Unfortunately, WordPress coders don't tend to make their files very functional.
// This is a functional translation of wp-content/plugins/wp-eMember/lib/class.emember_auth.php
// and the WordPress functions it calls.
// It's also stand-alone, not dependent on the SMF or WordPress environment.

$wpea_conn = null;

function wpea_open_db() {
  global $wpea_conn;
  // These variables need to be added to your SMF Settings.php file, copied from your WordPress wp-config.php
  global $wpea_db_server;    // DB_HOST
  global $wpea_db_name;	     // DB_NAME
  global $wpea_db_user;      // DB_USER
  global $wpea_db_password;  // DB_PASSWORD
  global $wpea_db_prefix;    // $table_prefix
  global $wpea_auth_key;     // AUTH_KEY
  global $wpea_auth_salt;    // AUTH_SALT

  if ($wpea_conn) return $wpea_conn;

  if (isset($wpea_db_server, $wpea_db_name, $wpea_db_user, $wpea_db_password, $wpea_db_prefix, $wpea_auth_key, $wpea_auth_salt)) {
    $wpea_conn = mysqli_connect($wpea_db_server, $wpea_db_user, $wpea_db_password, $wpea_db_name);
    return $wpea_conn;
  }
  return null;
}

function wpea_close_db() {
  global $wpea_conn;
  if ($wpea_conn) {
    $conn = $wpea_conn;
    $wpea_conn = null;
    mysqli_close($conn);
  }
}

function wpea_get_db_value($tbl, $name_col, $val_col, $name) {
  global $wpea_db_prefix;
  if ($conn = wpea_open_db()) {
    $tbl = $wpea_db_prefix . $tbl;
    if ($stmt = mysqli_prepare($conn, "SELECT $val_col FROM $tbl WHERE $name_col=? LIMIT 1")) {
      mysqli_stmt_bind_param($stmt, "s", $name);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_store_result($stmt);
      $value = null;
      mysqli_stmt_bind_result($stmt, $value);
      mysqli_stmt_fetch($stmt);
      mysqli_stmt_close($stmt);
      return $value;
    }
  }
  return null;
}

function wpea_set_db_value($tbl, $name_col, $val_col, $name, $value) {
  global $wpea_db_prefix;
  if ($conn = wpea_open_db()) {
    $tbl = $wpea_db_prefix . $tbl;
    if ($stmt = mysqli_prepare($conn, "UPDATE $tbl SET $val_col = ? WHERE $name_col=?")) {
      mysqli_stmt_bind_param($stmt, "ss", $value, $name);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);
    }
  }
  return null;
}

function wpea_get_option($name) {
  return wpea_get_db_value('options', 'option_name', 'option_value', $name);
}

function wpea_get_user_password($username) {
  return wpea_get_db_value('wp_eMember_members_tbl', 'user_name', 'password', $username);
}

function wpea_last_session_impression($hmac) {
  return wpea_get_db_value('wp_auth_session_tbl', 'session_id', 'last_impression', $hmac);
}

function wpea_set_last_session_impression($hmac, $time) {
  wpea_set_db_value('wp_auth_session_tbl', 'session_id', 'last_impression', $hmac, $time);
}

// wp_salt('auth') from wp-includes/pluggable.php
function wpea_wp_auth_salt() {
  global $wpea_auth_key;
  global $wpea_auth_salt;
  return $wpea_auth_key . $wpea_auth_salt;
}

// From wp-content/plugins/wp-eMember/lib/class.emember_auth.php
// The b_hash() function
function wpea_b_hash($data, $scheme = 'auth') {
  $salt = wpea_wp_auth_salt() . 'j4H!B3TA,J4nIn4.';
  return hash_hmac('md5', $data, $salt);
}

// Inside the validate() function
function wpea_user_password_hmac($username, $expiration) {
  $pass_frag = substr(wpea_get_user_password($username), 8, 4);
  $key = wpea_b_hash($username . $pass_frag . '|' . $expiration);
  return hash_hmac('md5', $username . '|' . $expiration, $key);
}

// COOKIEHASH
function wpea_cookiehash() {
  return md5(wpea_get_option('siteurl'));
}

function wpea_auth_cookie_name() {
  return 'wp_emember_' . wpea_cookiehash();
}

function wpea_cookie_value($cookie_name) {
  if (!isset($_COOKIE[$cookie_name])) return null;
  return $_COOKIE[$cookie_name];
}

// Return true if a user's WP eMember subscrition is still in effect.
// To be done.
// Need to process member_since & membership_level from wp_eMember_members_tbl,
// using subscription_period & subscription_unit from wp_eMember_membership_tbl.
// May also want a user setting to ignore this.
function wpea_is_membership_current($username) {
  return true;
}

// from current_time() in wp-includes/functions.php
function wpea_current_mysql_time($gmt = 0) {
  $format = 'Y-m-d H:i:s';
  return $gmt ? gmdate($format) : gmdate($format, time() + (wpea_get_option( 'gmt_offset') * 3600));
}

// Finally, the actual authorization function
// Returns the username if we're logged in as a WP eMember, or null otherwise.
// From validate() in wp-content/plugins/wp-eMember/lib/class.emember_auth.php
function wpea_logged_in_username() {
  $cookie = wpea_cookie_value(wpea_auth_cookie_name());
  if (!$cookie) return null;
  //echo "cookie: $cookie\n";
  $cookie_elements = explode('|', $cookie);
  if (count($cookie_elements) != 3) return null;
  list($username, $expiration, $hmac) = $cookie_elements;
  //echo "username: $username, expiration: $expiration, hmac: $hmac, time: " . time() . "\n";
  if ($expiration < time()) return null;
  if (!wpea_is_membership_current($username)) return null;
  $hash = wpea_user_password_hmac($username, $expiration);
  //echo "hash: $hash\n";
  if ($hmac != $hash) return null;
  $last_impression = wpea_last_session_impression($hmac);
  //echo "hmac: $hmac, last_impression: $last_impression\n";
  if (!$last_impression) return null;
  // Maybe I should do auto-logout here...
  $current_time = wpea_current_mysql_time(true);
  wpea_set_last_session_impression($hmac, $current_time);
  return $username;
}
