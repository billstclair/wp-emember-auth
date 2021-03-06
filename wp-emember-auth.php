<?PHP

// Unfortunately, WordPress coders don't tend to make their files very functional.
// This is a functional translation of wp-content/plugins/wp-eMember/lib/class.emember_auth.php
// and the WordPress functions it calls.
// It's also stand-alone, not dependent on the SMF or WordPress environment.

$wpea_env = null;

function wpea_prepare_eval_file($file) {
  $str = file_get_contents($file);
  $pattern = array('/.*require.*/',
                   '/.*require_once.*/',
                   '/.*include.*/',
                   '/.*include_once.*/');
  $str = preg_replace($pattern, '', $str);
  $str = "namespace wpea;\n?>\n$str";
  return $str;
}

function wpea_load_env() {
  global $wpea_env;

  if ($wpea_env) return;     // Already done

  // Support explicit configuration, usually in Settings.php
  global $wpea_db_server;    // DB_HOST
  global $wpea_db_name;	     // DB_NAME
  global $wpea_db_user;      // DB_USER
  global $wpea_db_password;  // DB_PASSWORD
  global $wpea_db_prefix;    // $table_prefix
  global $wpea_auth_key;     // AUTH_KEY
  global $wpea_auth_salt;    // AUTH_SALT

  if (isset($wpea_db_server, $wpea_db_name, $wpea_db_user, $wpea_db_password, $wpea_db_prefix, $wpea_auth_key, $wpea_auth_salt)) {
    // Explicitly configured in Settings.php. Move those settings into $wpea_env
    $wpea_env = Array('db-host' => $wpea_db_server,
		      'db-name' => $wpea_db_name,
		      'db-user' => $wpea_db_user,
		      'db-password' => $wpea_db_password,
		      'table-prefix' => $wpea_db_prefix,
		      'auth-key' => $wpea_auth_key,
		      'auth-salt' => $wpea_auth_salt);
  } else {
    // Not explicitly configured in Settings.php. Try to load wp-config.php
    global $wpea_wp_dir, $boarddir;
    if (!isset($wpea_wp_dir)) {
      $wpea_wp_dir = "$boarddir/..";
    }

    $wp_config = "$wpea_wp_dir/wp-config.php";
    if (file_exists($wp_config)) {
      // This could potentially generate a fatal error and die.
      eval(wpea_prepare_eval_file($wp_config));
      $wpea_env = Array('db-host' => DB_HOST,
                        'db-name' => DB_NAME,
                        'db-user' => DB_USER,
                        'db-password' => DB_PASSWORD,
                        'table-prefix' => $table_prefix,
                        'auth-key' => AUTH_KEY,
                        'auth-salt' => AUTH_SALT);
    }
  }
}

$wpea_conn = null;

