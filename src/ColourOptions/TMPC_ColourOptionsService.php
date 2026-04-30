<?php

    namespace TMProductConfigurator\ColourOptions;

    use TMProductConfigurator\ColourOptions\TMPC_ColourOptionsData;

    /**
     * Service class to handle fetching colour options from cache or Google Sheets
     * and return it in the expected format for frontend use.
     */
    class TMPC_ColourOptionsService {

        public static function getColourOptionsRaw($type = 'standard') {

            // Get product ID from URL query (if provided) to determine product type for fetching relevant options
            $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
            
            // Get product object (if product_id provided) to determine type for fetching relevant options
            $product = $product_id ? wc_get_product($product_id) : wc_get_product();

            // Determine product type to fetch relevant options
            $product_type = self::get_product_type($product);

            // Set cache key based on type
            $cache_key = $type === 'master' ? 'tmpc_colour_options_' . $product_type . '_master' : 'tmpc_colour_options_' . $product_type;
            
            // $cached = get_transient($cache_key);

            // // If cached data exists, return it
            // if ($cached !== false) {
            //     return $cached;
            // }
            // If no cached data, fetch from Google Sheets (internal call, bypass token)
            TMPC_ColourOptionsData::getDataFromGoogleSheets(true);

            // Return the freshly cached data
            return get_transient($cache_key);
        }

        /**
         * Fetches colour options from cache or Google Sheets if cache is empty.
         *
         * @return \WP_REST_Response
         */
        public static function getColourOptions() {
            return rest_ensure_response(self::getColourOptionsRaw('standard'));
        }

        /**
         * Get raw colour options for admin (unformatted)
         *
         * @return mixed Returns cached data or false if cache is empty
         */
        // public static function getAdminColourOptions() {

        //     // Get cached data from transient
        //     $cache_key = 'tmpc_admin_sheets_data';
        //     // $cached = get_transient($cache_key);

        //     // // If cached data exists, return it
        //     // if ($cached !== false) {
        //     //     return $cached; // NOTE: no REST response needed internally
        //     // }

        //     // Populate cache if missing
        //     TMPC_ColourOptionsData::getDataFromGoogleSheets(true);

        //     // Return the newly cached data
        //     return get_transient($cache_key);

        // }

        /**
         * Determine product type from WP categories
         *
         * @param object $product
         * @return string|null Returns 'solid', 'slim', 'edge' or null if no match
         */
        public static function get_product_type($product) {

            // Guard against invalid product
            if (!$product || !is_object($product) || !method_exists($product, 'get_id')) {
                return null;
            }

            // Get product category slugs
            $terms = get_the_terms($product->get_id(), 'product_cat');

            if (empty($terms) || is_wp_error($terms)) {
                return null;
            }

            // Define slugs of types to check
            $slugs = ['solid', 'slim', 'edge'];

            // Return the slug of the first matching category (ensure term_id is cast to int for comparison)
            foreach($terms as $term) {
                if (in_array($term->slug, $slugs)) {
                    return $term->slug; 
                }

            }

            // Return null if no matching category found
            return null; 

        }

    }