<?php

namespace TMProductConfigurator\Product;

use TMProductConfigurator\Images\TMPC_Images;
use TMProductConfigurator\Product\TMPC_ProductData;

class TMPC_CurrentStatus {

    public static function init() {

        // Add our current status template after WC add to cart button
        add_action('woocommerce_single_product_summary', [__CLASS__, 'add_current_status'], 10);

        // Re-add add to cart button in new location within current status section
        add_action('woocommerce_after_single_product_summary', 'woocommerce_template_single_add_to_cart', 15);
        
    }

    /**
     * Render the current status section on the product page, showing the selected options and a preview image
     *
     * @return void
     */
    public static function add_current_status() {
        
        global $product, $post;

        // Early return if this is a swatch product to avoid fatal error
        if ($post instanceof \WP_Post && has_term('swatch', 'product_cat', $post->ID)) {
            return;
        }

        $product_data = TMPC_ProductData::getProductData();

        ?>

            <div class="current-status-container">

                <div class="current-status">
                    <h3>Your Creation</h3>
                    <div class="status-qrcode"></div>
                    <h2 class="status-title"><?php echo get_the_title(); ?></h2>
                    <div class="status-price-container">
                        <p>Configured Price:</p> <span class="status-price" data-ex-vat-price-base="<?php echo $product->get_price(); ?>"></span>
                    </div>
                    <input type="hidden" name="configured_total" id="configured-total" value="" />
                    <div class="status-image">

                        <?php $images = TMPC_Images::getCompositeImages(); ?>
                        <?php if ($images): ?>
                            <img src="<?php echo esc_url($images['700']); ?>" alt="Configured Product">
                        <?php endif; ?>

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
                                    <img src="<?php echo esc_url($product_data['selected']['top']['url']); ?>" alt="Top Colour">
                                </div>
                                <p class="status-layer-title">Top Colour</p>
                                <p class="status-layer-colour <?php echo implode('-', explode(' ', $product_data['selected']['top']['name'])); ?>-finish"><?php echo $product_data['selected']['top']['name']; ?></p>
                            </div>

                            <div class="obj-base status-layer">
                                <div class="status-layer-img">
                                    <img src="<?php echo esc_url($product_data['selected']['base']['url']); ?>" alt="Base Colour">
                                </div>
                                <p class="status-layer-title">Base Colour</p>
                                <p class="status-layer-colour <?php echo implode('-', explode(' ', $product_data['selected']['base']['name'])); ?>-finish"><?php echo $product_data['selected']['base']['name']; ?></p>
                            </div>

                            <?php if (!empty($product_data['selected']['metal']) && !empty($product_data['selected']['metal']['url'])): ?>
                            <div class="obj-metal-edge-veneer status-layer">
                                <div class="status-layer-img">
                                    <img src="<?php echo esc_url($product_data['selected']['metal']['url']); ?>" alt="Metal Edge Colour">
                                </div>
                                <p class="status-layer-title">Metal Veneer</p>
                                <p class="status-layer-colour <?php echo implode('-', explode(' ', $product_data['selected']['metal']['name'])); ?>-finish"><?php echo $product_data['selected']['metal']['name']; ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="swatch-order-wrapper">
                            <a href="#" class="tm-button swatch-order-button">Order Samples</a>
                            <p>You can order porcelain stoneware colour swatches and real wood samples for all our models. Porcelain swatches are £15 each, wood swatch samples are £10 each. The cost for these samples will be reimbursed against your table order. Click the Order Swatches button above to add your selected colours to your cart.</p>
                        </div>
                    </div>
                </div>
            </div>

        <?php

    }

}