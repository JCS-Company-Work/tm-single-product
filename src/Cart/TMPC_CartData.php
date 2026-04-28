<?php

namespace TMProductConfigurator\Cart;

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
     *
     * @param array $cart_item_data
     * @param int $product_id
     * @param int $variation_id
     * @return array Modified cart item data with custom values added.
     */
    public static function tm_add_custom_product_values_to_cart($cart_item_data, $product_id, $variation_id) {

        // // Handle custom image URL and Base64 thumbnail
        // if (!empty($_POST['img_url'])) {

        //     // Sanitize the image URL
        //     $image_url = esc_url_raw($_POST['img_url']);

        //     // Store the original image URL in cart item data for later use
        //     if(!empty($image_url)) {
        //         $cart_item_data['custom_image_url'] = $image_url;
        //     }
            
        // }

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
     * Display custom product values in the cart and checkout item data
     *
     * @param array $item_data
     * @param array $cart_item
     * @return array
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
     * Update the product name link in the cart to point to a custom URL if set
     *
     * @param string $product_name The original product name HTML.
     * @param array $cart_item The cart item data.
     * @return string Modified product name HTML.
     */
    public static function tm_update_cart_urls($product_name, $cart_item) {

        // If a custom product URL is set, update the product name link in the cart to point to that URL
        if (!empty($cart_item['_tm_custom_product_url'])) {
        
            $link = esc_url($cart_item['_tm_custom_product_url']);
            $product_name = preg_replace('/href="[^"]+"/', 'href="' . $link . '"', $product_name);

        }

        return $product_name;

    }

    /**
     * Apply configured_total as product price in cart if it's set, 
     * ensuring it takes precedence over the default product price.
     *
     * @param WC_Cart $cart
     * @return void
     */
    public static function tm_apply_configured_total($cart) {

        // Avoid running in admin or during AJAX calls to prevent conflicts
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        // Loop through cart items and set the price to the configured total if it's set
        foreach ($cart->get_cart() as $cart_item) {
            
            // Check if the cart item has a configured total and apply it as the price
            if (isset($cart_item['configured_total'])) {
                
                // Ensure the configured total is treated as a float for pricing
                $price = floatval($cart_item['configured_total']);
                
                // Only set the price if it's greater than zero
                if ($price > 0) {
                    $cart_item['data']->set_price($price);
                }
            }
        }
    }

    /**
     * Save custom meta to order (store with programmatic keys, not labels).
     *
     * @param WC_Order_Item_Product $item
     * @param string $cart_item_key
     * @param array $values
     * @param WC_Order $order
     * @return void
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
    }
}
