<?php

    namespace TMProductConfigurator\Gallery;

    use TMProductConfigurator\Images\TMPC_Images;

    class TMPC_Gallery {

        /**
         * Initialize the gallery class.
         *
         * Registers the main WooCommerce hook to output the gallery
         * after the single product summary on eligible product pages.
         *
         * @return void
         */
        public static function init() {
            
            // Initialise gallery
            add_action('woocommerce_before_single_product_summary', [__CLASS__, 'top_gallery'], 5);
            add_action('woocommerce_after_single_product_summary', [__CLASS__, 'bottom_gallery'], 20);

        }

        /**
         * Only load plugin on product pages not in swatch category
         * @param \WC_Product $product The current product object
         * @return bool True if we should load the gallery, false otherwise
         */
        public static function should_load($product) {

            // Only load on single product pages and if product is valid
            if (!is_product() || !$product) return false;

            // Don't load on swatch category products
            return !has_term('swatch', 'product_cat', $product->get_id());

        }

        /**
         * Initialise top gallery with first four images for product only
         *
         * @return void
         */
        public static function top_gallery() {

            // Get the current product
            $product = wc_get_product(get_the_ID());

            // Check if we should load the gallery (only on non-swatch product pages)
            if (!self::should_load($product)) return;
                
            // Get gallery attachment IDs from product backend
            $attachment_ids = $product->get_gallery_image_ids();

            // Extract the first four images for the top gallery
            $attachment_ids = array_slice($attachment_ids, 0, 4);

            // Get product title for captions
            $caption = get_the_title();

            // Output gallery container and render gallery items
            self::render_top_gallery($attachment_ids, $caption);
        }

        /**
         * Initialise bottom gallery with remaining images for product and composite image as first item
         * 
         * @return void
         */
        public static function bottom_gallery() {

            // Get the current product
            $product = wc_get_product(get_the_ID());

            // Check if we should load the gallery (only on non-swatch product pages)
            if (!self::should_load($product)) return;

            // Get gallery attachment IDs from product backend
            $attachment_ids = $product->get_gallery_image_ids();

            // Remove the first four images for the bottom gallery
            $attachment_ids = array_slice($attachment_ids, 4);

            // Get product title for captions
            $caption = get_the_title();

            // Output gallery container and render gallery items
            echo '<ul class="tm-gallery">';

                // Render non-WAPF images
                self::render_bottom_gallery($attachment_ids, $caption);

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
        private static function render_top_gallery($attachment_ids, $caption) {

            if (empty($attachment_ids)) return;

            echo '<ul class="tm-gallery-grid">';

            // Main image (first)
            $main_id = $attachment_ids[0];
            $img_full = wp_get_attachment_image_src($main_id, 'full');
            $meta     = wp_get_attachment_metadata($main_id);
            $aspectClass = ($meta['width'] > $meta['height']) ? 'landscape' : 'portrait';

            echo '<li class="tm-gallery-main ' . $aspectClass . '">';
            echo '<a href="' . esc_url($img_full[0]) . '" 
                data-pswp-src="' . esc_url($img_full[0]) . '" 
                data-pswp-width="' . esc_attr($img_full[1]) . '" 
                data-pswp-height="' . esc_attr($img_full[2]) . '" 
                data-pswp-gallery="woocommerce-gallery">';
            echo wp_get_attachment_image(
                $main_id,
                'category-thumb',
                false,
                [
                    'alt'           => $caption . ' main gallery image',
                    'sizes'         => '(max-width: 480px) 100vw, (max-width: 768px) 200px, 300px',
                    'loading'       => 'eager',
                    'decoding'      => 'async',
                    'fetchpriority' => 'high',
                ]
            );
            echo '</a></li>';

            // Thumbnails (next three)
            for ($i = 1; $i < min(4, count($attachment_ids)); $i++) {
                $id = $attachment_ids[$i];
                $img_full = wp_get_attachment_image_src($id, 'full');
                $meta     = wp_get_attachment_metadata($id);
                $aspectClass = ($meta['width'] > $meta['height']) ? 'landscape' : 'portrait';

                echo '<li class="tm-gallery-thumb ' . $aspectClass . '">';
                echo '<a href="' . esc_url($img_full[0]) . '" 
                    data-pswp-src="' . esc_url($img_full[0]) . '" 
                    data-pswp-width="' . esc_attr($img_full[1]) . '" 
                    data-pswp-height="' . esc_attr($img_full[2]) . '" 
                    data-pswp-gallery="woocommerce-gallery">';
                echo wp_get_attachment_image(
                    $id,
                    'gallery-thumb-md',
                    false,
                    [
                        'alt'           => $caption . ' gallery thumbnail ' . $i,
                        'sizes'         => '(max-width: 480px) 100vw, (max-width: 768px) 200px, 300px',
                        'loading'       => 'lazy',
                        'decoding'      => 'async',
                        'fetchpriority' => 'low',
                    ]
                );
                echo '</a></li>';
            }
            echo '</ul>';
        }

        /**
         * Render bottom gallery images
         *
         * @param array $attachment_ids
         * @param string $caption
         * @return void
         */
        private static function render_bottom_gallery($attachment_ids, $caption) {

            // Render composite image as first item if it exists
            self::renderCompositeImage();
        
            $i = 1;

            // Loop through each attachment ID and output the corresponding gallery item
            foreach ($attachment_ids as $id) {

                // Get full image URL and dimensions for PhotoSwipe data attributes
                $img_full = wp_get_attachment_image_src($id, 'full');
                $meta     = wp_get_attachment_metadata($id);

                // Determine aspect ratio class for styling
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

        /**
         * Render the composite image as the first item in the gallery.
         *
         * @return void
         */
        public static function renderCompositeImage() {

            // Get composite image URLs
            $images = TMPC_Images::getCompositeImages();

            // Only render if we have a composite image
            if ($images) {

                // Output the composite image as the first gallery item with data attributes for PhotoSwipe
                echo '<li class="grid-item grid-item-0 landscape composite-image">';

                echo '<a href="' . esc_url($images['1600']) . '" 
                    data-pswp-src="' . esc_url($images['1600']) . '" 
                    data-pswp-width="1600" 
                    data-pswp-height="650" 
                    data-pswp-gallery="woocommerce-gallery">';

                echo '<img src="' . esc_url($images['1600']) . '" alt="Configured Product">';

                echo '</a></li>';
                
            }

        }
    }