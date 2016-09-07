This is an [SMF](http://www.simplemachines.org/) plugin to authenticate the forum from the [WP eMember](https://www.tipsandtricks-hq.com/wordpress-emember-easy-to-use-wordpress-membership-plugin-1706) plugin's auth data and cookies.

See readme.txt for installation instructions. To configure, you need to duplicate your WordPress ```wp-config.php``` database and authorization key and salt parameters (with similar, but different names) into your SMF ```Settings.php``` file.

Use the ```make-package``` script to create ```wp-emember-auth.tar.gz``` from the relevant files.

Requires SMF 2.0 or higher for use of the [integration hooks](http://wiki.simplemachines.org/smf/Integration_hooks).

Tested in SMF 2.0.11 with WordPress 4.6 and WP eMember v9.0.8.

Copies the code WP eMember uses to encode its authorization cookie, so prone to being broken should they change that. For some reason, WordPress authors feel no need to make stand-alone functional versions of their code that can just be used instead of copied. Sigh...

Bill St. Clair &lt;[billstclair@gmail.com](mailto:billstclair@gmail.com)&gt;
