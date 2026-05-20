<?php

namespace TMProductConfigurator\Product;

class TMPC_ProductSummary {

    public static function init() {
        
        add_action('woocommerce_before_single_product_summary', [__CLASS__, 'render_product_summary'], 5);

    }

    public static function render_product_summary() {

        $product = wc_get_product();

        $primary_cat_id = get_post_meta(get_the_ID(), '_yoast_wpseo_primary_product_cat', true);
        $primary_cat_slug = $primary_cat_id ? get_term_field('slug', $primary_cat_id, 'product_cat') : '';

        ?> 
        
            <div class="tm-top-summary">
                <div class="collection"><?php echo ucwords($primary_cat_slug) . ' Collection'; ?></div>
                <h3 class="product-title"><?php echo $product->get_name(); ?></h3>
                <p class="description"><?php echo $product->get_short_description(); ?></p>
                <p class="price">From <?php echo $product->get_price_html(); ?></p>
                <p class="lead-time">Made to order in 4-6 weeks.</p>
                <div class="features-list">
                    <ul>
                        <li>Beautiful, Heat & Stain Resistant Surfaces</li>
                        <li>2-+ Curated Colours & Styles</li>
                        <li>Fully Customisable Design</li>
                        <li>Seats up to 12 People</li>
                        <li>Design Guidance Included</li>
                    </ul>
                </div>
                <div class="buttons">
                    <div class="button-wrapper">
                        <a href="#" class="tm-button">Create Your Table in 3D</a>
                        <p>Explore colour combinations in real time</p>
                    </div>
                    <div class="button-wrapper">
                        <a href="#" class="tm-button table-specialist">Talk To A Table Specialist</a>
                        <p>Get guidance on size, colour and layout</p>
                    </div>
                </div>
            </div> 
            
        <?php

    }

}