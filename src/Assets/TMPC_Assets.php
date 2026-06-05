<?php

    namespace TMProductConfigurator\Assets;

    class TMPC_Assets {

        public static function init() {

            // Enqueue admin assets
            add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_frontend_assets'], 10);

            // Defer non-critical scripts
            add_filter('script_loader_tag', [__CLASS__, 'deferScripts'], 10, 2);

            // Defer non-critical CSS
            // add_filter('style_loader_tag', [__CLASS__, 'styleLoader'], 10, 4);

            // Convert certain scripts to modules
            add_filter('script_loader_tag', [__CLASS__, 'scriptLoader'], 10, 3);

            // Remove default title, price and excerpt from single product summary
            add_action('wp', [__CLASS__, 'removeContent'], 1);

        }

        /**
         * Remove default summary content from single product page
         *
         * @return void
         */
        public static function removeContent() {

            // Only modify single product pages
            if (!is_product()) return;

            // Remove add to basket from default location
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);

            // Remove breadcrumbs from single product page
            remove_action('storefront_before_content', 'woocommerce_breadcrumb', 10);

            // Remove default title, price and excerpt from single product summary
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
            remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );

        }


        /**
         * Enqueue frontend assets
         */
        public static function enqueue_frontend_assets() {

            global $post;

            // Product pages that are NOT swatches
            if (function_exists('is_product') && is_product() && $post instanceof \WP_Post && !has_term('swatch', 'product_cat', $post->ID)) {
                
                wp_enqueue_style('single-styles', TMPC_URL . 'assets/css/single-product.css', [], TMPC_VERSION, 'all');
                
                // Add AJAX to product pages that are NOT swatches
                wp_enqueue_script('product-ajax-cart', TMPC_URL . 'assets/js/ajax/ajax-add-product-to-cart.js', [], TMPC_VERSION, true);
                wp_enqueue_script('sample-ajax-cart', TMPC_URL . 'assets/js/ajax/ajax-add-sample-to-cart.js', [], TMPC_VERSION, true);

                // Product renders scripts (bundled for browser compatibility due to module/import use)
    		    wp_enqueue_script('tm-product-renders', TMPC_URL . 'assets/js/renders/dist/ProductRenders.bundle.js', [], TMPC_VERSION, true);

                // Pass plugin URL for use in JS
                wp_localize_script('tm-product-renders', 'TMPCPlugin', ['url' => TMPC_URL, 'TMPC_VERSION' => TMPC_VERSION]);

                // Add Product JS class on all non-swatch product pages
                wp_enqueue_script('product', TMPC_URL . 'assets/js/product/Product.js', [], TMPC_VERSION, true);

                wp_localize_script('product', 'TMPCPlugin', ['product_id' => $post->ID]);

                // QR code reseources
                wp_enqueue_script('qr_code', TMPC_URL . 'assets/js/qrcode/qrcode.min.js', [], TMPC_VERSION, ['strategy' => 'defer','in_footer' => true]); 

                // Enqueue class to power pdf download in current status
                wp_enqueue_script('pdf-download', TMPC_URL . 'assets/js/pdf/BuildPDF.js', [], TMPC_VERSION, true);

                // Add ModelSelection JS on all non-swatch product pages
                wp_enqueue_script('model-selection', TMPC_URL . 'assets/js/product/ModelSelection.js', [], TMPC_VERSION, true);

                // Enqueue CurrentStatus JS class
                wp_enqueue_script('current-status-class', TMPC_URL . 'assets/js/product/CurrentStatus.js', ['qr_code'], TMPC_VERSION, true);
                
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