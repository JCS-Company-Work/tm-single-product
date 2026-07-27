<?php
/**
 * Plugin Name: TM Single Product
 * Description: Single product page layout, WooCommerce hook overrides, gallery, cart and email handling for Tailor-made+ store.
 * Version: 2.0.2
 * Author: Tailor-made+
 */

if (!defined('ABSPATH')) exit;

// Constants
define( 'TMSP_PATH', plugin_dir_path( __FILE__ ) );
define( 'TMSP_URL',  plugin_dir_url( __FILE__ ) );
define( 'TMSP_VERSION', '2.0.2' );
define( 'TMSP_CSS', '1.0.0' );

// Path to composer also bring in dotenv for environment variable handling
if (file_exists(TMSP_PATH . 'vendor/autoload.php')) {
    require_once TMSP_PATH . 'vendor/autoload.php';

    // Load environment variables from .env file in root
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Classes required
use TMSingleProduct\Init\TMSP_Init;

add_action('plugins_loaded', function () {

    // Initialize the plugin by calling the init methods of all required modules
    TMSP_Init::init();

}, 1);