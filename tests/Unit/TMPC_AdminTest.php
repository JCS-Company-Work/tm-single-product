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
        add_filter('tmpc_is_autosave', '__return_false');

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

        remove_filter('tmpc_is_autosave', '__return_false');
    }

    /**
     * Test save_default_colours does not save if nonce is missing
     */
    public function test_save_default_colours_does_not_save_if_nonce_missing()

    {
    
        // Create a test user with administrator role to ensure permissions for saving post meta
        $user_id = $this->factory()->user->create(['role' => 'administrator']);
        
        // Set the current user to the admin user we just created
        wp_set_current_user($user_id);
        
        // Create a test product post
        $post_id = $this->factory()->post->create(['post_type' => 'product', 'post_status' => 'publish']);
        
        // Simulate POST data without nonce
        $_POST = [
            'tmpc_top_colour'    => 'red',
            'tmpc_base_colour'   => 'oak',
            'tmpc_metal_colour'  => 'chrome',
        ];
        
        // Call the save method
        TMPC_Admin::save_default_colours($post_id);
        
        // Assert postmeta was not saved due to missing nonce
        $this->assertEmpty(get_post_meta($post_id, '_tmpc_top_colour', true));
        $this->assertEmpty(get_post_meta($post_id, '_tmpc_base_colour', true));
        $this->assertEmpty(get_post_meta($post_id, '_tmpc_metal_colour', true));

    }

    /**
     * Test save_default_colours does not save if nonce is invalid
     */
    public function test_save_default_colours_does_not_save_if_nonce_invalid()

    {
        
        // Create a test user with administrator role to ensure permissions for saving post meta
        $user_id = $this->factory()->user->create(['role' => 'administrator']);
        
        // Set the current user to the admin user we just created
        wp_set_current_user($user_id);
        
        // Create a test product post
        $post_id = $this->factory()->post->create(['post_type' => 'product', 'post_status' => 'publish']);
        
        // Simulate POST data with invalid nonce
        $_POST = [
            'tmpc_colours_nonce' => 'invalid',
            'tmpc_top_colour'    => 'red',
            'tmpc_base_colour'   => 'oak',
            'tmpc_metal_colour'  => 'chrome',
        ];
        
        // Call the save method
        TMPC_Admin::save_default_colours($post_id);
        
        // Assert postmeta was not saved due to invalid nonce
        $this->assertEmpty(get_post_meta($post_id, '_tmpc_top_colour', true));
        $this->assertEmpty(get_post_meta($post_id, '_tmpc_base_colour', true));
        $this->assertEmpty(get_post_meta($post_id, '_tmpc_metal_colour', true));

    }

    /**
     * Test save_default_colours does not save if user cannot edit post
     */
    public function test_save_default_colours_does_not_save_if_user_cannot_edit()

    {

        // Create a test user with subscriber role (no edit permissions)
        $user_id = $this->factory()->user->create(['role' => 'subscriber']);
    
        // Set the current user to the subscriber user we just created
        wp_set_current_user($user_id);
    
        // Create a test product post
        $post_id = $this->factory()->post->create(['post_type' => 'product', 'post_status' => 'publish']);
    
        // Simulate POST data and nonce
        $_POST = [
            'tmpc_colours_nonce' => wp_create_nonce('tmpc_save_colours'),
            'tmpc_top_colour'    => 'red',
            'tmpc_base_colour'   => 'oak',
            'tmpc_metal_colour'  => 'chrome',
        ];
    
        // Call the save method
        TMPC_Admin::save_default_colours($post_id);
    
        // Assert postmeta was not saved due to insufficient permissions
        $this->assertEmpty(get_post_meta($post_id, '_tmpc_top_colour', true));
        $this->assertEmpty(get_post_meta($post_id, '_tmpc_base_colour', true));
        $this->assertEmpty(get_post_meta($post_id, '_tmpc_metal_colour', true));

    }

    /**
     * Test save_default_colours does not save if DOING_AUTOSAVE is true
     */
    public function test_save_default_colours_does_not_save_if_doing_autosave()
    
    {
    
        // Create a test user with administrator role to ensure permissions for saving post meta
        $user_id = $this->factory()->user->create(['role' => 'administrator']);
        
        // Set the current user to the admin user we just created
        wp_set_current_user($user_id);
        
        // Create a test product post
        $post_id = $this->factory()->post->create(['post_type' => 'product', 'post_status' => 'publish']);
        
        // Define DOING_AUTOSAVE as true to simulate autosave scenario
        add_filter('tmpc_is_autosave', '__return_true');
        
        // Simulate POST data for the colours and nonce
        $_POST = [
            'tmpc_colours_nonce' => wp_create_nonce('tmpc_save_colours'),
            'tmpc_top_colour'    => 'red',
            'tmpc_base_colour'   => 'oak',
            'tmpc_metal_colour'  => 'chrome',
        ];
        
        // Call the save method
        TMPC_Admin::save_default_colours($post_id);
        
        // Assert postmeta was not saved due to autosave
        $this->assertEmpty(get_post_meta($post_id, '_tmpc_top_colour', true));
        $this->assertEmpty(get_post_meta($post_id, '_tmpc_base_colour', true));
        $this->assertEmpty(get_post_meta($post_id, '_tmpc_metal_colour', true));

        remove_filter('tmpc_is_autosave', '__return_true');

    }

    /**
     * Test save_default_colours does not save if required POST fields are missing
     */
    public function test_save_default_colours_does_not_save_if_fields_missing()
    
    {
        
        // Create a test user with administrator role to ensure permissions for saving post meta
        $user_id = $this->factory()->user->create(['role' => 'administrator']);
        
        // Set the current user to the admin user we just created
        wp_set_current_user($user_id);
        
        // Create a test product post
        $post_id = $this->factory()->post->create(['post_type' => 'product', 'post_status' => 'publish']);
        
        // Simulate POST data with nonce but missing colour fields
        $_POST = [
            'tmpc_colours_nonce' => wp_create_nonce('tmpc_save_colours'),
        ];
        
        // Call the save method
        TMPC_Admin::save_default_colours($post_id);
        
        // Assert postmeta was not saved
        $this->assertEmpty(get_post_meta($post_id, '_tmpc_top_colour', true));
        $this->assertEmpty(get_post_meta($post_id, '_tmpc_base_colour', true));
        $this->assertEmpty(get_post_meta($post_id, '_tmpc_metal_colour', true));

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

    /**
     * Test get_product_type returns null if product has no categories
     */
    public function test_get_product_type_returns_null_for_no_categories()

    {
    
        // Create a test product post with no categories
        $post_id = $this->factory()->post->create([
            'post_type' => 'product',
            'post_status' => 'publish',
        ]);

        // Get the product object
        $product = wc_get_product($post_id);
        
        // Call the method to get product type
        $type = TMPC_Admin::get_product_type($product);
        
        // Assert it returns null when there are no categories
        $this->assertNull($type);

    }

    /**
     * Test get_product_type returns null if product has unrelated category
     */
    public function test_get_product_type_returns_null_for_unrelated_category()

    {
    
        // Create a test product post
        $post_id = $this->factory()->post->create([
            'post_type' => 'product',
            'post_status' => 'publish',
        ]);
        
        // Assign a category that is not in our expected list (e.g. 'other')
        $term = wp_insert_term('Other', 'product_cat', ['slug' => 'other']);
        
        // Check if term creation was successful and assign to product
        $cat_id = is_wp_error($term) ? null : $term['term_id'];
        if ($cat_id) {
            wp_set_object_terms($post_id, [$cat_id], 'product_cat');
        }
        
        // Get the product object
        $product = wc_get_product($post_id);
        
        // Call the method to get product type
        $type = TMPC_Admin::get_product_type($product);
        
        // Assert it returns null when categories do not match expected types
        $this->assertNull($type);

    }

    /**
     * Test that render_default_colours_box outputs dropdowns for valid product/category (by slug)
     */
    public function test_render_default_colours_box_outputs_dropdowns_for_valid_product_slug()
    
    {
        // Create product and assign valid category (slug 'solid')
        $post_id = $this->factory()->post->create([
            'post_type' => 'product',
            'post_status' => 'publish',
        ]);

        // Create the term and assign it to the product
        $term = wp_insert_term('Solid', 'product_cat', ['slug' => 'solid']);
        
        // Check if term creation was successful and assign to product
        $cat_id = is_wp_error($term) ? null : $term['term_id'];
        if ($cat_id) {
            wp_set_object_terms($post_id, [$cat_id], 'product_cat');
        }
        
        // Get the post object
        $post = get_post($post_id);
        
        // Simulate POST nonce
        $_POST['tmpc_colours_nonce'] = wp_create_nonce('tmpc_save_colours');
        
        // Capture output
        ob_start();
        
        // Call the render method
        TMPC_Admin::render_default_colours_box($post);
        
        // Get the output and clean buffer
        $output = ob_get_clean();
        
        // Assert that the output contains our expected dropdowns
        $this->assertStringContainsString('select id="top-colour"', $output);
        $this->assertStringContainsString('select id="base-colour"', $output);
        $this->assertStringContainsString('select id="metal-colour"', $output);

    }

    /**
     * Test that render_default_colours_box does not output dropdowns for invalid product/category (by slug)
     */
    public function test_render_default_colours_box_outputs_nothing_for_invalid_product_slug()

    {
        // Create product with no valid category
        $post_id = $this->factory()->post->create([
            'post_type' => 'product',
            'post_status' => 'publish',
        ]);

        // Assign a category that is not in our expected list (e.g. 'other')
        $term = wp_insert_term('Other', 'product_cat', ['slug' => 'other']);
        
        // Check if term creation was successful and assign to product
        $cat_id = is_wp_error($term) ? null : $term['term_id'];
        if ($cat_id) {
            wp_set_object_terms($post_id, [$cat_id], 'product_cat');
        }

        // Get the post object
        $post = get_post($post_id);
        
        // Simulate POST nonce
        $_POST['tmpc_colours_nonce'] = wp_create_nonce('tmpc_save_colours');
        
        // Capture output
        ob_start();
        
        // Call the render method
        TMPC_Admin::render_default_colours_box($post);
        
        // Get the output and clean buffer
        $output = ob_get_clean();
        
        // Assert that the output does not contain our expected dropdowns
        $this->assertStringNotContainsString('select id="top-colour"', $output);
        $this->assertStringNotContainsString('select id="base-colour"', $output);
        $this->assertStringNotContainsString('select id="metal-colour"', $output);

    }

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