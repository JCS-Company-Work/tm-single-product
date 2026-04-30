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