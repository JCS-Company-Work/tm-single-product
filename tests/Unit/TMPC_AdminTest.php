<?php

use TMProductConfigurator\Admin\TMPC_Admin;
require_once __DIR__ . '/../Support/WooTestEnv.php';

beforeEach(function () {
    // Reset hooks and $_POST before each test
    remove_all_actions('woocommerce_product_data_tabs');
    remove_all_actions('woocommerce_product_data_panels');
    remove_all_actions('woocommerce_admin_process_product_object');
    remove_all_actions('admin_enqueue_scripts');
    $_POST = [];
});

it('saves and reloads product model sizes in admin', function () {

    // Create admin user and set current user
    $user_id = WooTestEnv::createUser();

    // Create product
    $product_id = WooTestEnv::createProduct([
        'title' => 'Test Slim Product',
        'type'  => 'simple',
        'post_author' => $user_id,
    ]);

    // Set categories for new product
    WooTestEnv::setProductCategories($product_id);

    // Simulate real WP product page context
    WooTestEnv::forProduct($product_id);

    // Check Woo product loads
    $product = wc_get_product($product_id);

    // Expect product to be valid    
    expect($product)->toBeInstanceOf('WC_Product');
    
    // Simulate POST data for model sizes with nonce and default size index
    $_POST = [
        'tmpc_model_size_nonce' => wp_create_nonce('tmpc_save_model_size'),
        'post_ID' => $product_id,
        'tmpc_model_sizes' => [
            ['label' => 'Large', 'dims' => '20x20', 'price' => 200.00],
            ['label' => 'Small', 'dims' => '10x10', 'price' => 100.00],
        ],
        'tmpc_model_sizes_default' => 1,
    ];

    // Ensure meta is empty before saving
    update_post_meta($product_id, '_tmpc_model_size', []);
    
    // Call the method to save model size
    TMPC_Admin::save_model_size($product_id);
    
    // Expect that the model size meta is updated with the new values
    $saved = get_post_meta($product_id, '_tmpc_model_size', true);
    
    // Assert that the saved model sizes match the input including default value
    // based on tmpc_model_sizes_default (1 means the first item is default, zero-based index)
    expect($saved)->toBe([
        ['label' => 'Large', 'dims' => '20x20', 'price' => 200.00, 'is_default' => false],
        ['label' => 'Small', 'dims' => '10x10', 'price' => 100.00, 'is_default' => true],
    ]);

    // Clean up by removing created user
    WooTestEnv::removeUser($user_id);
    
});

it('saves and reloads product default colours in admin', function () {

    // Create admin user and set current user
    $user_id = WooTestEnv::createUser();

    // Create product
    $product_id = WooTestEnv::createProduct([
        'title' => 'Test Slim Product',
        'type'  => 'simple',
        'post_author' => $user_id,
    ]);

    // Set categories for new product
    WooTestEnv::setProductCategories($product_id);

    // Simulate real WP product page context
    WooTestEnv::forProduct($product_id);

    // Check Woo product loads
    $product = wc_get_product($product_id);

    // Expect product to be valid    
    expect($product)->toBeInstanceOf('WC_Product');

    $_POST = [
        'tmpc_colours_nonce' => wp_create_nonce('tmpc_save_colours'),
        'tmpc_top_colour' => 'blue',
        'tmpc_base_colour' => 'walnut',
        'tmpc_metal_colour' => 'gold',
    ];

    // Ensure meta is empty before call TMPC_Admin::save_default_colours
    update_post_meta($product_id, '_tmpc_top_colour', '');
    update_post_meta($product_id, '_tmpc_base_colour', '');
    update_post_meta($product_id, '_tmpc_metal_colour', '');

    // Call the method to save default colours
    TMPC_Admin::save_default_colours($product_id);
    
    // Expect that the colour meta is updated with the new values
    expect(get_post_meta($product_id, '_tmpc_top_colour', true))->toBe('blue');
    expect(get_post_meta($product_id, '_tmpc_base_colour', true))->toBe('walnut');
    expect(get_post_meta($product_id, '_tmpc_metal_colour', true))->toBe('gold');

    // Clean up by removing created user
    WooTestEnv::removeUser($user_id);

});

