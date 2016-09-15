wp-emember-auth is for sites that have both SMF and WordPress with the [url=https://www.tipsandtricks-hq.com/wordpress-emember-easy-to-use-wordpress-membership-plugin-1706]WP eMember[/url] plugin. If a member is logged in to WP eMember, she will be automatically logged in to SMF.

By default, the association of user IDs between eMember and SMF is done by email address. This will only work if a user has the same email in both eMember and SMF and if all of the SMF users have unique email addresses.

For cases with existing SMF members who may not satisfy that criteria, there is an override table ([font=courier]$wpea_smf_member_names[/font]) that establishes the mapping explicitly.

If an SMF user cannot be found, from the mapping table or with a matching email address, a new SMF member will be created, with the eMember username as both member name and real name and with the eMember email address. If there is already an SMF member with the eMember username, but a different email address, a new SMF member name will be created, by appending a number to the eMember username.

The auto-created member account has no associated password, so if the user wants to change her "Account Settings", she'll have to go through the "forgot password" rigamarole to create one. But you may want to disable direct SMF login. See below for one way to do that.

You have two choices for configuring access to your WordPress database. If you do nothing, the plugin will look for the file [font=courier]wp-config.php[/font] in the parent directory of your SMF directory. If it finds it, it will load it (read it, eliminate the [font=courier]require_once[/font] at the end, and eval the rest), and use the variables it defines.

If you want to load the existing [font=courier]wp-config.php[/font], but your WordPress directory is NOT the parent directory of your SMF directory, you can specify the WordPress directory with a line like the following in your SMF [font=courier]Settings.php[/font] file (it should NOT end with a slash):

[code]
$wpea_wp_dir = "/var/www/html";
[/code]

It is possible that evaluating the WordPress [font=courier]wp-settings.php[/font] file will break your SMF. It works for me with SMF 2.0.11 and WordPress 4.6, but it may not work in some other versions. In that case, you'll need to configure the WordPress database settings yourself. You can do this by adding the following at the end of your SMF [font=courier]Settings.php[/font] file:

[code]
# Copy these values from your wp-config.php. Do NOT use them as is!
$wpea_db_server = 'localhost';          # DB_HOST
$wpea_db_name = 'wp';                   # DB_NAME
$wpea_db_user = 'root';                 # DB_USER
$wpea_db_password = 'root';             # DB_PASSWORD
$wpea_db_prefix = 'wp_ztxn_';           # $table_prefix
$wpea_auth_key = 'dummy-auth-key';      # AUTH_KEY
$wpea_auth_salt = 'dummy-auth-salt';    # AUTH_SALT
[/code]

There are two optional settings you can add to your SMF [font=courier]Settings.php[/font] file:

[code]
# Map WP eMember username to SMF member name.
# Useful for adding WP eMember authentication to an existing SMF forum,
# where email addresses are not guaranteed to match between the two.
# Optional.
$wpea_smf_member_names = array('wws' => 'Bill St. Clair',
                               'bozo' => 'clown'
                               );			       

# Set if you want new members to be put into a particular SMF group
# Optional.
$wpea_smf_member_group_id = 9;
[/code]

You may want to disallow direct logins to SMF, and require user requests for email or displayed name changes to be handled by an administrator. One way to do that, if you're using Apache, or another web server that parses .htaccess files, is with an .htaccess file in the forum directory containing something like the following (where you may have to change "/member-login/" to be the path to the WP eMember login page on your system):

[code]
# Redirect from SMF login page to WP eMember login page
RewriteEngine On

RewriteCond %{QUERY_STRING} ^action=login.*$ [OR]
RewriteCond %{QUERY_STRING} ^action=reminder$ [OR]
RewriteCond %{QUERY_STRING} ^action=register.*$
RewriteRule . /member-login/? [R,L]
[/code]

Code is at [url=https://github.com/billstclair/wp-emember-auth]github.com/billstclair/wp-emember-auth[/url].

Bill St. Clair <billstclair@gmail.com>
