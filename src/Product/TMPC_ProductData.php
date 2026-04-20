<?php 

    namespace TMProductConfigurator\Product;

    use TMProductConfigurator\ColourOptions\TMPC_ColourOptionsService;

    /**
     * class to handle product data and globally distribute it
     */
    class TMPC_ProductData {

        public static function init() {
            // This class is responsible for handling product data and making it globally available for other classes to use
        }

        /**
         * Get product data to be used in config drawers and current status display
         *
         * @return array Returns array of product data
         */
        public static function getProductData() {

            // Array to hold final product data
            $product_data = [];
            
            // Check initial state of product based on URL params or defaults, 
            $product_data['selected'] = self::productInitialState();

            // Get product data to be used in config drawers and current status display
            $product_data['colour_options'] = TMPC_ColourOptionsService::getColourOptionsRaw();

            // Return product data
            return $product_data;

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

            // Get available options for this product to validate against
            $available = self::getAvailableColoursForProduct($product);

            // Normalise inputs
            $colour = self::normalise($params['colour'] ?? null, '_');
            $base   = self::normalise($params['base'] ?? null);
            $metal  = self::normalise($params['veneer'] ?? null);

            $allowedColoursForTop = self::findByTopName($available, $colour);

            // Validate against available options
            $base  = self::validateOrFallback('base', $base,  $allowedColoursForTop['base']  ?? []);
            $metal = self::validateOrFallback('metal', $metal, $allowedColoursForTop['metal'] ?? []);

            return [
                'top'   => $colour ? str_replace('_', '-', $colour) : null,
                'base'  => $base,
                'metal' => $metal,
            ];
        }

        /**
         * Helper function to find the allowed options for a given top colour
         *
         * @param array $array
         * @param string $name
         * @return array|null Returns the allowed options for the given top colour or null if not found
         */
        private static function findByTopName(array $array, string $name) {

            foreach ($array as $item) {
                if (isset($item['top']['name']) && $item['top']['name'] === $name) {
                    return $item;
                }
            }

            return null;

        }

        private static function normalise($value, $space_replace = null) {

            if (!$value) return null;

            $value = sanitize_text_field(wp_unslash($value));
            $value = strtolower($value);

            if ($space_replace) {
                $value = str_replace(' ', $space_replace, $value);
            }

            return $value;
        }

        /**
         * Validate a value against allowed options, with fallback to default if invalid
         *
         * @param string $type The type of value being validated (e.g., 'base', 'metal')
         * @param mixed $value The value to validate
         * @param array $allowed The array of allowed values
         * @return mixed Returns the validated value or a fallback default if the value is invalid
         */
        private static function validateOrFallback($type, $value, $allowed) {

            // Extract name value pairs from allowed options for easier validation
            $allowedNames = array_column($allowed, 'name');
 
            if (!is_array($allowed) || empty($allowed)) {
                return null;
            }

            return in_array($value, $allowedNames, true)
                ? $value
                : ($allowed[0]['name'] ?? null);
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

        /**
         * Get colour data for a specific product to be used in config drawers, 
         * based on product type and available options
         *
         * @param object $product
         * @return array Returns available colour options for the product
         */
        public static function getAvailableColoursForProduct($product) {

            // Use admin colour format for colours as this groups options by product type
            $colourOptions = TMPC_ColourOptionsService::getColourOptionsRaw();

            // Determine product type (slim, solid, edge)
            $productType = TMPC_ColourOptionsService::get_product_type($product);
            
            // Return options for this product type, or empty array if no options found
            return $colourOptions[$productType] ?? [];
            
        }

    }