<?php

    namespace TMSingleProduct\Overrides;

    class TMSP_LayoutOverrides {

        public static function init() {

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

    }