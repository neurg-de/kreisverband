<?php
/**
 * PHPUnit bootstrap for Neurg Kreisverband theme tests.
 *
 * Uses the WordPress test suite (wp-phpunit).
 * Install it via: bin/install-wp-tests.sh
 *
 * @package Neurg_Kreisverband
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
    $_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
    echo "Could not find {$_tests_dir}/includes/functions.php\n";
    echo "Run: bin/install-wp-tests.sh wordpress_test root '' localhost latest\n";
    exit( 1 );
}

// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the theme for testing.
 */
function _manually_load_theme() {
    // Set the theme directory to our theme.
    register_theme_directory( dirname( __DIR__ ) );

    // Switch to our theme.
    switch_theme( 'theme' );
}
tests_add_filter( 'setup_theme', '_manually_load_theme' );

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";
