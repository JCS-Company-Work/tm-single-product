<?php 

    namespace TMProductConfigurator\Product;

    class TMPC_AddToCart {

        public static function init() {


            // Wrap the add to cart form in a custom div for styling purposes
            add_action('woocommerce_before_add_to_cart_form', [__CLASS__, 'add_opening_wrapper_tag'], 10);

            // Add custom content to custom wrapper
            add_action('woocommerce_after_add_to_cart_form', [__CLASS__, 'add_custom_content'], 5);

            // Wrap add-to-cart button directly (inside form)
            add_action('woocommerce_before_add_to_cart_button', [__CLASS__, 'add_button_opening_wrapper_tag'], 5);
            add_action('woocommerce_after_add_to_cart_button', [__CLASS__, 'add_button_closing_wrapper_tag'], 99);
            
            // Close the custom wrapper div after the add to cart form
            add_action('woocommerce_after_add_to_cart_form', [__CLASS__, 'add_closing_wrapper_tag'], 10);
        }

        /**
         * Add opening wrapper div before the add to cart form 
         *
         * @return void
         */
        public static function add_opening_wrapper_tag() {

            // Do not add on swatches
            if (has_term(227, 'product_cat')) return;

            ?>

            <div id="product-add-to-cart-section" class="product-add-to-cart-wrapper">
                <div class="product-add-to-cart-content">
                    <div class="add-to-basket-price text-center"><p></p></div>
                    <p class="text-small"><b>Handcrafted to your specification in 4-6 weeks</b></p>
                </div>
                <div class="product-add-to-cart-buttons">

            <?php

        }

        /**
         * Add custom content after cart form
         *
         * @return void
         */
        public static function add_custom_content() {

            // Do not add on swatches
            if (has_term(227, 'product_cat')) return;

            ?>
                <div class="table-specialist-button">
                    <a href="#" class="tm-button button-reverse whatsapp-chat-btn">Talk To A Table Specialist</a>
                    <div class="whatsapp-wrapper">
                        <p class="text-small m-0 border-0">Not sure what finish will work best?</p>
                        <img src="/wp-content/uploads/Digital_Glyph_Black_RGB_2026.svg" class="whatsapp-logo" alt="Whatsapp logo">
                    </div>
                </div>
            </div>
            <div class="add-to-cart-list-wrapper">
                <ul class="add-to-cart-list list-none">
                    <li>
                        <i class="fa-light fa-check" aria-hidden="true"></i>
                        Made to order in the UK
                    </li>
                    <li>
                        <i class="fa-light fa-check" aria-hidden="true"></i>
                        Samples available
                    </li>
                    <li>
                        <i class="fa-light fa-check" aria-hidden="true"></i>
                        Design guidance included
                    </li>
                </ul>
            </div>

            <?php

        }

        /**
         * Add opening wrapper directly before add-to-cart button
         *
         * @return void
         */
        public static function add_button_opening_wrapper_tag() {

            // Do not add on swatches
            if (has_term(227, 'product_cat')) return;

            echo '<div class="add-to-cart-button-wrapper">';
        }

        /**
         * Add closing wrapper directly after add-to-cart button
         *
         * @return void
         */
        public static function add_button_closing_wrapper_tag() {

            // Do not add on swatches
            if (has_term(227, 'product_cat')) return;

            echo '</div>';
        }

        /**
         * Add bottom section and closing div after cart form
         *
         * @return void
         */
        public static function add_closing_wrapper_tag() {

            // Do not add on swatches
            if (has_term(227, 'product_cat')) return;

            ?> 
            </div>

            <div class="quote-divider-row">
                <hr class="quote-divider">
                <blockquote class="landing-section-quote text-center">
                    <p>"Impeccable installation.<br> Very happy all round."</p>
                </blockquote>
                <hr class="quote-divider">
            </div>

            <?php

        }

    }