<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Tm_Product_Configurator
 */

define( 'TESTS_PLUGIN_DIR', dirname( __DIR__ ) );
define( 'UNIT_TESTS_DATA_PLUGIN_DIR', TESTS_PLUGIN_DIR . '/tests/Data/' ); // Customize.

// Define WP_CORE_DIR if not already defined
if ( ! defined( 'WP_CORE_DIR' ) ) {
	$_wp_core_dir = getenv( 'WP_CORE_DIR' );
	if ( ! $_wp_core_dir ) {
		$_wp_core_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress';
	}
	define( 'WP_CORE_DIR', $_wp_core_dir );
}

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file.
$_phpunit_polyfills_path = getenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' );
if ( false !== $_phpunit_polyfills_path ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path );
}

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	
	// Load WooCommerce first from the standard WordPress plugins directory
	require_once WP_CORE_DIR . '/wp-content/plugins/woocommerce/woocommerce.php';

	require dirname( dirname( __FILE__ ) ) . '/tm-product-configurator.php';

}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

tests_add_filter('init', function () {

    if (class_exists('WC_Install')) {
        WC_Install::install();
        wc_update_product_lookup_tables();
    }
});

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";

//
require_once dirname(__DIR__) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

// Override wp_verify_nonce for tmpc_save_colours nonce in tests
tests_add_filter('wp_verify_nonce', function($result, $nonce, $action) {
    if ($action === 'tmpc_save_colours') {
        return true;
    }
    return $result;
}, 10, 3);

// Register product post type for testing
register_post_type('product', [
	'public' => true,
	'supports' => ['title'],
	'capability_type' => 'post'
]);

