<?php

    namespace TMProductConfigurator\Gallery;

    class TMPC_Gallery {

        public static function init() {
            
            // Initialise gallery
            add_action('woocommerce_after_single_product_summary', [__CLASS__, 'init_gallery'], 5);

        }

        /**
         * Only load plugin on product pages not in swatch category
         */
        public static function should_load($product) {

            // Only load on single product pages and if product is valid
            if (!is_product() || !$product) return false;

            // Don't load on swatch category products
            return !has_term('swatch', 'product_cat', $product->get_id());

        }

        /**
         * Render gallery elements including WAPF imagery
         */
        public static function init_gallery() {

            // Get the current product
            $product = wc_get_product(get_the_ID());

            // Check if we should load the gallery (only on non-swatch product pages)
            if (!self::should_load($product)) return;

            // Get gallery attachment IDs from product backend
            $attachment_ids = $product->get_gallery_image_ids();

            // Get product title for captions
            $caption = get_the_title();

            // Output gallery container and render gallery items
            echo '<ul class="tm-gallery">';

                // Render non-WAPF images
                self::render_gallery($attachment_ids, $caption);

            echo '</ul>';
        }

        /**
         * Render a list of additional gallery attachments.
         *
         * Outputs <li> items containing linked thumbnail images that open
         * in the PhotoSwipe lightbox, including full-size image dimensions.
         *
         * @param array  $attachment_ids Array of attachment IDs.
         * @param string $caption        Caption text for alt attributes.
         * @return void
         */
        private static function render_gallery($attachment_ids, $caption) {
        
            $i = 1;

            foreach ($attachment_ids as $id) {

                $img_full = wp_get_attachment_image_src($id, 'full');
                $meta     = wp_get_attachment_metadata($id);

                $aspectClass = ($meta['width'] > $meta['height'])
                    ? 'landscape'
                    : 'portrait';

                echo '<li class="grid-item grid-item-' . $i . ' ' . $aspectClass . '">';

                echo '<a href="' . esc_url($img_full[0]) . '" 
                    data-pswp-src="' . esc_url($img_full[0]) . '" 
                    data-pswp-width="' . esc_attr($img_full[1]) . '" 
                    data-pswp-height="' . esc_attr($img_full[2]) . '" 
                    data-pswp-gallery="woocommerce-gallery">';

                echo wp_get_attachment_image(
                    $id,
                    'gallery-thumb-md', // Use largest thumb for srcset
                    false,
                    [
                        'alt'           => $caption . ' gallery image ' . $i,
                        'sizes'         => '(max-width: 480px) 100vw, (max-width: 768px) 200px, 300px',
                        'loading'       => 'lazy',
                        'decoding'      => 'async',
                        'fetchpriority' => 'low',
                    ]
                );

                echo '</a></li>';

                $i++;
            }
        }
    }