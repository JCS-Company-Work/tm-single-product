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
         * Set the global product data instance
         * @param array $data
         */
        public static function setProductData($data) {
            self::$product_data = $data;
        }

        /**
         * Get the global product data instance
         * @return array
         */
        public static function getProductDataInstance() {
            return self::$product_data;
        }

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

            // Display either tile or wood bases depending on whether product is in wood category 
            $data = self::filterDataByWoodCategory($data, $product);

            // Check initial state of product based on URL params or defaults, 
            $data['selected'] = self::productInitialState($product_id);

            // Get model size data from post meta and check for URL overide value
            $data['model_sizes'] = self::setModelSizes($product_id, $data['selected']['model'] ?? null);

            // Return product data
            return $data;

        }

        /**
         * Check if model is set in URL and use that if it is, otherwise fallback to database defaults
         *
         * @param int $product_id The ID of the product
         * @param string|null $selected_model The selected model from URL or null
         * @return array Returns array of model sizes
         */
        public static function setModelSizes($product_id, $selected_model = null) {

            // Get product model sizes from database
            $model_sizes = get_post_meta($product_id, '_tmpa_model_size', true);

            // If selected array contains model value from URL, override the default value from the database
            if (!empty($selected_model)) {

                // Check that url model value exists in the model sizes array for the current product,
                $labels = array_column($model_sizes, 'label');

                if (in_array($selected_model, $labels, true)) {

                    // Update selected model to reflect value from url param
                    // Loop through model sizes and set is_default to true for the matching label, false for others
                    foreach ($model_sizes as &$model_size) {
                        $model_size['is_default'] = ($model_size['label'] === $selected_model);
                    }

                // break reference
                unset($model_size); 

                }
                
            }

            return $model_sizes;

        }

        /**
         * Check url for params or if none determine default values from postmeta
         *
         * @param int|null $product_id The ID of the product (optional)
         * @return array Returns array of selected options to be used for image layer rendering and current status display
         */

        public static function productInitialState($product_id = null) {

            // Resolve product ID once
            $product_id = $product_id ?: get_the_ID();

            // Safely resolve request URI
            $request_uri = $request_uri ?? ($_SERVER['REQUEST_URI'] ?? '');

            // Extract query string
            $query = $request_uri ? parse_url($request_uri, PHP_URL_QUERY) : null;

            if ($query) {
                parse_str($query, $params);

                // Only trigger URL logic if relevant params exist
                if (
                    !empty($params['colour']) ||
                    !empty($params['base']) ||
                    !empty($params['veneer']) ||
                    !empty($params['model'])
                ) {
                    return self::setProductDataFromURL($params, $product_id);
                }
            }

            // If no relevant URL params, return default product data based on database values 
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

            // Loop over params and normalise values (e.g. sanitize, convert to lowercase) for comparison with allowed options
            $normalised_values = fn($key) => self::normalise(str_replace('_', ' ', $params[$key]));

            // Extract normalised values to variables
            $colour = $normalised_values('colour');
            $base   = $normalised_values('base');
            $metal  = $normalised_values('veneer');
            $model  = $normalised_values('model');

            // Validate top
            $allowedColoursForTop = self::findByTopName($colour);

            // If no allowed options found for the selected top colour, fallback to defaults to avoid invalid state
            if (!$allowedColoursForTop) {
                return self::setDefaultProductData($product_id);
            }

            // Extract allowed options for the selected top colour
            $allowedBase  = $allowedColoursForTop['base']  ?? [];
            $allowedMetal = $allowedColoursForTop['metal'] ?? [];

            // Validate base and metal options against the allowed options for the selected top colour, 
            // with fallback to defaults if invalid to avoid invalid state
            $base  = self::validateOrFallback($base, $allowedBase);
            $metal = self::validateOrFallback($metal, $allowedMetal);

            // Find selected options in the product data to get the correct 
            //image urls for the current selection based on the URL params,
            $final_values = self::setFinalValues($colour, $base, $metal);

            // Return default selections
            $result = [
                'top'   => $final_values['top'],
                'base'  => $final_values['base'],
                'model' => $model,
            ];

            // Only set metal if it exists
            if (!empty($final_values['metal'])) {
                $result['metal'] = $final_values['metal'];
            }

            return $result;
        }

        /**
         * Set product data based on default values from the database, used when no URL params provided or invalid params
         * @param int|null $product_id The ID of the product (optional)
         * @return array Returns array of selected options to be used for image layer rendering and current status display
         * 
         */
        public static function setDefaultProductData($product_id = null) {

            // If no colour param, fallback to defaults (if set) or empty
            $post_id = $product_id ?? get_the_ID();

            $fields = [
                'top'   => '_tmpa_top_colour',
                'base'  => '_tmpa_base_colour',
                'metal' => '_tmpa_metal_colour',
                'model' => '_tmpa_model_size',
            ];

            // Loop through fields and get values from post meta, set to image layers
            $image_layers = [];

            foreach ($fields as $key => $meta_key) {
                $image_layers[$key] = get_post_meta($post_id, $meta_key, true);
            }

            // Determine current default model based on the is_default flag in the model sizes array
            if (is_array($image_layers['model'])) {
                foreach ($image_layers['model'] as $model) {
                    if (!empty($model['is_default'])) {
                        $image_layers['model'] = $model['label'];
                        break;
                    }
                }
            }

            // Find the correct image URLs for the default selections based on the product data,
            $final_values = self::setFinalValues($image_layers['top'], $image_layers['base'], $image_layers['metal']);

            // Return default selections
            $result = [
                'top'   => $final_values['top'],
                'base'  => $final_values['base'],
                'model' => $image_layers['model'],
            ];

            // Only set metal if it exists
            if (!empty($final_values['metal'])) {
                $result['metal'] = $final_values['metal'];
            }

            return $result;

        }

        /**
         * Extract the selected options from the product data based on the current URL params or defaults,
         *
         * @param string|null $colour
         * @param string|null $base
         * @param string|null $metal
         * @return array Returns array of selected options with their names and URLs
         */
        public static function setFinalValues($colour, $base, $metal) {

            // Array to hold final values
            $final_values = [];

            // Loop over top colour options to find the matching colour and get the corresponding image URL, 
            // set to final values array for current selection
            foreach(self::$product_data['colour_options'] as $option) {

                if ($option['top']['name'] === $colour) {
                    $final_values['top'] = [
                        'name' => $option['top']['name'],
                        'url'  => $option['top']['url'],
                    ];

                    // Break loop once the matching top colour is found
                    break; 
                }
            }

            // Loop over base colour options to find the matching colour and get the corresponding image URL,
            // set to final values array for current selection
            foreach(self::$product_data['master_values']['base'] as $colourName => $option) {

                if ($colourName === $base) {
                    $final_values['base'] = [
                        'name' => $option['name'],
                        'url'  => $option['url'],
                    ];

                    // Break loop once the matching base colour is found
                    break; 
                }

            }
            
            //Check if metal option exists for this product type
            if(array_key_exists('metal', self::$product_data['master_values'])) {

                // Loop over metal colour options to find the matching colour and get the corresponding image URL,
                // set to final values array for current selection
                foreach(self::$product_data['master_values']['metal'] as $colourName => $option) {
    
                    if ($colourName === $metal) {
                        $final_values['metal'] = [
                            'name' => $option['name'],
                            'url'  => $option['url'],
                        ];
    
                        // Break loop once the matching metal colour is found
                        break; 
                    }
    
                }

            }

            // return final values
            return $final_values;
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
         * Filter bases depending on whether product is in wood category (id 199)
         *
         * @param array $data
         * @param object $product
         * @return array Returns the filtered product data with either wood or tile bases and corresponding colour options based on the product category
         */
        private static function filterDataByWoodCategory($data, $product) {

            // Define wood base options
            $woodBaseOptions = [
                'american walnut',
                'jet black'
            ];

            // Determine if product is in wood category (id 199)
            $isWoodProduct = has_term(199, 'product_cat', $product->get_id());

            // If product is in wood category, filter out tile base options, otherwise filter out wood base options
            if ($isWoodProduct) {

                // Filter master values to include only wood bases
                $data['master_values']['base'] = array_filter(
                    $data['master_values']['base'],
                    fn($option) => in_array(strtolower($option['name']), $woodBaseOptions, true)
                );

                // Filter colour options to include only options that have the allowed wood bases for the selected top colour
                foreach ($data['colour_options'] as &$option) {
                    $option['base'] = array_filter(
                        $option['base'],
                        fn($base) => in_array(strtolower($base), $woodBaseOptions, true)
                    );
                }

            } else {

                // Filter master values to exclude only wood bases
                $data['master_values']['base'] = array_filter(
                    $data['master_values']['base'],
                    fn($option) => !in_array(strtolower($option['name']), $woodBaseOptions, true)
                );

                // Filter colour options to include only options that do not have the wood bases for the selected top colour
                foreach ($data['colour_options'] as &$option) {
                    $option['base'] = array_filter(
                        $option['base'],
                        fn($base) => !in_array(strtolower($base), $woodBaseOptions, true)
                    );
                }
            }
            
            return $data;
        }

    }