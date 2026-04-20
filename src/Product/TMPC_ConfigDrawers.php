<?php

    namespace TMProductConfigurator\Product;

    use TMProductConfigurator\Product\TMPC_ProductData;

    class TMPC_ConfigDrawers {

        public static function init() {
            // This class is responsible for rendering the configuration drawers on the product page
        }

        public static function render() {

            $product = wc_get_product(get_the_ID());

            // Only render if product is valid and has options
            if (!$product) {
                echo '<p>Product not found.</p>';
            return;

            }

            // Get valid colour combinations for current product
            $colourOptions = TMPC_ProductData::getAvailableColoursForProduct($product);

            // Loop over returned images and create swatch paths

            }
        }