<?php 

    namespace TMProductConfigurator\Product;

    use TMProductConfigurator\ColourOptions\TMPC_ColourOptionsService;

    /**
     * class to handle product data and globally distribute it
     */
    class TMPC_ProductData {

        protected static $product_data = [];

        /**
         * Get product data to be used in config drawers and current status display
         *
         * @return array Returns array of product data
         */
        public static function getProductData() {

            // Set product data globally
            self::$product_data = TMPC_ColourOptionsService::getColourOptionsRaw('master');

            // Get the current product data to be used in config drawers and current status display
            $data = self::$product_data;

            // Check initial state of product based on URL params or defaults, 
            $data['selected'] = self::productInitialState();

            // Return product data
            return $data;

        }

        /**
         * Check url for params or if none determine default values from postmeta
         *
         * @return array Returns array of selected options to be used for image layer rendering and current status display
         */
        public static function productInitialState() {

            // Parse URL query to get selected options (if any)
            $query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);

            // If colour param exists in URL 
            if (!empty($query)) {

                // Set product data based on URL params, with fallbacks to defaults if params are missing or invalid
                return self::setProductDataFromURL($query);

            } else {

                // If no colour param, fallback to defaults (if set) or empty
                return self::setDefaultProductData();

            }

        }

        /**
         * Set product data based on URL params, 
         * with fallbacks to defaults if params are missing or invalid
         *
         * @param string $query The URL query string
         * @return array Returns array of selected options to be used for image layer rendering and current status display
         */
        public static function setProductDataFromURL($query) {

            // Parse URL query to get selected options (if any)
            parse_str($query, $params);

            // Get current product
            $product = wc_get_product(get_the_ID());

            // If no product found, return empty to avoid errors
            if (!$product) return [];

            // Normalise inputs
            $colour = self::normalise(str_replace('_', ' ', $params['colour'] ?? null));
            $base   = self::normalise(str_replace('_', ' ', $params['base'] ?? null));
            $metal  = self::normalise(str_replace('_', ' ', $params['metal'] ?? null));

            // Get allowed options for the selected top colour to validate base and metal selections against
            $allowedColoursForTop = self::findByTopName($colour);

            // Validate against available options
            $base  = self::validateOrFallback($base,  $allowedColoursForTop['base']  ?? []);
            $metal = self::validateOrFallback($metal, $allowedColoursForTop['metal'] ?? []);

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
         * @return array Returns array of selected options to be used for image layer rendering and current status display
         * 
         */
        public static function setDefaultProductData() {

            // If no colour param, fallback to defaults (if set) or empty
            $post_id = get_the_ID();
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