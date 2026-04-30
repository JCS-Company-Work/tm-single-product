<?php 

    namespace TMProductConfigurator\Product;

    use TMProductConfigurator\ColourOptions\TMPC_ColourOptionsService;

    /**
     * class to handle product data and globally distribute it
     */
    class TMPC_ProductData {

        /**
         * Variable to hold global product data
         *
         * @var array
         */
        protected static $product_data = [];

        /**
         * Get product data to be used in config drawers and current status display
         *
         * @param int|null $product_id The ID of the product (optional)
         * @return array Returns array of product data
         */
        public static function getProductData($product_id = null) {

            // Get product ID from URL query (if provided) to determine product type for fetching relevant options
            $product_id = $product_id ?? get_the_ID();
            
            // Get product object to determine type for fetching relevant options, guard against invalid product
            $product = function_exists('wc_get_product') ? wc_get_product($product_id) : null;

            // Exit early if no valid product found to avoid errors and return default data
            // to ensure config drawers and current status display can still function
            if (!$product) {
                return [
                    'selected'     => self::setDefaultProductData($product_id),
                    'model_sizes'  => [],
                    'colour_options' => [],
                ];
            }

            // Set product data globally
            self::$product_data = TMPC_ColourOptionsService::getColourOptionsRaw('master');

            // Get the current product data to be used in config drawers and current status display
            $data = self::$product_data;

            // Check initial state of product based on URL params or defaults, 
            $data['selected'] = self::productInitialState($product_id);

            // Get model size data from post meta
            $data['model_sizes'] = get_post_meta($product_id, '_tmpc_model_size', true);

            // Return product data
            return $data;

        }

        /**
         * Check url for params or if none determine default values from postmeta
         *
         * @param int|null $product_id The ID of the product (optional)
         * @param string|null $request_uri The request URI to parse for URL params (optional, defaults to server REQUEST_URI)
         * @return array Returns array of selected options to be used for image layer rendering and current status display
         */

        public static function productInitialState($product_id = null, $request_uri = null) {

            // Resolve product ID once
            $product_id = $product_id ?: get_the_ID();

            // Safely resolve request URI (works in CLI/tests too)
            $request_uri = $request_uri ?? ($_SERVER['REQUEST_URI'] ?? '');

            // Extract query string
            $query = $request_uri ? parse_url($request_uri, PHP_URL_QUERY) : null;

            if ($query) {
                parse_str($query, $params);

                // Only trigger URL logic if relevant params exist
                if (
                    !empty($params['colour']) ||
                    !empty($params['base']) ||
                    !empty($params['metal'])
                ) {
                    return self::setProductDataFromURL($params, $product_id);
                }
            }

            // Fallback
            return self::setDefaultProductData($product_id);
        }

        /**
         * Set product data based on URL params, 
         * with fallbacks to defaults if params are missing or invalid
         *
         * @param array $params The URL parameters to set product data from
         * @param int|null $product_id The ID of the product (optional)
         * @return array Returns array of selected options to be used for image layer rendering and current status display
         */
        public static function setProductDataFromURL($params, $product_id = null) {

            // Get product ID
            $product_id = $product_id ?: get_the_ID();
            
            // Get WC product object
            $product = wc_get_product($product_id);

            // If no product found, fallback safely
            if (!$product) {
                return self::setDefaultProductData($product_id);
            }

            // Normalise inputs
            $colour = self::normalise(str_replace('_', ' ', $params['colour'] ?? null));
            $base   = self::normalise(str_replace('_', ' ', $params['base'] ?? null));
            $metal  = self::normalise(str_replace('_', ' ', $params['veneer'] ?? null));

            // Validate top
            $allowedColoursForTop = self::findByTopName($colour);

            if (!$allowedColoursForTop) {
                return self::setDefaultProductData($product_id);
            }

            // Safe extraction
            $allowedBase  = $allowedColoursForTop['base']  ?? [];
            $allowedMetal = $allowedColoursForTop['metal'] ?? [];

            // Validate children
            $base  = self::validateOrFallback($base, $allowedBase);
            $metal = self::validateOrFallback($metal, $allowedMetal);

            return [
                'top'   => $colour,
                'base'  => $base,
                'metal' => $metal,
            ];
        }

        /**
         * Helper function to find the allowed options for a given top colour
         *
         * @param string $name
         * @return array|null Returns the allowed options for the given top colour or null if not found
         */
        private static function findByTopName(string $name) {

            // Loop through colour options to find the one matching the given top colour name
            foreach (self::$product_data['colour_options'] as $item) {

                // When match found, return the current selected top colour array 
                // which contains the allowed base and metal options for that top colour           
                if (isset($item['top']['name']) && $item['top']['name'] === $name) { 
                    return $item;
                }

            }

            // If no match found, return null
            return null;

        }

        /**
         * Method to normalise input values by sanitising, converting to 
         * lowercase and replacing spaces with a specified character (if needed)
         *
         * @param string|null $value The value to normalise
         * @param string|null $space_replace The character to replace spaces with (optional)
         * @return string|null Returns the normalised value or null if input is empty
         */
        private static function normalise($value, $space_replace = null) {

            // If value is empty or null, return null
            if (!$value) return null;

            // Sanitize the value to prevent security issues
            $value = sanitize_text_field(wp_unslash($value));
            $value = strtolower($value);

            // Replace spaces with specified character if provided
            if ($space_replace) {
                $value = str_replace(' ', $space_replace, $value);
            }

            // Return the normalised value
            return $value;

        }

        /**
         * Validate a value against allowed options, with fallback to default if invalid
         *
         * @param mixed $value The value to validate
         * @param array $allowed The array of allowed values
         * @return mixed Returns the validated value or a fallback default if the value is invalid
         */
        private static function validateOrFallback($value, $allowed) {
 
            // If no allowed options provided, return null to avoid invalid selections
            if (!is_array($allowed) || empty($allowed)) {
                return null;
            }

            return in_array($value, $allowed, true)
                ? $value
                : ($allowed[0] ?? null);
        }

        /**
         * Set product data based on URL params, with fallbacks to defaults if params are missing or invalid
         * @param int|null $product_id The ID of the product (optional)
         * @return array Returns array of selected options to be used for image layer rendering and current status display
         * 
         */
        public static function setDefaultProductData($product_id = null) {

            // If no colour param, fallback to defaults (if set) or empty
            $post_id = $product_id ?? get_the_ID();
            $fields = [
                'top'   => '_tmpc_top_colour',
                'base'  => '_tmpc_base_colour',
                'metal' => '_tmpc_metal_colour',
            ];

            // Loop through fields and get values from post meta, set to image layers
            $image_layers = [];

            foreach ($fields as $key => $meta_key) {
                $image_layers[$key] = get_post_meta($post_id, $meta_key, true);
            }

            return $image_layers;

        }

    }