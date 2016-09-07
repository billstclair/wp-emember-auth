<?PHP

remove_integration_function('integrate_logout', 'wpea_integrate_logout');
remove_integration_function('integrate_verify_user', 'wpea_integrate_verify_user');
remove_integration_function('integrate_pre_include', '$sourcedir/wp-emember-auth.php');
