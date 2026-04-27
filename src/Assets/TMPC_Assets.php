<?php

    namespace TMProductConfigurator\Assets;

    class TMPC_Assets {

        public static function init() {

            // Enqueue admin assets
            add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_frontend_assets'], 10);

            // Defer non-critical scripts
            add_filter('script_loader_tag', [__CLASS__, 'deferScripts'], 10, 2);

            // Defer non-critical CSS
            add_filter('style_loader_tag', [__CLASS__, 'styleLoader'], 10, 4);

            // Convert certain scripts to modules
            add_filter('script_loader_tag', [__CLASS__, 'scriptLoader'], 10, 3);

        }


        /**
         * Enqueue frontend assets
         */
        public static function enqueue_frontend_assets() {

            global $post;

            // Add product AJAX to all product pages
            if (function_exists('is_product') && is_product() && $post instanceof \WP_Post) {

                wp_enqueue_script('product-ajax-cart', TMPC_URL . 'assets/js/ajax/ajax-add-product-to-cart.js', [], TMPC_VERSION, true);

            }

            // Product pages that are NOT swatches
            if (function_exists('is_product') && is_product() && $post instanceof \WP_Post && !has_term('swatch', 'product_cat', $post->ID)) {
                
                // Add sample AJAX to product pages that are NOT swatches
                wp_enqueue_script('sample-ajax-cart', TMPC_URL . 'assets/js/ajax/ajax-add-sample-to-cart.js', [], TMPC_VERSION, true);

                // Product renders scripts (bundled for browser compatibility due to module/import use)
    		    wp_enqueue_script('tm-product-renders', TMPC_URL . 'assets/js/renders/dist/ProductRenders.bundle.js', [], TMPC_VERSION, true);

                // Pass plugin URL for use in JS
                wp_localize_script('tm-product-renders', 'TMPCPlugin', ['url' => TMPC_URL]);

                // Add ColourOptions JS class on all non-swatch product pages
                wp_enqueue_script('colour-options', TMPC_URL . 'assets/js/product/ColourOptions.js', [], TMPC_VERSION, true);

                wp_localize_script('colour-options', 'TMPCPlugin', ['product_id' => $post->ID]);

                // QR code reseources
                wp_enqueue_script('qr_code', TMPC_URL . 'assets/js/qrcode/qrcode.min.js', [], TMPC_VERSION, ['strategy' => 'defer','in_footer' => true]); 

                // Enqueue class to power pdf download in current status
                wp_enqueue_script('pdf-download', TMPC_URL . 'assets/js/pdf/BuildPDF.js', [], TMPC_VERSION, true);

                // Add ModelSelection JS on all non-swatch product pages
                wp_enqueue_script('model-selection', TMPC_URL . 'assets/js/product/ModelSelection.js', [], TMPC_VERSION, true);

                // Enqueue CurrentStatus JS class
                wp_enqueue_script('current-status-class', TMPC_URL . 'assets/js/product/CurrentStatus.js', ['qr_code'], TMPC_VERSION, true);

                // Ensure Select2 is enqueued for dropdowns in product configurator
                if ( ! wp_script_is('select2', 'enqueued') ) {
                    wp_enqueue_script('select2');
                    wp_enqueue_style('select2');
                }
                
                // Enqueue Select2 for dropdowns in product configurator
                wp_enqueue_script('tm-swatch-select2', TMPC_URL . 'assets/select2/tm-swatch-select2.js', ['jquery', 'select2'], TMPC_VERSION, ['strategy' => 'defer','in_footer' => true]);
                wp_enqueue_style('tm-swatch-select2', TMPC_URL . 'assets/select2/tm-swatch-select2.css', [], TMPC_VERSION);
            
            }

        }

        /**
         * Defer JS not required above the fold
         *
         * @param string $tag
         * @param string $handle
         * @return string
         */
        public static function deferScripts($tag, $handle) {

            // List of scripts to defer
            $async_scripts = [
                'product-ajax-cart',
                'sample-ajax-cart',
                'current-status-class',
                'model-selection',
            ];

            // Defer selected scripts
            if (in_array($handle, $async_scripts, true)) {
                return str_replace('<script ', '<script defer ', $tag);
            }

            // Return unmodified tag for other scripts
            return $tag;
       
        }

        /**
         * Convert resources to modules to allow for modern JS features (import/export)  
         * and better performance where possible, while ensuring compatibility with 
         * older browsers by only doing so for scripts that can support it.
         *
         * @param string $tag
         * @param string $handle
         * @param string $src
         * @return string
         */
        public static function scriptLoader($tag, $handle, $src) {
            
            // JS handles safe to defer/async
            $defer_js = [
                'tm-product-renders',
            ];

            if (in_array($handle, $defer_js)) {
                // Add defer attribute
                $tag = str_replace(' src=', ' defer src=', $tag);
            }
            
            // load photoswipe as module
            if ($handle === 'photoswipe-init') {
                return '<script type="module" src="' . esc_url($src) . '"></script>';
            }
            return $tag;
        }

        /**
         * Safely defer non-critical CSS using media="print"
         *
         * @param string $html
         * @param string $handle
         * @param string $href
         * @param string $media
         * @return string
         */
        public static function styleLoader($html, $handle, $href, $media) {

            // List of non-critical CSS handles to defer
            $defer_css = [
                'select2',
                'tm-swatch-select2',
            ];

            if (in_array($handle, $defer_css, true)) {
                // Use media="print" trick to load asynchronously
                $html = str_replace(
                    "media='all'",
                    "media='print' onload=\"this.media='all'\"",
                    $html
                );
            }

            return $html;

        }

    }