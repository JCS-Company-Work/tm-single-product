<?php 

    namespace TMProductConfigurator\Images;

    class TMPC_Images {

        /**
         * Scaffold API to control image creation
         *
         * @return void
         */
        public static function init() {
            
            // API endpoint to update product images options
            add_action('rest_api_init', function () {
                register_rest_route('tmpc/v1', '/update-product-images', [
                    'methods' => 'POST',
                    'callback' => [self::class, 'buildImageUrl'],
                    'permission_callback' => '__return_true',
                ]);
            });

            // Hook into page load to serve images based on URL params (if any)
            add_action('wp', [self::class, 'serveImagesOnPageLoad']);

        }

        public static function serveImagesOnPageLoad() {

            // Check if we are on single product page
            if (is_product()) {

                // Check URL to see if any params set or if we are serving the defaults
                $query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);

                // Array to hold params
                $params = [];

                // Array to hold final layer data
                $image_layers = [];

                // If query string exists, parse and sanitize parameters
                if (!empty($query)) {

                    // Parse query string into parameters
                    parse_str($query, $params);

                    // Sanitize parameters to prevent security issues
                    foreach($params as $key => $value) {

                        if($key === 'colour') {

                            $image_layers['top'] = sanitize_text_field(wp_unslash($value));

                        } else if($key === 'veneer') {

                            $image_layers['metal'] = sanitize_text_field(wp_unslash($value));

                        } else {

                            $image_layers[$key] = sanitize_text_field(wp_unslash($value));

                        }

                    }

                    var_dump($image_layers);
                } else {
                    // Get default values from database
                    $image_layers['top'] = get_post_meta(get_the_ID(), '_tmpc_top_colour', true);
                    $image_layers['base'] = get_post_meta(get_the_ID(), '_tmpc_base_colour', true);
                    $image_layers['metal'] = get_post_meta(get_the_ID(), '_tmpc_metal_colour', true);
                }

                // Get product ID
                $product_id = get_the_ID();

                 // Validate product ID
                 if (!$product_id) {
                    error_log('[TMPC] serveImagesOnPageLoad: No product ID found');
                    return;
                }

                // Get the product object
                $product = wc_get_product($product_id);

                // Validate product object
                if (!$product) {
                    error_log('[TMPC] Product not found for ID: ' . $product_id);
                    return [
                        'success' => false,
                        'message' => 'Invalid product'
                    ];
                }

                // Get product SKU            
                $product_sku = $product->get_sku();

                // Process the layers and selected options to determine which images to return
                $image_layers = self::processLayers($product_sku, $image_layers);

                // Build the composite image and get the URL
                return self::buildCompositeImage($image_layers);

            }

        }

        /**
         * Build image urls for use on single product page
          * @param WP_REST_Request $request
          * @return array
         */
        public static function buildImageUrl($request) {

            $params = $request->get_json_params();

            // Extract product ID from request parameters
            $product_id = isset($params['productID']) ? intval($params['productID']) : 0;

            // Validate product ID
            if (!$product_id) {
                error_log('[TMPC] No product_id passed');
                return [
                    'success' => false,
                    'message' => 'Missing product_id'
                ];
            }

            // Get the product object
            $product = wc_get_product($product_id);

            // Validate product object
            if (!$product) {
                error_log('[TMPC] Product not found for ID: ' . $product_id);
                return [
                    'success' => false,
                    'message' => 'Invalid product'
                ];
            }

            // Get product SKU            
            $product_sku = $product->get_sku();

            // Process the layers and selected options to determine which images to return
            $image_layers = self::processLayers($product_sku, $params['selectedLayers']);

            // Build the composite image and get the URL
            return self::buildCompositeImage($image_layers);

        }

        /**
         * Function to convert SKU and layer selections into correct image paths
         *
         * @param string $product_sku
         * @param array $selectedLayers
         * @return array
         */
        public static function processLayers($product_sku, $selectedLayers) {

            // Base folder for layer images
            $base_folder = ABSPATH . $_ENV['IMAGE_LAYER_PATH'];

            // Array to hold images
            $images = [];

            // Parse the SKU to extract the base model identifier
            preg_match('/^(.*?)-bp(.*)$/', $product_sku, $matches);

            // Extract top layer value and base layer value from SKU
            $top_layer = rtrim($matches[1] ?? '', '-');
            $base_layer = 'bp' . ($matches[2] ?? '');

            // Extract shadow layer value (first part before '-')
            if (strpos($top_layer, '-') !== false) {
                $shadow_layer = explode('-', $top_layer)[0];
            } else {
                $shadow_layer = $top_layer;
            }

            // Initiate metal layer
            $metal_layer = null;
            
            // If the base model contains 'metal', assign it to the metal layer as well as top layer
            if (strpos($top_layer, 'metal') !== false) {
                $metal_layer = $top_layer;
            }

            // Build file paths for each layer based on SKU and selected options and return in order (we build bottom to top)

            // Add shadow layer at the top
            $images['shadow_layer'] = $base_folder . '/' . $shadow_layer . '-shadow.png';

            // Add base layer
            $images['base_layer'] = $base_folder . '/' . $base_layer . '-' . str_replace(' ', '-', strtolower($selectedLayers['base'])) . '.png';
            
            // Only include metal layer if it exists for this product
            if ($metal_layer) {
                $images['metal_layer'] = $base_folder . '/' . $metal_layer . '-' . str_replace(' ', '-', strtolower($selectedLayers['metal'])) . '.png';
            }

            // Add top layer at the end to ensure it is on top in the image stacking order
            $images['top_layer'] = $base_folder . '/' . $top_layer . '-' . str_replace(' ', '-', strtolower($selectedLayers['top'])) . '.png';

            // Return constructed image paths
            return $images;

        }

        /**
         * Method to create single image PNG
         *
         * @param array $paths
         * @return string|false URL of the generated image or false on failure
         */
        public static function buildCompositeImage(array $paths) {

            // Generate a unique hash based on the image paths to use as the filename for caching purposes. 
            // This way, the same combination of layers will always produce the same filename, allowing us 
            // to reuse previously generated images and avoid unnecessary processing.
            $hash = md5(json_encode($paths));

            // Image path
            $image_path = rtrim($_ENV['IMAGE_LAYER_COMPOSITES_PATH'], '/') . '/' . $hash . '-1600.png';

            // Path to save final composite image
            $output_path = ABSPATH . $image_path;

            // If the image already exists, return the URL immediately to save processing time
             if (file_exists($output_path)) {
                error_log("[TMPC] buildCompositeImage: Returning cached image for hash $hash");
                return $image_path;
            }

            // If no paths provided, log and return false
            if (empty($paths)) {
                error_log('[TMPC] buildCompositeImage: No paths provided');
                return false;
            }

            // Create a base image resource to copy onto
            $base = null;
            
            // Variables to hold dimensions of the images
            $width = $height = 0;
            
            // Flag to track if we've set the base image yet
            $first = true;

            // Loop through each layer path, create image resource and copy onto base
            foreach ($paths as $layer => $path) {

                if (!file_exists($path)) {
                    error_log("[TMPC] buildCompositeImage: File does not exist: $path");
                    continue;
                }

                // Create image resource from PNG file
                $img = @imagecreatefrompng($path);
                if (!$img) {
                    error_log("[TMPC] buildCompositeImage: Failed to create image from: $path");
                    continue;
                }

                // Enable alpha blending and save alpha for proper transparency handling
                imagealphablending($img, true);
                imagesavealpha($img, true);

                // If this is the first valid image, set it as the base and get dimensions
                if ($first) {

                    // First valid image becomes the base
                    $base = $img;
                    $width  = imagesx($base);
                    $height = imagesy($base);
                    $first = false;
                    error_log("[TMPC] buildCompositeImage: Set base image from $path (width: $width, height: $height)");
                    continue;

                }

                imagecopy($base, $img, 0, 0, 0, 0, $width, $height);
                error_log("[TMPC] buildCompositeImage: Copied layer '$layer' onto base");
            }

            // If we failed to create a base image, log and return false
            if (!$base) {
                error_log('[TMPC] buildCompositeImage: No valid base image created');
                return false;
            }

            // Only save 1600px, 700px, 400px versions
            $sizes = [1600, 700, 400];

            // Variable to hold the URL of the main image (1600px)
            $main_url = '';
            
            foreach ($sizes as $size) {
                $resized_path = rtrim($_ENV['IMAGE_LAYER_COMPOSITES_PATH'], '/') . "/{$hash}-{$size}.png";
                $resized_output_path = ABSPATH . $resized_path;
                if ($size === 1600) {
                    // 1600px: use composite base
                    self::resizeImageFromResource($base, $resized_output_path, $size);
                    $main_url = $resized_path;
                } else {
                    // 700px, 400px: use 1600px as source
                    $src_1600 = ABSPATH . rtrim($_ENV['IMAGE_LAYER_COMPOSITES_PATH'], '/') . "/{$hash}-1600.png";
                    self::resizeImage($src_1600, $resized_output_path, $size);
                }
            }

            // Return 1600px URL
            return $main_url;
        }

        /**
         * Resize a GD image resource to a new width, keeping aspect ratio.
         * Used when the image is already in memory (e.g., after building the composite).
         * This avoids writing to disk and reloading for the first (largest) output.
         *
         * @param resource|GdImage $img The in-memory image resource to resize.
         * @param string $dest Destination file path for the resized image.
         * @param int $new_width Target width in pixels.
         *
         */
        private static function resizeImageFromResource($img, $dest, $new_width) {
            
            // Get original size
            $width = imagesx($img);
            $height = imagesy($img);

            // If already small enough, just save
            if ($width <= $new_width) {
                imagepng($img, $dest, 9);
                return true;
            }
            
            // Calc new height
            $new_height = intval($height * ($new_width / $width));
            
            // Create new image
            $resized = imagecreatetruecolor($new_width, $new_height);
            
            // Keep transparency
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            
            // Resize
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagepng($resized, $dest, 9);
            imagedestroy($resized);

            return true;
        }

        /**
         * Resize a PNG file to a new width, keeping aspect ratio.
         * Used when the image is only available as a file (e.g., after saving the 1600px version).
         * This is used for smaller sizes (700px, 400px) to avoid keeping large images in memory.
         *
         * Why both resize methods?
         * - resizeImageFromResource: For the first (largest) output, we already have the image in memory.
         * - resizeImage: For smaller outputs, we load from the just-saved file to save memory and simplify logic.
         * This pattern is efficient and avoids unnecessary disk I/O or memory usage.
         *
         * @param string $src Source PNG file path.
         * @param string $dest Destination file path for the resized image.
         * @param int $new_width Target width in pixels.
         */
        private static function resizeImage($src, $dest, $new_width) {
            
            // Load image
            $img = @imagecreatefrompng($src);
            
            // If loading fails, log and return false
            if (!$img) return false;
            
            // Get original size
            $width = imagesx($img);
            $height = imagesy($img);
            
            // If already small enough, just copy
            if ($width <= $new_width) {
                copy($src, $dest);
                imagedestroy($img);
                return true;
            }
            
            // Calc new height
            $new_height = intval($height * ($new_width / $width));
            
            // Create new image
            $resized = imagecreatetruecolor($new_width, $new_height);
            
            // Keep transparency
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            
            // Resize
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagepng($resized, $dest, 9);
            imagedestroy($img);
            imagedestroy($resized);
            
        }

    }