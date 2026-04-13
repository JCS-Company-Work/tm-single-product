<?php

namespace TMProductConfigurator\Cart;

use TMProductConfigurator\Cart\TMPC_CartImage;

class TMPC_CartData

{
    public static function init()

    {
        // Hook into WC cart data
        add_filter('woocommerce_add_cart_item_data', [__CLASS__, 'tm_add_custom_product_values_to_cart'], 10, 3);
        add_filter('woocommerce_get_item_data', [__CLASS__, 'tm_display_custom_product_values_cart'], 10, 2);
        add_filter('woocommerce_cart_item_name', [__CLASS__, 'tm_update_cart_urls'], 10, 2);
        
        // Set prices
        add_action('woocommerce_before_calculate_totals', [__CLASS__, 'tm_apply_configured_total']);
        
        // Save custom meta to order items
        add_action('woocommerce_checkout_create_order_line_item', [__CLASS__, 'tm_save_meta_to_order'], 10, 4);

    }
   
    /**
     * Add custom data to cart item (Base64 image, URL, WAPF fields, configured_total)
     */
    public static function tm_add_custom_product_values_to_cart($cart_item_data, $product_id, $variation_id) {

        // Handle custom image URL and Base64 thumbnail
        if (!empty($_POST['img_url'])) {

            // Sanitize the image URL
            $image_url = esc_url_raw($_POST['img_url']);
            
            // Generate Base64 thumbnail using the TMPC_CartImage class
            $base64 = TMPC_CartImage::tm_get_custom_base64_thumbnail($image_url, 150);
            
            if (!empty($base64)) {
                $cart_item_data['custom_image']     = $base64;
                $cart_item_data['custom_image_url'] = $image_url;
            }
        }

        foreach ([
            'top_colour',
            'base',
            'model',
            'metal_edge_veneer',
            'metal_edge_checkbox',
            '_tm_custom_product_url',
            'configured_total',
            'options_total'
        ] as $key) {
            if (!empty($_POST[$key])) {
                $cart_item_data[str_replace('-', '_', $key)] = ($key === '_tm_custom_product_url')
                    ? esc_url_raw($_POST[$key])
                    : sanitize_text_field($_POST[$key]);
            }
        }

        return $cart_item_data;
    }

    /**
     * Display custom meta in cart & checkout, including totals
     */
    public static function tm_display_custom_product_values_cart($item_data, $cart_item) {

        // Define the fields to display with their labels
        $fields = [
            'top_colour'          => 'Top Colour',
            'base'                => 'Base',
            'model'               => 'Model',
            'metal_edge_veneer'   => 'Metal Edge Veneer',
            'metal_edge_checkbox' => 'Metal Edge Sample',
            'note'                => 'Note',
            'options_total'       => 'Options Total',
        ];

        // Loop through the defined fields and add them to the item data if they exist in the cart item
        foreach ($fields as $key => $label) {
            if (!empty($cart_item[$key])) {
                $value = in_array($key, ['options_total']) ? wc_price($cart_item[$key]) : esc_html($cart_item[$key]);
                $item_data[] = ['name' => $label, 'value' => $value];
            }
        }

        // Return the modified item data to be displayed in the cart and checkout
        return $item_data;
    }

    /**
     * Replace product name link in cart
     */
    public static function tm_update_cart_urls($product_name, $cart_item) {
        if (!empty($cart_item['_tm_custom_product_url'])) {
            $link = esc_url($cart_item['_tm_custom_product_url']);
            $product_name = preg_replace('/href="[^"]+"/', 'href="' . $link . '"', $product_name);
        }
        return $product_name;
    }

    /**
     * Apply configured_total as product price
     */
    public static function tm_apply_configured_total($cart) {

        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        foreach ($cart->get_cart() as $cart_item) {
            if (isset($cart_item['configured_total'])) {
                $price = floatval($cart_item['configured_total']);
                if ($price > 0) {
                    $cart_item['data']->set_price($price);
                }
            }
        }
    }

    /**
     * Save custom meta to order (store with programmatic keys, not labels).
     */
    public static function tm_save_meta_to_order($item, $cart_item_key, $values, $order) {
        $meta_fields = [
            'top_colour'          		=> 'Top Colour',
            'base'                		=> 'Base',
            'model'               		=> 'Model',
            'metal_edge_veneer'   		=> 'Metal Edge Veneer',
            'metal_edge_checkbox' 		=> 'Metal Edge Sample',
            'note'                		=> 'Note',
            '_tm_custom_product_url'  	=> 'Product Url',
        ];

        foreach ($meta_fields as $key => $label) {
            if (!empty($values[$key])) {
                // Save using key
                $item->add_meta_data($key, $values[$key], true);
            }
        }
        
        // Save custom url with params to meta data
        if (!empty($values['_tm_custom_product_url'])) {
            $item->add_meta_data('_tm_custom_product_url', esc_url_raw($values['_tm_custom_product_url']), true);
        }


        // Save custom image as hidden technical key
        if (!empty($values['custom_image_url'])) {

            // Build the /wapf/ image URL from the original image URL or filename
            $upload_dir = wp_upload_dir();
            $filename = basename($values['custom_image_url']);
            $image_url = $upload_dir['baseurl'] . '/wapf/' . $filename;
            $item->add_meta_data('_tm_custom_image', esc_url_raw($image_url), true);

        }
    }
}
