wp-emember-auth is for sites that have both SMF and WordPress with the [url=https://www.tipsandtricks-hq.com/wordpress-emember-easy-to-use-wordpress-membership-plugin-1706]WP eMember[/url] plugin. If a member is logged in to WP eMember, she will be automatically logged in to SMF.

By default, the association of user IDs between eMember and SMF is done by email address. This will only work if a user has the same email in both eMember and SMF and if all of the SMF users have unique email addresses.

For cases with existing SMF members who may not satisfy that criteria, there is an override table ([font=courier]$wpea_smf_member_names[/font]) that establishes the mapping explicitly.

If an SMF user cannot be found, from the mapping table or with a matching email address, a new SMF member will be created, with the eMember username as both member name and real name and with the eMember email address. If there is already an SMF member with the eMember username, but a different email address, a new SMF member name will be created, by appending a number to the eMember username.

The auto-created member account has no associated password, so if the user wants to change her "Account Settings", she'll have to go through the "forgot password" rigamarole to create one.

Configuration is via new variables in the SMF [font=courier]Settings.php[/font] file. Most of these duplicate information that is already in the WordPress [font=courier]wp-config.php[/font] file. All parameter values must be copied verbatim, or it won't work.

[font=courier]# From wp-config.php
$wpea_db_server = 'localhost';          # DB_HOST
$wpea_db_name = 'wp';                   # DB_NAME
$wpea_db_user = 'root';                 # DB_USER
$wpea_db_password = 'root';             # DB_PASSWORD
$wpea_db_prefix = 'wp_ztxn_';           # $table_prefix
$wpea_auth_key = 'dummy-auth-key';      # AUTH_KEY
$wpea_auth_salt = 'dummy-auth-salt';    # AUTH_SALT

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
[/font]

Code is at [url=https://github.com/billstclair/wp-emember-auth]github.com/billstclair/wp-emember-auth[/url].

Bill St. Clair <billstclair@gmail.com>