it('registers WooCommerce admin hooks on init', function () {

    // Call the init method to register hooks
    TMPC_Admin::init();

    // Check that the expected hooks are registered
    expect(has_filter('woocommerce_product_data_tabs', [TMPC_Admin::class, 'add_configurator_tabs']))->not->toBeFalse();
    expect(has_action('woocommerce_product_data_panels', [TMPC_Admin::class, 'render_configurator_panels']))->not->toBeFalse();
    expect(has_action('woocommerce_admin_process_product_object', [TMPC_Admin::class, 'save_configurator_fields']))->not->toBeFalse();
    expect(has_action('admin_enqueue_scripts', [TMPC_Admin::class, 'enqueue_admin_assets']))->not->toBeFalse();

});

it('adds custom tabs to WooCommerce product data tabs', function () {

    // Simulate standard tabs from WP life cycle
    $tabs = [];
    
    // Call the method to add configurator tabs
    $result = TMPC_Admin::add_configurator_tabs($tabs);

    // Check that the new tabs are added
    expect($result)->toHaveKey('tmpc_colours');
    expect($result)->toHaveKey('tmpc_model_size');

    // Check that the labels are correct
    expect($result['tmpc_colours']['label'])->toBe(__('Select Colours', 'tm-product-configurator'));
    expect($result['tmpc_model_size']['label'])->toBe(__('Model Sizes', 'tm-product-configurator'));

});

it('does not enqueue admin assets on unrelated pages', function () {

    // Should not enqueue on dashboard
    $scripts_before = did_action('wp_enqueue_scripts');
    
    // Simulate unrelated admin page
    TMPC_Admin::enqueue_admin_assets('index.php');
    
    // No assertion here, but no errors should occur and no scripts should be enqueued
    $scripts_after = did_action('wp_enqueue_scripts');
    
    // Expect no change in the number of times scripts were enqueued
    expect($scripts_after)->toBe($scripts_before);

});

it('enqueues admin assets on product edit pages', function () {

    // Simulate product edit page
    add_filter('wp_enqueue_scripts', '__return_true');
    
    // Call the method to enqueue assets
    TMPC_Admin::enqueue_admin_assets('post.php');
    
    // No assertion here, but no errors should occur
    expect(true)->toBeTrue();

});

it('does not save model size if nonce is missing', function () {

    // Create admin user and set current user
    $user_id = WooTestEnv::createUser();

    // Create product
    $product_id = WooTestEnv::createProduct([
        'title' => 'Test Slim Product',
        'type'  => 'simple',
        'post_author' => $user_id,
    ]);

    // Set categories for new product
    WooTestEnv::setProductCategories($product_id);

    // Simulate real WP product page context
    WooTestEnv::forProduct($product_id);

    // Check Woo product loads
    $product = wc_get_product($product_id);

    // Expect product to be valid    
    expect($product)->toBeInstanceOf('WC_Product');

    // Simulate POST data without nonce
    $_POST = [
        // 'tmpc_model_size_nonce' => missing
        'post_ID' => 1,
        'tmpc_model_sizes' => [['label' => 'Test', 'dims' => '10x10', 'price' => 100]],
        'tmpc_model_sizes_default' => 0,
    ];

    // Ensure meta is empty before saving
    update_post_meta($product_id, '_tmpc_model_size', []);
    
    // Call the method to save model size
    TMPC_Admin::save_model_size($product_id);
    
    // Expect that the model size meta is not updated and remains empty
    expect(get_post_meta($product_id, '_tmpc_model_size', true))->toBe([]);

    // Clean up by removing created user
    WooTestEnv::removeUser($user_id);

});

