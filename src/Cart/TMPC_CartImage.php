<?php

namespace TMProductConfigurator\Cart;

class TMPC_CartImage
{
    public static function init()

    {

        add_filter('woocommerce_cart_item_thumbnail', [__CLASS__, 'tm_display_custom_cart_item_thumbnail'], 10, 3);
        add_filter('woocommerce_mini_cart_item_thumbnail', [__CLASS__, 'tm_display_custom_cart_item_thumbnail'], 10, 3);
        add_filter('kses_allowed_protocols', [__CLASS__, 'tm_allow_data_urls'], 10, 3);

    }

    /**
     * Generate Base64 thumbnail from image URL
     */
    public static function tm_get_custom_base64_thumbnail($image_url, $max_width = 150) {

        // Validate and sanitize the image URL
        if (empty($image_url)) return '';

        // Use WordPress functions to download and process the image
        if (!function_exists('download_url')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        // Download the image to a temporary location
        $temp_file = download_url($image_url);
        if (is_wp_error($temp_file)) {
            error_log('[TMPC] download_url failed: ' . print_r([
                'image_url' => $image_url,
                'error' => $temp_file->get_error_message(),
                'data' => $temp_file->get_error_data()
            ], true));
            return '';
        }

        // Use WP_Image_Editor to resize the image
        $editor = wp_get_image_editor($temp_file);
        if (is_wp_error($editor)) {
            error_log('[TMPC] wp_get_image_editor failed: ' . print_r([
                'image_url' => $image_url,
                'temp_file' => $temp_file,
                'error' => $editor->get_error_message(),
                'data' => $editor->get_error_data()
            ], true));
            @unlink($temp_file);
            return '';
        }

        // Resize the image to the specified max width while maintaining aspect ratio
        $editor->resize($max_width, null);

        // Save the resized image to a temporary location
        $upload_dir = wp_upload_dir();

        // Ensure the /wapf/ directory exists
        $wapf_dir = $upload_dir['basedir'] . '/wapf/';

        // Set filename
        $filename = basename($image_url);
        
        // Set target path for the resized image in the /wapf/ directory
        $target_path = $wapf_dir . $filename;

        // Save the resized image directly to the /wapf/ directory with the desired filename
        // For use in the cart and order emails etc
        $resized = $editor->save($target_path, 'image/png');

        // Build the image URL for later use
        $image_url = $upload_dir['baseurl'] . '/wapf/' . $filename;

        // Clean up the original temporary file
        @unlink($temp_file);

        // Handle errors from saving the resized image
        if (is_wp_error($resized) || empty($resized['path'])) {
            error_log('[TMPC] Image save failed: ' . print_r([
                'image_url' => $image_url,
                'target_path' => $target_path,
                'resized' => is_wp_error($resized) ? $resized->get_error_message() : 'No path',
                'data' => is_wp_error($resized) ? $resized->get_error_data() : null
            ], true));
            return '';
        }

        // Read the contents of the resized image file
        $final_data = file_get_contents($resized['path']);

        // Return the image data as a Base64-encoded data URI
        return 'data:image/png;base64,' . base64_encode($final_data);
        
    }

    /**
     * Override cart & mini-cart thumbnail with Base64 image
     */
    public static function tm_display_custom_cart_item_thumbnail($thumbnail, $cart_item) {
        if (!empty($cart_item['custom_image'])) {
            $img_tag = '<img src="' . esc_attr($cart_item['custom_image']) . '" alt="Configured Product Image" style="max-width:150px;height:auto;">';
            if (!empty($cart_item['_tm_custom_product_url'])) {
                return '<a href="' . esc_url($cart_item['_tm_custom_product_url']) . '">' . $img_tag . '</a>';
            }
            return $img_tag;
        }
        return $thumbnail;
    }

    /**
     * Allow data URIs for Base64 images
     */
    public static function tm_allow_data_urls($protocols) {
        $protocols[] = 'data';
        return $protocols;
    }

}
