<?php 

    namespace TMProductConfigurator\Product;

    class TMPC_AddToCart {

        public static function init() {


            // Wrap the add to cart form in a custom div for styling purposes
            add_action('woocommerce_before_add_to_cart_form', [__CLASS__, 'add_opening_wrapper_tag'], 10);

            // Add custom content to custom wrapper
            add_action('woocommerce_after_add_to_cart_form', [__CLASS__, 'add_custom_content'], 5);
            
            // Close the custom wrapper div after the add to cart form
            add_action('woocommerce_after_add_to_cart_form', [__CLASS__, 'add_closing_wrapper_tag'], 10);
        }

        public static function add_opening_wrapper_tag() {

            // Do not add on swatches
            if (has_term(227, 'product_cat')) return;

            ?>

            <div class="product-add-to-cart-wrapper">
                <div class="product-add-to-cart-content">
                    <div class="add-to-basket-price text-center"></div>
                    <p class="text-small"><b>Handcrafted to your specification in 4-6 weeks</b></p>
                </div>
                <div class="product-add-to-cart-buttons">

            <?php

        }

        public static function add_custom_content() {

            // Do not add on swatches
            if (has_term(227, 'product_cat')) return;

            ?>
                <div class="table-specilaist-button">
                    <a href="#" class="tm-button button-reverse">Talk To A Table Specialist</a>
                    <div class="whatsapp-wrapper">
                        <p class="text-small m-0 border-0">Not sure what finish will work best?</p>
                        <img class="d-none" src="path/to/whatsapp-logo.png" alt="Whatsapp logo">
                    </div>
                </div>
            </div>
            <ul class="m-0">
                <li>Made to order in the UK</li>
                <li>Samples available</li>
                <li>Design guidance included</li>
            </ul>

            <?php

        }

        public static function add_closing_wrapper_tag() {

            // Do not add on swatches
            if (has_term(227, 'product_cat')) return;

            ?> 
            </div>';

            <div class="quote-divider-row">
                <hr class="quote-divider">
                <blockquote class="landing-section-quote text-center pb-1">
                    <p>"Impeccable installation.<br> — Very happy all round."</p>
                </blockquote>
                <hr class="quote-divider">
            </div>

            <?php

        }

    }