<?php

use TMProductConfigurator\Product\TMPC_ProductData;
require_once __DIR__ . '/../Support/WooTestEnv.php';

function dummy_model_sizes() {
    return [
        [
            'label' => '200cm',
            'dims' => '200cm L x 105cm W x 77cm H',
            'price' => 0.0,
            'is_default' => true,
        ],
        [
            'label' => '220cm',
            'dims' => '220cm L x 105cm W x 77cm H',
            'price' => 100.0,
            'is_default' => false,
        ],
        [
            'label' => '250cm',
            'dims' => '250cm L x 120cm W x 77cm H',
            'price' => 800.0,
            'is_default' => false,
        ],
        [
            'label' => '300cm',
            'dims' => '300cm L x 130cm W x 77cm H',
            'price' => 1500.0,
            'is_default' => false,
        ],
    ];
}

it('returns defaults when no URL params are set', function () {

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
    expect($data['selected']['top']['name'])->toBe('viola rosso');
    expect($data['selected']['base']['name'])->toBe('american walnut');

    // Assert that metal colour is present and correct for this product type
    if(array_key_exists('metal', $data['selected'])) {
        expect($data['selected']['metal']['name'])->toBe('brushed bronze');
    }

    //fwrite(STDOUT, 'Product data: ' . print_r($data, true) . PHP_EOL);

    // Clean up test environment
    WooTestEnv::cleanup($product_id);

});

it('returns correct selected values from valid URL params', function () {

    // Create category
    $cat = get_term_by('slug', 'edge', 'product_cat');

    if (!$cat) {
        $cat_id = wp_insert_term('Edge', 'product_cat', ['slug' => 'edge']);
        $cat_id = is_array($cat_id) ? $cat_id['term_id'] : $cat_id;
    } else {
        $cat_id = $cat->term_id;
    }

    // Create product via helper
    $product_id = WooTestEnv::createProduct([
        'title' => 'Test Product',
        'type'  => 'simple',
    ]);

    // Assign category to product
    wp_set_object_terms($product_id, [$cat_id], 'product_cat');

    // Simulate WP product page context
    WooTestEnv::forProduct($product_id);

    // Set dummy model size data in post meta
    $dummy_model_sizes = dummy_model_sizes();

    // Update saved model sizes for product
    update_post_meta($product_id, '_tmpc_model_size', $dummy_model_sizes);

    // URL params (this is what you're testing)
    $_SERVER['REQUEST_URI'] =
        '/product/test-product?base=American%20Walnut&veneer=Brushed%20Bronze&colour=Viola%20Rosso&model=200cm';

    // Get product data
    $data = TMPC_ProductData::getProductData($product_id);

    // Assert that data and data['selected'] are arrays
    expect($data)->toBeArray();
    expect($data['selected'])->toBeArray();
    
    // Assert that selected colours match URL params (with spaces decoded)
    expect($data['selected']['top']['name'])->toBe('viola rosso');
    expect($data['selected']['base']['name'])->toBe('american walnut');
    if (array_key_exists('metal', $data['selected'])) {
        expect($data['selected']['metal']['name'])->toBe('brushed bronze');
    }

    // Cleanup
    WooTestEnv::cleanup($product_id);

});

it('corrects invalid colour combinations in URL params to valid values', function () {

    // Create category
    $cat = get_term_by('slug', 'edge', 'product_cat');

    if (!$cat) {
        $cat_id = wp_insert_term('Edge', 'product_cat', ['slug' => 'edge']);
        $cat_id = is_array($cat_id) ? $cat_id['term_id'] : $cat_id;
    } else {
        $cat_id = $cat->term_id;
    }

    // Create product via helper
    $product_id = WooTestEnv::createProduct([
        'title' => 'Test Product',
        'type'  => 'simple',
    ]);

    // Assign category to product
    wp_set_object_terms($product_id, [$cat_id], 'product_cat');

    // Simulate WP product page context
    WooTestEnv::forProduct($product_id);

    // Set dummy model size data in post meta
    $dummy_model_sizes = dummy_model_sizes();

    // Update saved model sizes for product
    update_post_meta($product_id, '_tmpc_model_size', $dummy_model_sizes);

    // URL params
    $_SERVER['REQUEST_URI'] =
        '/product/test-product?base=Moro&veneer=Brushed%20Bronze&colour=Viola%20Rosso&model=200cm';

    // Get product data
    $data = TMPC_ProductData::getProductData($product_id);

    // Assert that data and data['selected'] are arrays
    expect($data)->toBeArray();
    expect($data['selected'])->toBeArray();
    
    // Assert that selected top colour matches URL param
    expect($data['selected']['top']['name'])->toBe('viola rosso');
    
    // Assert that invalid base colour in URL param has been corrected 
    // to the first available option for the selected top colour (not 'moro')
    expect($data['selected']['base']['name'])->not()->toBe('moro');

    // Assert that metal colour is still correctly set from URL param
    if (array_key_exists('metal', $data['selected'])) {
        expect($data['selected']['metal']['name'])->toBe('brushed bronze');
    }

    // fwrite(STDOUT, 'Product data: ' . print_r($data, true) . PHP_EOL);

    // Cleanup
    WooTestEnv::cleanup($product_id);

});

