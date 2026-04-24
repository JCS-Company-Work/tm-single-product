<?php
/**
 * Plugin Name: TM Product Configurator
 * Description: Custom product cart handling, Base64 thumbnails, mini-cart, current status, AJAX, model selection and totals.
 * Version: 2.5
 * Author: Tailor-made+
 */

if (!defined('ABSPATH')) exit;

// Constants
define( 'TMPC_PATH', plugin_dir_path( __FILE__ ) );
define( 'TMPC_URL',  plugin_dir_url( __FILE__ ) );
define( 'TMPC_VERSION', '2.5' );

// Path to composer also bring in dotenv for environment variable handling
if (file_exists(TMPC_PATH . 'vendor/autoload.php')) {
    require_once TMPC_PATH . 'vendor/autoload.php';

    // Load environment variables from .env file in root
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Classes required
use TMProductConfigurator\Init\TMPC_Init;

add_action('plugins_loaded', [TMPC_Init::class, 'init']);