function wpea_open_db() {
  global $wpea_env;
  global $wpea_conn;

  if ($wpea_conn) return $wpea_conn;

  wpea_load_env();
  if ($wpea_env) {
    $wpea_conn = mysqli_connect($wpea_env['db-host'], $wpea_env['db-user'], $wpea_env['db-password'], $wpea_env['db-name']);
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

$wpea_smf_conn = null;

function wpea_open_smf_db() {
  global $wpea_smf_conn;
  // These variables need to be added to your SMF Settings.php file, copied from your WordPress wp-config.php
  global $db_type, $db_server, $db_name, $db_user, $db_passwd, $db_prefix;

  if ($wpea_smf_conn) return $wpea_smf_conn;

  if (isset($db_type, $db_server, $db_name, $db_user, $db_passwd, $db_prefix)) {
    if ($db_type != 'mysql') return null;
    $wpea_smf_conn = mysqli_connect($db_server, $db_user, $db_passwd, $db_name);
    return $wpea_smf_conn;
  }
  return null;
}

function wpea_close_smf_db() {
  global $wpea_smf_conn;
  if ($wpea_smf_conn) {
    $conn = $wpea_smf_conn;
    $wpea_smf_conn = null;
    mysqli_close($conn);
  }
}

function wpea_get_conn_value($conn, $tbl, $name_col, $val_col, $name, $allowMultiple=false) {
  $limit = $allowMultiple ? 1 : 2;
  if ($stmt = mysqli_prepare($conn, "SELECT $val_col FROM $tbl WHERE $name_col=? LIMIT $limit")) {
    mysqli_stmt_bind_param($stmt, "s", $name);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $value = null;
    mysqli_stmt_bind_result($stmt, $value);
    mysqli_stmt_fetch($stmt);
    $res = $value;
    if (!$allowMultiple) {
      $value = null;
      mysqli_stmt_fetch($stmt);
      if ($value != null) return null;
    }
    mysqli_stmt_close($stmt);
    return $res;
  }
  return null;
}

function wpea_db_table($tbl) {
  global $wpea_env;

  wpea_load_env();
  if ($wpea_env) {
    $tbl = $wpea_env['table-prefix'] . $tbl;
  }
  return $tbl;
}

function wpea_get_db_value($tbl, $name_col, $val_col, $name, $allowMultiple=false) {
  if ($conn = wpea_open_db()) {
    $tbl = wpea_db_table($tbl);
    return wpea_get_conn_value($conn, $tbl, $name_col, $val_col, $name, $allowMultiple);
  }
  return null;
}

function wpea_get_smf_db_value($tbl, $name_col, $val_col, $name, $allowMultiple=false) {
  global $db_prefix;
  if ($conn = wpea_open_smf_db()) {
    $tbl = $db_prefix . $tbl;
    return wpea_get_conn_value($conn, $tbl, $name_col, $val_col, $name, $allowMultiple);
  }
  return null;
}

function wpea_set_conn_value($conn, $tbl, $name_col, $val_col, $name, $value) {
  if ($stmt = mysqli_prepare($conn, "UPDATE $tbl SET $val_col = ? WHERE $name_col=?")) {
    mysqli_stmt_bind_param($stmt, "ss", $value, $name);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
  }
}

function wpea_set_db_value($tbl, $name_col, $val_col, $name, $value) {
  if ($conn = wpea_open_db()) {
    $tbl = wpea_db_table($tbl);
    wpea_set_conn_value($conn, $tbl, $name_col, $val_col, $name, $value);
  }
}

function wpea_set_smf_db_value($tbl, $name_col, $val_col, $name, $value) {
  global $db_prefix;
  if ($conn = wpea_open_smf_db()) {
    $tbl = $db_prefix . $tbl;
    wpea_set_conn_value($conn, $tbl, $name_col, $val_col, $name, $value);
  }
}

function wpea_conn_delete_row($conn, $tbl, $name_col, $name, $only_one=true) {
  $limit = $only_one ? " LIMIT 1" : "";
  if ($stmt = mysqli_prepare($conn, "DELETE FROM $tbl WHERE $name_col=?$limit")) {
    mysqli_stmt_bind_param($stmt, "s", $name);
    mysqli_stmt_execute($stmt);
    // Maybe we should get the ROW_COUNT(), but I don't need that for now
    return true;
  }
  return false;
}

function wpea_delete_db_row($tbl, $name_col, $name, $only_one=true) {
  if ($conn = wpea_open_db()) {
    $tbl = wpea_db_table($tbl);
    return wpea_conn_delete_row($conn, $tbl, $name_col, $name, $only_one);
  }
  return false;
}

function wpea_delete_smf_db_row($tbl, $name_col, $name, $only_one=true) {
  global $db_prefix;
  if ($conn = wpea_open_smf_db()) {
    $tbl = $db_prefix . $tbl;
    return wpea_conn_delete_row($conn, $tbl, $name_col, $name, $only_one);
  }
  return false;
}

function wpea_get_option($name) {
  return wpea_get_db_value('options', 'option_name', 'option_value', $name);
}

function wpea_get_emember_value($username, $column_name) {
  return wpea_get_db_value('wp_eMember_members_tbl', 'user_name', $column_name, $username);
}

function wpea_get_user_password($username) {
  return wpea_get_emember_value($username, 'password');
}

function wpea_get_user_email($username) {
  return wpea_get_emember_value($username, 'email');
}

function wpea_get_user_account_state($username) {
  return wpea_get_emember_value($username, 'account_state');
}

function wpea_last_session_impression($hmac) {
  return wpea_get_db_value('wp_auth_session_tbl', 'session_id', 'last_impression', $hmac);
}

function wpea_set_last_session_impression($hmac, $time) {
  wpea_set_db_value('wp_auth_session_tbl', 'session_id', 'last_impression', $hmac, $time);
}

function wpea_delete_session($hmac) {
  wpea_delete_db_row('wp_auth_session_tbl', 'session_id', $hmac);
}

// wp_salt('auth') from wp-includes/pluggable.php
function wpea_wp_auth_salt() {
  global $wpea_env;

  wpea_load_env();
  if ($wpea_env) {
    return $wpea_env['auth-key'] . $wpea_env['auth-salt'];
  };
  return "this ain't your mama's salt.";
}

// The b_hash() function in wp-content/plugins/wp-eMember/lib/class.emember_auth.php
function wpea_b_hash($data, $scheme = 'auth') {
  $salt = wpea_wp_auth_salt() . 'j4H!B3TA,J4nIn4.';
  return hash_hmac('md5', $data, $salt);
}

// From inside the validate() function in wp-content/plugins/wp-eMember/lib/class.emember_auth.php
function wpea_user_password_hmac($username, $expiration) {
  $password = wpea_get_user_password($username);
  $pass_frag = substr($password, 8, 4);
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

// Return true if a user's WP eMember subscription is still in effect.
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
  return $gmt ? gmdate($format) : gmdate($format, time() + (wpea_get_option('gmt_offset') * 3600));
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

// Looks up an SMF member's name by unique email
function wpea_lookup_smf_member_by_email($email) {
  return wpea_get_smf_db_value('members', 'email_address', 'member_name', $email);
}

function wpea_lookup_smf_member_email($member_name) {
  return wpea_get_smf_db_value('members', 'member_name', 'email_address', $member_name);
}

// Returns an SMF member's ID
function wpea_lookup_smf_member_id($member_name) {
  return wpea_get_smf_db_value('members', 'member_name', 'id_member', $member_name);
}

// Convert a WP eMember username to an SMF member name.
// This allows configuration to make them different, via $wpea_smf_member_names.
// Matching depends on unique email addresses.
function wpea_username_to_smf_member_name($username) {
  global $wpea_smf_member_names;
  if (isset($wpea_smf_member_names) && is_array($wpea_smf_member_names)) {
    if (isset($wpea_smf_member_names[$username])) {
      return $wpea_smf_member_names[$username];
    }
  }
  if ($email = wpea_get_user_email($username)) {
    $res = wpea_lookup_smf_member_by_email($email);
    if ($res) return $res;
    if ($smf_email = wpea_lookup_smf_member_email($username)) {
      if ($email != $smf_email) {
	// There's already an SMF member with $username, but a different email address.
	// If this were real, it would have been in $wpea_smf_member_names.
	// Create a new $username that is not yet in the database
	// This means that if you change the email in WP eMember, you MUST change it in SMF
	for ($cnt=2; ; $cnt++) {
	  $un = $username . $cnt;
	  if (!wpea_lookup_smf_member_id($un)) return $un;
	}
      }
    }
  }
  return $username;
}

function wpea_create_smf_member($wp_name, $smf_name) {
  global $db_prefix, $wpea_smf_member_group_id;
  $email = wpea_get_user_email($wp_name);
  $group = 0;
  if (isset($wpea_smf_member_group_id)) {
    $group = $wpea_smf_member_group_id;
  }
  if ($conn = wpea_open_smf_db()) {
    $date = time();
    $tbl = $db_prefix . 'members';
    if ($stmt = mysqli_prepare($conn, "INSERT INTO $tbl (member_name, date_registered, id_group, real_name, email_address, hide_email) VALUES (?, ?, ?, ?, ?, 1)")) {
      mysqli_stmt_bind_param($stmt, "siiss", $smf_name, $date, $group, $smf_name, $email);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);
      return true;
    }
  }
  return false;
}

// Called when the user logs out of SMF.
// Remove the cookie that keeps them logged in to WP eMember.
function wpea_integrate_logout() {
  $cookie_name = wpea_auth_cookie_name();
  $cookie = wpea_cookie_value($cookie_name);
  if (!$cookie) return null;
  //echo "cookie: $cookie\n";
  $cookie_elements = explode('|', $cookie);
  if (count($cookie_elements) != 3) return null;
  list($username, $expiration, $hmac) = $cookie_elements;
  unset($_COOKIE[$cookie_name]);
  setcookie($cookie_name, '', time() - 3600, '/');
  wpea_delete_session($hmac);
  wpea_close_smf_db();
  wpea_close_db();
}

// Checks WP eMember session cookie for validity
// If valid, creates SMF 'members' table row if it doesn't exist,
// and returns the SMF id_member.
// If invalid, or can't find or create SMF member, return 0.
// This is as expected by http://wiki.simplemachines.org/smf/Integration_hooks#integrate_verify_user
function wpea_integrate_verify_user() {
  $username = wpea_logged_in_username();
  $res = 0;
  if ($username) {
    $smf_member_name = wpea_username_to_smf_member_name($username);
    if ($smf_member_name) {
      $id = wpea_lookup_smf_member_id($smf_member_name);
      if (!$id) {
	if (wpea_create_smf_member($username, $smf_member_name)) {
	  $id = wpea_lookup_smf_member_id($smf_member_name);
	}
      } else {
        $state = wpea_get_user_account_state($username);
        if ($state != 'active') {
          return 0;
        }
      }
      if ($id) $res = $id;
    }
  }
  wpea_close_smf_db();
  wpea_close_db();
  return $res;
}