it('returns correct model_sizes from post meta', function () {

    // Create category
    $cat = get_term_by('slug', 'slim', 'product_cat');

    // Create category if it doesn't exist
    if (!$cat) {
        $cat_id = wp_insert_term('Slim', 'product_cat', ['slug' => 'slim']);
        $cat_id = is_array($cat_id) ? $cat_id['term_id'] : $cat_id;
    } else {
        $cat_id = $cat->term_id;
    }
    $product_id = WooTestEnv::createProduct([
        'title' => 'Test Slim Product',
        'type'  => 'simple',
    ]);

    // Assign category to product
    wp_set_object_terms($product_id, [$cat_id], 'product_cat');
    
    // Simulate WP product page context
    WooTestEnv::forProduct($product_id);

    // Set dummy model size data in post meta
    $model_sizes = dummy_model_sizes();
    
    // Update post meta with dummy model sizes
    update_post_meta($product_id, '_tmpc_model_size', $model_sizes);
    
    // Get product data
    $data = TMPC_ProductData::getProductData($product_id);
    
    // Assert that model_sizes in product data matches the dummy data we set in post meta
    expect($data['model_sizes'])->toBe($model_sizes);
    
    // Cleanup
    WooTestEnv::cleanup($product_id);

});

it('calls ColourOptionsService and returns its data', function () {

    // Create category (using edge as it has metals)
    $cat = get_term_by('slug', 'edge', 'product_cat');
    
    // Create category if it doesn't exist
    if (!$cat) {
        $cat_id = wp_insert_term('Edge', 'product_cat', ['slug' => 'edge']);
        $cat_id = is_array($cat_id) ? $cat_id['term_id'] : $cat_id;
    } else {
        $cat_id = $cat->term_id;
    }

    // Create product via helper
    $product_id = WooTestEnv::createProduct([
        'title' => 'Test Edge Product',
        'type'  => 'simple',
    ]);

    // Assign category to product
    wp_set_object_terms($product_id, [$cat_id], 'product_cat');
    
    // Simulate WP product page context
    WooTestEnv::forProduct($product_id);
    
    // Set dummy model size data in post meta
    $dummy_model_sizes = dummy_model_sizes();

    // Update saved model sizes for product
    update_post_meta($product_id, '_tmpc_model_size', $dummy_model_sizes);
    
    // Get product data, which should internally call ColourOptionsService to populate colour options
    $data = TMPC_ProductData::getProductData($product_id);
    
    // Assert that colour options are present
    expect($data)->toHaveKey('colour_options');
    
    // Expect colour options to not be empty
    expect($data['colour_options'])->not()->toBeEmpty();
    
    // Assert that we have a top colour
    expect($data['colour_options'])->toHaveKey('viola_rosso');
    
    // Assert that the colour option has top/base/metal options
    expect($data['colour_options']['viola_rosso'])->toHaveKeys(['top', 'base', 'metal']);
    
    // Assert that top/base/metal options have expected structure (name, slug, id, url)
    expect($data['colour_options']['viola_rosso']['top'])->toBeArray();
    expect($data['colour_options']['viola_rosso']['top'])->toHaveKeys(['name', 'slug', 'id', 'url']);
    expect($data['colour_options']['viola_rosso']['base'])->toBeArray();
    if(array_key_exists('metal', $data['colour_options']['viola_rosso'])) {
        expect($data['colour_options']['viola_rosso']['metal'])->toBeArray();
    }

    fwrite(STDOUT, 'Product data: ' . print_r($data['colour_options']['viola_rosso'], true) . PHP_EOL);
    
    // Cleanup
    WooTestEnv::cleanup($product_id);
});

it('only allows valid base/metal for selected top', function () {

    // Create category
    $cat = get_term_by('slug', 'edge', 'product_cat');

    // Create category if it doesn't exist
    if (!$cat) {
        $cat_id = wp_insert_term('Edge', 'product_cat', ['slug' => 'edge']);
        $cat_id = is_array($cat_id) ? $cat_id['term_id'] : $cat_id;
    } else {
        $cat_id = $cat->term_id;
    }

    // Create product via helper
    $product_id = WooTestEnv::createProduct([
        'title' => 'Test Product',
        'type'  => 'simple',
    ]);

    // Assign category to product
    wp_set_object_terms($product_id, [$cat_id], 'product_cat');

    // Simulate WP product page context
    WooTestEnv::forProduct($product_id);

    // Set dummy model size data in post meta
    $dummy_model_sizes = dummy_model_sizes();

    // Update saved model sizes for product
    update_post_meta($product_id, '_tmpc_model_size', $dummy_model_sizes);
    
    // Simulate invalid base/metal in URL params
    $_SERVER['REQUEST_URI'] = '/product/test-product?base=InvalidBase&veneer=InvalidMetal&colour=Viola%20Rosso&model=200cm';
    $data = TMPC_ProductData::getProductData($product_id);
    
    // Assert that base/metal have been corrected to valid options
    expect($data['selected']['base'])->not()->toBe('invalidbase');
    if (array_key_exists('metal', $data['selected'])) {
        expect($data['selected']['metal'])->not()->toBe('invalidmetal');
    }

    fwrite(STDOUT, 'Product data: ' . print_r($data['selected'], true) . PHP_EOL);

    // Cleanup
    WooTestEnv::cleanup($product_id);

});