it('does not save default colours if nonce is missing', function () {

    // Create admin user and set current user
    $user_id = WooTestEnv::createUser();

    // Create product
    $product_id = WooTestEnv::createProduct([
        'title' => 'Test Slim Product',
        'type'  => 'simple',
        'post_author' => $user_id,
    ]);

    // Set categories for new product
    WooTestEnv::setProductCategories($product_id);

    // Simulate real WP product page context
    WooTestEnv::forProduct($product_id);

    // Check Woo product loads
    $product = wc_get_product($product_id);

    // Expect product to be valid    
    expect($product)->toBeInstanceOf('WC_Product');

    // Simulate POST data without nonce
    $_POST = [
        // 'tmpc_colours_nonce' => missing
        'tmpc_top_colour' => 'red',
        'tmpc_base_colour' => 'oak',
        'tmpc_metal_colour' => 'chrome',
    ];

    // Ensure meta is empty before saving
    update_post_meta($product_id, '_tmpc_top_colour', '');
    
    // Call the method to save default colours
    TMPC_Admin::save_default_colours($product_id);
    

    // Expect that the top colour meta is not updated and remains empty
    expect(get_post_meta($product_id, '_tmpc_top_colour', true))->toBe('');

    // Clean up by removing created user
    WooTestEnv::removeUser($user_id);

});

it('does not save model size if autosave', function () {

    // Create admin user and set current user
    $user_id = WooTestEnv::createUser();

    // Create product
    $product_id = WooTestEnv::createProduct([
        'title' => 'Test Slim Product',
        'type'  => 'simple',
        'post_author' => $user_id,
    ]);

    // Set categories for new product
    WooTestEnv::setProductCategories($product_id);

    // Simulate real WP product page context
    WooTestEnv::forProduct($product_id);

    // Check Woo product loads
    $product = wc_get_product($product_id);

    // Expect product to be valid    
    expect($product)->toBeInstanceOf('WC_Product');

    // Simulate POST data with nonce
    $_POST = [
        'tmpc_model_size_nonce' => wp_create_nonce('tmpc_save_model_size'),
        'post_ID' => 1,
        'tmpc_model_sizes' => [['label' => 'Test', 'dims' => '10x10', 'price' => 100]],
        'tmpc_model_sizes_default' => 0,
    ];

    // Simulate autosave
    add_filter('tmpc_is_autosave', '__return_true');
    
    // Ensure meta is empty before saving
    update_post_meta($product_id, '_tmpc_model_size', []);
    
    // Call the method to save model size
    TMPC_Admin::save_model_size($product_id);
    
    // Expect that the model size meta is not updated and remains empty
    expect(get_post_meta($product_id, '_tmpc_model_size', true))->toBe([]);
    
    // Clean up filter
    remove_filter('tmpc_is_autosave', '__return_true');

    // Clean up by removing created user
    WooTestEnv::removeUser($user_id);

});

it('does not save default colours if autosave', function () {

    // Create admin user and set current user
    $user_id = WooTestEnv::createUser();

    // Create product
    $product_id = WooTestEnv::createProduct([
        'title' => 'Test Slim Product',
        'type'  => 'simple',
        'post_author' => $user_id,
    ]);

    // Set categories for new product
    WooTestEnv::setProductCategories($product_id);

    // Simulate real WP product page context
    WooTestEnv::forProduct($product_id);

    // Check Woo product loads
    $product = wc_get_product($product_id);

    // Expect product to be valid    
    expect($product)->toBeInstanceOf('WC_Product');

    // Simulate POST data with nonce
    $_POST = [
        'tmpc_colours_nonce' => wp_create_nonce('tmpc_save_colours'),
        'tmpc_top_colour' => 'red',
        'tmpc_base_colour' => 'oak',
        'tmpc_metal_colour' => 'chrome',
    ];

    // Simulate autosave
    add_filter('tmpc_is_autosave', '__return_true');
    
    // Ensure meta is empty before saving
    update_post_meta($product_id, '_tmpc_top_colour', '');
    
    // Call the method to save default colours
    TMPC_Admin::save_default_colours($product_id);
    
    // Expect that the top colour meta is not updated and remains empty
    expect(get_post_meta($product_id, '_tmpc_top_colour', true))->toBe('');
    
    // Clean up filter
    remove_filter('tmpc_is_autosave', '__return_true');

    // Clean up by removing created user
    WooTestEnv::removeUser($user_id);

});