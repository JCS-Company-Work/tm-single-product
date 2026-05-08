<?php

    namespace TMProductConfigurator\Email;

    class TMPC_Email

    {
        public static function init()

        {
            // Hook into WooCommerce email order item thumbnail to set custom image in emails
            add_filter('woocommerce_order_item_thumbnail', [__CLASS__, 'set_email_order_item_image'], 99, 2);
            
            // Capitalize custom meta values in WooCommerce emails
            add_filter('woocommerce_order_item_get_formatted_meta_data', [__CLASS__, 'capitalize_custom_meta_email'], 10, 2);

            // Add custom CSS to WooCommerce emails to ensure proper styling of custom images
            add_filter('woocommerce_email_styles', [__CLASS__, 'add_custom_email_css']);
        }

        /**
         * Set custom image for WooCommerce email order item thumbnails
         *
         * @param string $thumbnail The original thumbnail HTML.
         * @param \WC_Order_Item_Product $item The order item object.
         * @return string Modified thumbnail HTML.
         */
        public static function set_email_order_item_image($thumbnail, $item) {

            // Try to get custom image from order item meta
            $custom_image = $item->get_meta('custom_image');
            
            // Try to get custom product URL from order item meta
            $custom_url = $item->get_meta('_tm_custom_product_url');
            
            if (!empty($custom_image)) {
                $img_tag = '<img src="' . esc_attr($custom_image) . '" alt="Configured Product Image" style="max-width:250px;height:auto;">';
                if (!empty($custom_url)) {
                    return '<a href="' . esc_url($custom_url) . '">' . $img_tag . '</a>';
                }
                return $img_tag;
            }
            return $thumbnail;
        }

        /**
         * Capitalize and format custom meta values in WooCommerce emails
         *
         * @param array $formatted_meta
         * @param \WC_Order_Item $item
         * @return array
         */
        public static function capitalize_custom_meta_email($formatted_meta, $item) {

            foreach ($formatted_meta as $key => $meta) {

                // Skip non-string values
                if (!is_string($meta->value)) {
                    continue;
                }

                $value = trim($meta->value);

                // Skip empty values
                if ($value === '') {
                    continue;
                }

                // Skip prices/numeric values
                if (preg_match('/^£|\$|€|\d+(\.\d{2})?$/', $value)) {
                    continue;
                }

                // Skip URLs
                if (filter_var($value, FILTER_VALIDATE_URL)) {
                    continue;
                }

                // Skip hex colours
                if (preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $value)) {
                    continue;
                }

                // Convert:
                // royal-botticino -> Royal Botticino
                // ROYAL_BOTTICINO -> Royal Botticino
                $formatted = str_replace(
                    ['-', '_'],
                    ' ',
                    strtolower($value)
                );

                $formatted = ucwords($formatted);

                // Update BOTH raw and display values
                $meta->value         = $formatted;
                $meta->display_value = $formatted;

            }

            return $formatted_meta;
        }

        /**
         * Add custom CSS to WooCommerce emails to ensure proper styling of custom images
         *
         * @param string $css
         * @return string
         */
        public static function add_custom_email_css($css) {

            $css .= '

                table.td td:first-child {
                    vertical-align: middle !important;
                }

            ';

            return $css;

        }
    }