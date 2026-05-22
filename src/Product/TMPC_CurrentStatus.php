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
                    <div class="current-status-wrapper">
                        <div class="current-status-swatches">
                            <div class="status-price-container">
                                <p class="bold"><?php echo get_the_title(); ?></p> 
                                <p class="status-price" data-ex-vat-price-base="<?php echo $product->get_price(); ?>"></p>
                                <?php $top_colour = $product_data['selected']['top']['name'] ?? ''; ?>

                                <?php if($top_colour) : ?>

                                    <p class="bold">Surface</p>
                                    <?php echo $top_colour; ?>

                                <?php endif; ?>

                                <?php $base_colour = $product_data['selected']['base']['name'] ?? ''; ?>

                                <?php if($base_colour) : ?>

                                    <p class="bold">Base Finish</p>
                                    <?php echo $base_colour; ?>

                                <?php endif; ?>

                                <?php $metal_colour = $product_data['selected']['metal']['name'] ?? ''; ?>

                                <?php if($metal_colour) : ?>

                                    <p class="bold">Metal Edge</p>
                                    <?php echo $metal_colour; ?>

                                <?php endif; ?>

                            </div>
                            <input type="hidden" name="configured_total" id="configured-total" value="" />
                            <div class="status-layers">
                                <div class="status-layer-images">
                                    <div class="obj-top-colour status-layer">
                                        <div class="status-layer-img">
                                            <img src="<?php echo esc_url($product_data['selected']['top']['url']); ?>" alt="Top Colour">
                                        </div>
                                        <p class="status-layer-title">Top Colour</p>
                                        <p class="status-layer-colour <?php echo implode('-', explode(' ', $top_colour)); ?>-finish"><?php echo $top_colour; ?></p>
                                    </div>

                                    <div class="obj-base status-layer">
                                        <div class="status-layer-img">
                                            <img src="<?php echo esc_url($product_data['selected']['base']['url']); ?>" alt="Base Colour">
                                        </div>
                                        <p class="status-layer-title">Base Colour</p>
                                        <p class="status-layer-colour <?php echo implode('-', explode(' ', $base_colour)); ?>-finish"><?php echo $base_colour; ?></p>
                                    </div>

                                    <?php if (!empty($product_data['selected']['metal']) && !empty($product_data['selected']['metal']['url'])): ?>
                                    <div class="obj-metal-edge-veneer status-layer">
                                        <div class="status-layer-img">
                                            <img src="<?php echo esc_url($product_data['selected']['metal']['url']); ?>" alt="Metal Edge Colour">
                                        </div>
                                        <p class="status-layer-title">Metal Edge</p>
                                        <p class="status-layer-colour <?php echo implode('-', explode(' ', $metal_colour)); ?>-finish"><?php echo $metal_colour; ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="swatch-order-wrapper">
                                <a href="#" class="swatch-order-button">Order Samples</a>
                                <div class="swatch-add-message"></div>
                            </div>
                        </div>
                        <div class="current-status-specification">
                            <div class="status-dimensions-container text-center">
                                <p><b>Product Specification</b></p> 
                                <p class="status-dimensions"></p>
                            </div>
                            <a href="#" class="text-center text-underline">View Full Technical Specification</a>
                            <div class="status-image">

                                <?php $images = TMPC_Images::getCompositeImages(); ?>
                                <?php if ($images): ?>
                                    <img src="<?php echo esc_url($images['700']); ?>" alt="Configured Product">
                                <?php endif; ?>

                            </div>
                            <div class="save-share-download-btns">
                                <a href="#" class="save-share-download-btn">Save Your Design</a>
                                <a href="#" class="save-share-download-btn">Share Via WhatsApp</a>
                                <a href="#" class="save-share-download-btn">Download Your Design PDF</a>
                            </div>
                        </div>
                    </div>
                    <div class="swatch-price-note-wrapper">
                        <p class="swatch-price-note text-small text-center">You can order porcelain stoneware colour swatches and real wood samples for all our models. Porcelain swatches are £15 each, wood swatch samples are £10 each. The cost for these samples will be reimbursed against your table order. Click the Order Swatches button above to add your selected colours to your cart.</p>
                    </div>
                </div>
            </div>

        <?php

    }

}