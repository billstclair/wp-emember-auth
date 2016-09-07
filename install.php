<?PHP

add_integration_function('integrate_pre_include', '$sourcedir/wp-emember-auth.php',TRUE);
add_integration_function('integrate_verify_user', 'wpea_integrate_verify_user', TRUE);
add_integration_function('integrate_logout', 'wpea_integrate_logout', TRUE);
