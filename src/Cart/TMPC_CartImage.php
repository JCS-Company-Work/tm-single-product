<?php

namespace TMProductConfigurator\Cart;

class TMPC_CartImage
{
    public static function init()

    {

        // Hook into cart item thumbnail generation to set custom image for configured products
        add_filter('woocommerce_cart_item_thumbnail', [__CLASS__, 'set_cart_image'], 10, 3);
        
        // Hook into mini-cart thumbnail generation to ensure custom image is used there as well
        add_filter('woocommerce_mini_cart_item_thumbnail', [__CLASS__, 'set_cart_image'], 10, 3);

        // Hide custom_image meta from WooCommerce order item meta output
        add_filter('woocommerce_order_item_get_formatted_meta_data', [__CLASS__, 'hide_custom_image_meta'], 10, 2);

    }

    /**
     * Set custom cart image for configured products
     *
     * @param string $thumbnail The original thumbnail HTML.
     * @param array $cart_item The cart item data.
     * @return string Modified thumbnail HTML.
     */
    public static function set_cart_image($thumbnail, $cart_item) {

        // If the cart item has a custom image, use it instead of the default thumbnail
        if (!empty($cart_item['custom_image'])) {

            // Build image tag with custom basket image
            $img_tag = '<img src="' . esc_attr($cart_item['custom_image']) . '" alt="Configured Product Image" style="max-width:250px;height:auto;">';
            
            // If a custom product URL is set, wrap the image in a link
            if (!empty($cart_item['_tm_custom_product_url'])) {
                return '<a href="' . esc_url($cart_item['_tm_custom_product_url']) . '">' . $img_tag . '</a>';
            }
            
            // Otherwise, just return the image tag
            return $img_tag;

        }

        // If no custom image is set, return the original thumbnail
        return $thumbnail;

    }

    /**
     * Hide custom_image meta from WooCommerce order item meta output
     *
     * @param array $formatted_meta
     * @param WC_Order_Item $item
     * @return array
     */
    public static function hide_custom_image_meta($formatted_meta, $item) {
        foreach ($formatted_meta as $key => $meta) {
            if ($meta->key === 'custom_image') {
                unset($formatted_meta[$key]);
            }
        }
        return $formatted_meta;
    }
}