<?php

    class WooTestEnv

    {
        /**
         * Ensure WooCommerce product environment is correctly set up for testing
         *
         * @param integer $product_id
         * @return void
         */
        public static function forProduct(int $product_id): void
        
        {
            // Ensure Woo product cache is clean
            if (function_exists('wc_delete_product_transients')) {
                wc_delete_product_transients($product_id);
            }

            clean_post_cache($product_id);

            // Set global WP post context
            global $post;

            $post = get_post($product_id);

            if ($post) {
                setup_postdata($post);
            }

            // Ensure Woo lifecycle is stable
            do_action('wp_loaded');

        }

        /**
         * Create new admin user for tests
         *
         * @return integer User ID of created user
         */
        public static function createUser() {
            
            // Create admin user for testing
            $user_id = wp_create_user('test_user', 'password', 'admin@example.com');
          
            // Set user role to administrator
            $user = new WP_User($user_id);
        
            // Set the role to administrator
            $user->set_role('administrator');
        
            // Set the current user to the newly created admin
            wp_set_current_user($user_id);

            // Return user ID
            return $user_id;

        }

        /**
         * Clean up after tests by removing created user
         *
         * @param integer $user_id
         * @return void
         */
        public static function removeUser($user_id) {
            wp_delete_user($user_id);
        }

        /**
         * Woocommerce product factory
         *
         * @param array $args
         * @return integer
         */
        public static function createProduct(array $args = []): int
    
        {
            $product_id = wp_insert_post([
                'post_title'  => $args['title'] ?? 'Test Product',
                'post_type'   => 'product',
                'post_status' => 'publish',
                'post_author' => $args['post_author'] ?? 1, // Default to user ID 1 if not provided
            ]);

            // Product type taxonomy (Woo requirement)
            wp_set_object_terms(
                $product_id,
                $args['type'] ?? 'simple',
                'product_type'
            );

            // Basic Woo meta (only what you actually need in most tests)
            update_post_meta($product_id, '_price', '10');
            update_post_meta($product_id, '_regular_price', '10');

            // Clean caches so Woo sees it immediately
            clean_post_cache($product_id);

            if (function_exists('wc_delete_product_transients')) {
                wc_delete_product_transients($product_id);
            }

            // Return product id after creation
            return $product_id;
        }

        /**
         * Set categories for newly created product
         *
         * @param string $product_id
         * @return void
         */
        public static function setProductCategories($product_id) {

            // Ensure category exists
            $cat = get_term_by('slug', 'slim', 'product_cat');

            // Create category if it doesn't exist
            if (!$cat) {
                $cat_id = wp_insert_term('Slim', 'product_cat', ['slug' => 'slim']);
                $cat_id = is_array($cat_id) ? $cat_id['term_id'] : $cat_id;
            } else {
                $cat_id = $cat->term_id;
            }

            // Assign category to product
            wp_set_object_terms($product_id, [$cat_id], 'product_cat');

        }

        /**
         * Clean up WooCommerce product environment after testing
         *
         * @param integer $product_id
         * @return void
         */
        public static function cleanup(int $product_id): void

        {
            wp_delete_post($product_id, true);
            wp_reset_postdata();
        }
    }