<?php

use TMProductConfigurator\Product\TMPC_ProductData;
require_once __DIR__ . '/../Support/WooTestEnv.php';

it('returns defaults when no URL params or meta are set', function () {

    //fwrite(STDERR, "WooCommerce loaded: " . (class_exists('WooCommerce') ? 'yes' : 'no') . "\n");

    // Ensure category exists
    $cat = get_term_by('slug', 'slim', 'product_cat');

    // Create category if it doesn't exist
    if (!$cat) {
        $cat_id = wp_insert_term('Slim', 'product_cat', ['slug' => 'slim']);
        $cat_id = is_array($cat_id) ? $cat_id['term_id'] : $cat_id;
    } else {
        $cat_id = $cat->term_id;
    }

    // Create product
    $product_id = WooTestEnv::createProduct([
        'title' => 'Test Slim Product',
        'type'  => 'simple',
    ]);

    // Assign category to product
    wp_set_object_terms($product_id, [$cat_id], 'product_cat');

    // Simulate real WP product page context
    WooTestEnv::forProduct($product_id);

    // Check Woo product loads
    $product = wc_get_product($product_id);

    //fwrite(STDOUT, 'wc_get_product: ' . (is_object($product) ? 'object' : 'false') . PHP_EOL);

    // Expect product to be valid object, otherwise the rest of the test will fail with errors rather than a clear failure message
    expect($product)->not->toBeFalse('WooCommerce product failed to initialise');

    // meta defaults
    update_post_meta($product_id, '_tmpc_top_colour', 'viola rosso');
    update_post_meta($product_id, '_tmpc_base_colour', 'american walnut');
    update_post_meta($product_id, '_tmpc_metal_colour', 'brushed bronze');

    // Get product data
    $data = TMPC_ProductData::getProductData($product_id);

    // Assert that data and data['selected'] are arrays
    expect($data)->toBeArray();
    expect($data['selected'])->toBeArray();

    // Assert that default colours are the values that we set earlier
    expect($data['selected']['top'])->toBe('viola rosso');
    expect($data['selected']['base'])->toBe('american walnut');
    expect($data['selected']['metal'])->toBe('brushed bronze');

    //fwrite(STDOUT, 'Product data: ' . print_r($data, true) . PHP_EOL);

    // Clean up test environment
    WooTestEnv::cleanup($product_id);

});