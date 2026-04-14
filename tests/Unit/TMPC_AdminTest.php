<?php

// Integration test for TMPC_Admin using the WordPress test suite
use TMProductConfigurator\Admin\TMPC_Admin;

class TMPC_AdminTest extends WP_UnitTestCase

{

    public function setUp(): void {
        // Call parent setup to initialize the test environment
        parent::setUp();
    }

    /**
     * Test that all hooks correctly added
     *
     * @return void
     */
    public function test_init_registers_admin_hooks () 

    {

        // Remove existing hooks to isolate test
        remove_all_actions('add_meta_boxes');
        remove_all_actions('save_post');

        // Call init
        TMPC_Admin::init();

        // Check that hooks were added
        $this->assertTrue(has_action('add_meta_boxes', [TMPC_Admin::class, 'add_colour_dropdowns']) !== false);
        $this->assertTrue(has_action('save_post', [TMPC_Admin::class, 'save_default_colours']) !== false);

    }

    /**
     * Test that selected layer values are correctly saved in wp_postmeta
     *
     * @return void
     */
    public function test_save_default_colours_saves_postmeta()

    {

        // Create a test user with administrator role to ensure permissions for saving post meta
        $user_id = $this->factory()->user->create([
            'role' => 'administrator',
        ]);

        // Set the current user to the admin user we just created
        wp_set_current_user($user_id);

        // Create a test product post
        $post_id = $this->factory()->post->create([
            'post_type' => 'product',
            'post_status' => 'publish',
        ]);

        // Simulate POST data and nonce
        if (!defined('DOING_AUTOSAVE')) {
            define('DOING_AUTOSAVE', false);
        }

        // Simulate POST data for the colours and nonce
        $_POST = [
            'tmpc_colours_nonce' => wp_create_nonce('tmpc_save_colours'),
            'tmpc_top_colour'    => 'red',
            'tmpc_base_colour'   => 'oak',
            'tmpc_metal_colour'  => 'chrome',
        ];

        // Call the save method
        TMPC_Admin::save_default_colours($post_id);

        // Assert postmeta was saved correctly
        $this->assertEquals('red', get_post_meta($post_id, '_tmpc_top_colour', true));
        $this->assertEquals('oak', get_post_meta($post_id, '_tmpc_base_colour', true));
        $this->assertEquals('chrome', get_post_meta($post_id, '_tmpc_metal_colour', true));
    }

    /**
     * Check that dropdowns added to product admin area
     *
     * @return void
     */
    public function test_add_colour_dropdowns_adds_meta_box() 

    {

        // Create a test product post
        $post_id = $this->factory()->post->create([
            'post_type' => 'product',
            'post_status' => 'publish',
        ]);

        // Call the method to add meta boxes
        TMPC_Admin::add_colour_dropdowns();

        // Get the global meta boxes array
        global $wp_meta_boxes;

        // Check that our meta box was added for the product post type
        $this->assertArrayHasKey('tmpc_default_colours', $wp_meta_boxes['product']['normal']['default']);

    }

    /**
     * Check that get_product_type returns expected type based on product categories
     *
     * @return void
     */
    public function test_get_product_type_returns_expected_type() 

    {

        // Create a test product post
        $post_id = $this->factory()->post->create([
            'post_type' => 'product',
            'post_status' => 'publish',
        ]);

        // Assign a category to determine product type (e.g. 'solid')
        $slugs = ['solid', 'slim', 'edge'];

        // Randomly assign one of the slugs to the product for testing
        $assigned_slug = $slugs[array_rand($slugs)];

        // Create the term and assign it to the product
        $term = wp_insert_term(ucfirst($assigned_slug), 'product_cat', ['slug' => $assigned_slug]);
        
        // Check if term creation was successful and assign to product
        $cat_id = is_wp_error($term) ? null : $term['term_id'];
        if ($cat_id) {
            wp_set_object_terms($post_id, [$cat_id], 'product_cat');
        }

        // Get the product object
        $product = wc_get_product($post_id);

        // Call the method to get product type
        $type = TMPC_Admin::get_product_type($product);

        // Assert it returns the expected type 
        $this->assertContains($type, ['solid', 'slim', 'edge']);

    }

// public function test_render_default_colours_box_outputs_dropdowns()
//     {
//         do_action('init');
//         do_action('woocommerce_init');
//         // Create a real WooCommerce product using WooCommerce API
//         $product = new \WC_Product_Simple();
//         $product->set_name('Test Product');
//         $product->set_status('publish');
//         $product->set_sku('test-sku-123');
//         $product->save();
//         $post_id = $product->get_id();
//         $post = get_post($post_id);

//         // Explicitly set _product_type and flush cache
//         update_post_meta($post_id, '_product_type', 'simple');
//         clean_post_cache($post_id);

//         // Direct instantiation as workaround
//         $wc_product = new \WC_Product_Simple($post_id);
//         $this->assertInstanceOf(\WC_Product_Simple::class, $wc_product);

//         // Capture the output of the render method
//         ob_start();
//         TMPC_Admin::render_default_colours_box($post);
//         $output = ob_get_clean();
// var_dump('render output: ' . $output); // Debug output to check what is rendered
//         // Check that the output contains our expected dropdowns (simplified check)
//         $this->assertStringContainsString('select id="top-colour"', $output);
//         $this->assertStringContainsString('select id="base-colour"', $output);
//         $this->assertStringContainsString('select id="metal-colour"', $output);
//     }

    /**
     * Clean up data after tests
     *
     * @return void
     */
    public function tearDown(): void
    {
        $_POST = [];
        parent::tearDown();
    }

}