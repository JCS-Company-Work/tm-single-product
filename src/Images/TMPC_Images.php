<?php

    namespace TMProductConfigurator\Images;

    use TMProductConfigurator\ColourOptions\TMPC_ColourOptionsService;

    class TMPC_Images {

        public static function init() {

            add_action('rest_api_init', function () {
                register_rest_route('tmpc/v1', '/update-product-images', [
                    'methods' => 'POST',
                    'callback' => [self::class, 'buildImageUrl'],
                    'permission_callback' => '__return_true',
                ]);
            });

        }

        public static function serveImagesOnPageLoad() {

            // Only run on product pages
            if (!is_product()) return;

            // Make product variable available globally
            global $product;

            // Check if Imagick is available
            if (!class_exists('Imagick')) {
                error_log('[TMPC] Imagick not available');
                return;
            }
            
            // Get product from WooCommerce
            $product = wc_get_product(get_the_ID());
            
            // If product not found, return early
            if (!$product) return;

            // Get valid colour combinations for current product and options
            $valid_colours = self::getValidColourCombinations($product);

            // Process layers to get image paths
            $image_layers = self::processLayers($product->get_sku(), $valid_colours);

            // Build composite image and get URL path
            $path = self::buildCompositeImage($image_layers);
            
            $final_image = '<img src="' . esc_url(site_url($path)) . '" alt="Product Image" />';

            // Output the image HTML
            return $final_image;
        }

        public static function getValidColourCombinations($product) {

            // Get colour options data
            $colourOptions = TMPC_ColourOptionsService::getColourOptionsRaw();
            
            // Parse URL query to get selected options (if any)
            $query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
            $params = [];
            $image_layers = [];

            // If colour param exists in URL 
            if (!empty($query)) {

                // Parse query string into params array
                parse_str($query, $params);

                // Extract and format colour param for lookup
                $colour = isset($params['colour']) ? str_replace(' ', '_', strtolower(sanitize_text_field(wp_unslash($params['colour'])))) : null;

                // Get product type for current product
                $productType = self::get_product_type($product);

                // Get available colours for this product type and selected colour
                $availableColours = $colourOptions[$colour][$productType] ?? [];

                // Validate and assign base and metal from URL, fallback to first available if not valid
                $base = isset($params['base']) ? strtolower(sanitize_text_field(wp_unslash($params['base']))) : null;
                $metal = isset($params['metal']) ? strtolower(sanitize_text_field(wp_unslash($params['metal']))) : null;

                if (isset($availableColours['base']) && is_array($availableColours['base'])) {
                    if (!in_array($base, $availableColours['base'], true)) {
                        $base = $availableColours['base'][0] ?? null;
                    }
                }
                if (isset($availableColours['metal']) && is_array($availableColours['metal'])) {
                    if (!in_array($metal, $availableColours['metal'], true)) {
                        $metal = $availableColours['metal'][0] ?? null;
                        var_dump('metal fallback', $metal);
                    }
                }

                // Build image layers array based on selected options
                // Hyphenate colour names for image file naming convention
                $image_layers['top'] = str_replace('_', '-', $colour);
                $image_layers['base'] = $base;
                $image_layers['metal'] = $metal;

            }else {
                // If no colour param, fallback to defaults (if set) or empty
                $image_layers['top'] = get_post_meta(get_the_ID(), '_tmpc_top_colour', true);
                $image_layers['base'] = get_post_meta(get_the_ID(), '_tmpc_base_colour', true);
                $image_layers['metal'] = get_post_meta(get_the_ID(), '_tmpc_metal_colour', true);
            }

            return $image_layers;

        }

        /**
         * Undocumented function
         *
         * @param [type] $request
         * @return void
         */
        public static function buildImageUrl($request) {

            if (!class_exists('Imagick')) {
                return ['success' => false, 'message' => 'Imagick not available'];
            }

            $start = microtime(true);
            $timings = [];
            $params = $request->get_json_params();

            $product = wc_get_product(intval($params['product_id'] ?? 0));
            if (!$product) {
                return ['success' => false, 'message' => 'Invalid product'];
            }

            $image_layers = self::processLayers($product->get_sku(), $params['selectedLayers']);

            // Build composite image and get URL path
            $img700 = self::buildCompositeImage($image_layers, $timings);

            $hash = md5(json_encode($image_layers));
            $dir = site_url('wp-content/themes/tm-shop-child/assets/layers/composites');

            $images = [
                '700' => "$dir/{$hash}-700.png",
                '1600' => "$dir/{$hash}-1600.png",
                '400' => "$dir/{$hash}-400.png",
            ];

            return [
                'success' => true,
                'duration' => microtime(true) - $start,
                'images' => $images,
                'timings' => $timings
            ];
        }

        public static function processLayers($product_sku, $selectedLayers) {

            $base_folder = ABSPATH . 'wp-content/themes/tm-shop-child/assets/layers';

            preg_match('/^(.*?)-bp(.*)$/', $product_sku, $matches);

            $top_layer = rtrim($matches[1] ?? '', '-');
            $base_layer = 'bp' . ($matches[2] ?? '');

            $shadow_layer = strpos($top_layer, '-') !== false
                ? explode('-', $top_layer)[0]
                : $top_layer;

            $metal_layer = strpos($top_layer, 'metal') !== false ? $top_layer : null;

            $format = fn($v) => str_replace(' ', '-', strtolower($v));

            return array_filter([
                'shadow' => "$base_folder/{$shadow_layer}-shadow.png",
                'base'   => "$base_folder/{$base_layer}-" . $format($selectedLayers['base']) . '.png',
                'metal'  => $metal_layer ? "$base_folder/{$metal_layer}-" . $format($selectedLayers['metal']) . '.png' : null,
                'top'    => "$base_folder/{$top_layer}-" . $format($selectedLayers['top']) . '.png',
            ]);
        }

        public static function buildCompositeImage(array $paths) {

            $hash = md5(json_encode($paths));
            $dir = ABSPATH . 'wp-content/themes/tm-shop-child/assets/layers/composites';

            $file700  = "$dir/{$hash}-700.png";
            $file1600 = "$dir/{$hash}-1600.png";
            $file400  = "$dir/{$hash}-400.png";

            // Accept timings array by reference for profiling
            $timings = func_num_args() > 1 ? func_get_arg(1) : null;

            // Return cached
            if (file_exists($file700)) {
                if (!file_exists($file1600)) {
                    self::generateOtherSizes($paths, $file1600, $file400);
                }
                if (is_array($timings)) {
                    $timings['cached'] = true;
                }
                return str_replace(ABSPATH, '', $file700);
            }

            try {
                $step_start = microtime(true);
                $base = null;

                foreach ($paths as $path) {
                    if (!file_exists($path)) continue;

                    $img = new \Imagick($path);
                    $img->setImageBackgroundColor(new \ImagickPixel('transparent'));

                    if (!$base) {
                        $base = $img;
                        continue;
                    }

                    $base->compositeImage($img, \Imagick::COMPOSITE_OVER, 0, 0);
                    $img->clear();
                    $img->destroy();
                }
                if (is_array($timings)) {
                    $timings['composite'] = microtime(true) - $step_start;
                }

                if (!$base) return false;

                // 700px
                $step_700 = microtime(true);
                $img700 = clone $base;
                $img700->thumbnailImage(700, 0);
                $img700->writeImage($file700);
                if (is_array($timings)) {
                    $timings['700'] = microtime(true) - $step_700;
                }

                $img700->clear();
                $img700->destroy();

                // Other sizes
                $step_other = microtime(true);
                self::generateOtherSizesFromBase($base, $file1600, $file400);
                if (is_array($timings)) {
                    $timings['other_sizes'] = microtime(true) - $step_other;
                }

                $base->clear();
                $base->destroy();

                return str_replace(ABSPATH, '', $file700);

            } catch (\Exception $e) {
                error_log('[TMPC] Imagick error: ' . $e->getMessage());
                return false;
            }
        }

        private static function generateOtherSizesFromBase($base, $file1600, $file400) {

            if (file_exists($file1600)) return;

            $img1600 = clone $base;
            $img1600->thumbnailImage(1600, 0);
            $img1600->writeImage($file1600);

            $img400 = clone $img1600;
            $img400->thumbnailImage(400, 0);
            $img400->writeImage($file400);

            $img1600->clear(); $img1600->destroy();
            $img400->clear();  $img400->destroy();
        }

        private static function generateOtherSizes($paths, $file1600, $file400) {

            try {
                $base = null;

                foreach ($paths as $path) {
                    if (!file_exists($path)) continue;

                    $img = new \Imagick($path);

                    if (!$base) {
                        $base = $img;
                        continue;
                    }

                    $base->compositeImage($img, \Imagick::COMPOSITE_OVER, 0, 0);
                    $img->clear();
                    $img->destroy();
                }

                if (!$base) return;

                self::generateOtherSizesFromBase($base, $file1600, $file400);

                $base->clear();
                $base->destroy();

            } catch (\Exception $e) {
                error_log('[TMPC] Imagick resize error: ' . $e->getMessage());
            }
        }

        /**
         * Determine product type from WP categories
         *
         * @param object $product
         * @return string|null Returns 'solid', 'slim', 'edge' or null if no match
         */
        public static function get_product_type($product) {

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