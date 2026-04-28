<?php

namespace TMProductConfigurator\Gallery;

if (!defined('ABSPATH')) exit;

/**
 * 
 */
class TMPC_GalleryAssets {

    public static function init() {

        // Enqueue scripts
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets'], 99);

        // Defer non-critical styles
        add_filter('style_loader_tag', [__CLASS__, 'deferStyles'], 10, 2);

        // Remove native WooCommerce gallery support
        add_action('after_setup_theme', [__CLASS__, 'removeWCSupport'], 100);

        // Dequeue non-needed styles and scripts
        add_action('wp_enqueue_scripts', [__CLASS__, 'dequeue_styles'], 100);
        add_action('wp_enqueue_scripts', [__CLASS__, 'dequeue_scripts'], 100);

    }

    /**
     * Remove native Woocommerce gallery support
     */
    public static function removeWCSupport() {

        remove_theme_support('wc-product-gallery-zoom');
        remove_theme_support('wc-product-gallery-lightbox');
        remove_theme_support('wc-product-gallery-slider');
        remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);

    }

    /**
     * Method to dequeue non-needed native styles
     */
    public static function dequeue_styles() {

        wp_dequeue_style('photoswipe');
        wp_dequeue_style('photoswipe-default-skin');
        wp_dequeue_style('woocommerce_photoswipe');
        wp_dequeue_style('woocommerce_photoswipe_skin');

    }
    
    /**
     * Method to dequeue non-needed native scripts
     */
    public static function dequeue_scripts() {

        // Remove WC-bundled Photoswipe 4
        wp_dequeue_script('woocommerce_photoswipe');
        wp_dequeue_script('woocommerce_photoswipe_ui');

    }

    /**
     * Enqueue styles and scripts
     */
    public static function enqueue_assets() {
        
        // Only enqueue assets is this is a non-swatch product page
        $product = wc_get_product(get_the_ID());
        if (!self::should_load($product)) return false;

        // Register core Photoswipe module
        wp_register_script_module(
            'photoswipe',
            TMPC_URL . 'assets/js/photoswipe/photoswipe.esm.min.js',
            [],
            TMPC_VERSION
        );

        // Register Lightbox module, depends on photoswipe
        wp_register_script_module(
            'photoswipe-lightbox',
            TMPC_URL . 'assets/js/photoswipe/photoswipe-lightbox.esm.min.js',
            ['photoswipe'],
            TMPC_VERSION
        );

        // Expose globally after module loads
        wp_add_inline_script('photoswipe', 'window.PhotoSwipe = PhotoSwipe;', 'after');
        wp_add_inline_script('photoswipe-lightbox', 'window.PhotoSwipeLightbox = PhotoSwipeLightbox;', 'after');

        // Enqueue gallery JS, depends on lightbox
        wp_enqueue_script_module(
            'tm-gallery-js',
            TMPC_URL . 'assets/js/gallery/TMGallery.js',
            ['photoswipe-lightbox'],
            TMPC_VERSION
        );

        // Inline config
        $config = [
            'ajax' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tm_gallery_nonce'),
        ];
        echo '<script>window.tmGalleryConfig = ' . wp_json_encode($config) . ';</script>';

        // Photoswipe CSS
        wp_enqueue_style(
            'tm-photoswipe',
            TMPC_URL . 'assets/css/gallery/photoswipe.css',
            [],
            TMPC_VERSION
        );
    }

    /**
     * Defer non-critical CSS
     *
     * @param string $html   The HTML tag for the enqueued style.
     * @param string $handle The style handle.
     * @return string        The (possibly modified) HTML tag.
     */
    public static function deferStyles($html, $handle) {

        // Defer tm-photoswipe CSS
        if ($handle === 'tm-photoswipe') {
            $html = str_replace(
                "rel='stylesheet'",
                "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"",
                $html
            );
        }

        // Return the modified or unmodified HTML
        return $html;

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

}