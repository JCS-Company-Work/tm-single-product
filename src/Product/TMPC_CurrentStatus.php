<?php

namespace TMProductConfigurator\Product;

use TMProductConfigurator\Images\TMPC_Images;

class TMPC_CurrentStatus {

    public static function init() {

        // Add our current status template after WC add to cart button
        add_action('woocommerce_after_add_to_cart_button', [__CLASS__, 'add_current_status'], 50);

    }

    /**
     * Render the current status section on the product page, showing the selected options and a preview image
     *
     * @return void
     */
    public static function add_current_status() {
        
        global $product;
       
        ?>

            <div class="current-status-container">

                <div class="current-status">
                    <div class="status-qrcode"></div>
                    <h2 class="status-title"><?php echo get_the_title(); ?></h2>
                    <div class="status-price-container">
                        <p>Configured Price:</p> <span class="status-price" data-ex-vat-price-base="<?php echo $product->get_price(); ?>"></span>
                    </div>
                    <input type="hidden" name="configured_total" id="configured-total" value="" />
                    <div class="status-image">

                    <?php echo TMPC_Images::serveImagesOnPageLoad(); ?>

                    </div>
                    
                    <div class="status-dimensions-container">
                        <p>Selected Model:</p> <span class="status-dimensions"></span>
                    </div>
                    <div class="status-specification-text">
                        <p></p>
                    </div>
                    <div class="status-layers">
                        <div class="status-layer-images">
                            <div class="obj-top-colour status-layer">
                                <div class="status-layer-img">
                                    <img src="" alt="Top Colour">
                                </div>
                                <p class="status-layer-title">Top Colour</p>
                                <p class="status-layer-colour"></p>
                            </div>

                            <div class="obj-base status-layer">
                                <div class="status-layer-img">
                                    <img src="" alt="Base Colour">
                                </div>
                                <p class="status-layer-title">Base Colour</p>
                                <p class="status-layer-colour"></p>
                            </div>

                            <div class="obj-metal-edge-veneer status-layer">
                                <div class="status-layer-img">
                                    <img src="" alt="Metal Edge Colour">
                                </div>
                                <p class="status-layer-title">Metal Veneer</p>
                                <p class="status-layer-colour"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php

    }

}