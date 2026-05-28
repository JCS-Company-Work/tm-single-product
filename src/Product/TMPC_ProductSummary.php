<?php

namespace TMProductConfigurator\Product;

class TMPC_ProductSummary {

    public static function init() {
        
        // Add our product summary template to the top of the single product page
        add_action('woocommerce_before_single_product_summary', [__CLASS__, 'render_product_summary'], 5);

        // Add our table specialist template to the single product page
        add_action( 'woocommerce_single_product_summary', [ __CLASS__, 'render_table_specialist' ], 5 );
        
        // Add our created by us template to the single product page
        add_action( 'woocommerce_single_product_summary', [ __CLASS__, 'render_created_by_us' ], 5 );

        // Add our explore the collection template to the single product page
        add_action('woocommerce_after_single_product_summary', [__CLASS__, 'render_explore_collection'], 25);


    }

    /**
     * Render product summary section
     *
     * @return void
     */
    public static function render_product_summary() {

        $product = wc_get_product();

        $primary_cat_id = get_post_meta(get_the_ID(), '_yoast_wpseo_primary_product_cat', true);
        $primary_cat_slug = $primary_cat_id ? get_term_field('slug', $primary_cat_id, 'product_cat') : '';

        ?> 
        
            <div class="tm-top-summary pt-2 pb-2">
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
                        <a href="#" class="tm-button w-100">Create Your Table in 3D</a>
                        <p>Explore colour combinations in real time</p>
                    </div>
                    <div class="button-wrapper">
                        <a href="#" class="tm-button w-100 button-reverse">Talk To A Table Specialist</a>
                        <p>Get guidance on size, colour and layout</p>
                        <img src="path/to/whatsapp-logo.png" alt="Whatsapp logo">
                    </div>
                </div>
            </div>
            
        <?php

    }

    /**
     * Render table specialist section
     *
     * @return void
     */
    public static function render_table_specialist() {

        ?>
            <div class="table-specialist text-center pb-1">
                <h3>Work With a Personal Table Specialist</h3>
                <p>Not sure which size, finish, or combination will work best? Our team can guide you through the process and help you refine your design.</p>
                <a href="#" class="tm-button button-reverse">Talk To A Table Specialist</a>
                <div class="whatsapp-wrapper">
                    <p class="text-small margin-0 border-0">Send us a photo of your space.</p>
                    <img src="path/to/whatsapp-logo.png" alt="Whatsapp logo">
                </div>
            </div> 
            <blockquote class="landing-section-quote text-center pb-1"><p>"Exceptional delivery service<br> — careful and precise."</p></blockquote>
        <?php
    }

    /**
     * Render created by us section
     *
     * @return void
     */
    public static function render_created_by_us() {

        ?>
            <div class="created-by-us text-center">
                <h3>Created By Us</h3>
                <p>A selection of our most popular colour and finish pairings. Click to load configuration.</p>
            </div> 
        <?php

    }

    public static function render_explore_collection() {

        ?>
            <blockquote class="landing-section-quote text-center pb-1"><p>"Thrilled with the table<br> — it looks stunning."</p></blockquote>
            <div class="explore-collection text-center">
                <h3>Explore The Collection</h3>
                <p>Discover curated interiors, finishes, and configurations from the Tailor-made dining table collection.</p>
                <a href="#" class="text-center text-underline">Download Collection Brochure</a>
                <a class="landing-link-brochure" href="https://tailormade.uk/wp-content/uploads/pdf/brochure/Tailor-made-Luxury-Dining-Tables.pdf" target="_blank">
                <div class="flex-h-center pt-2">
                    <img width="768" height="497" alt="Download Our Brochure" src="https://store.tailormade.uk/wp-content/uploads/tm-luxury-dining-table-catalogue-768x497.jpg?ver=4">
                </div>
                </a>
                <div class="porcelain-stoneware pt-2">
                    <div class="porcelain-stoneware-text">
                        <h3 class="porcelain-stoneware-title">Why Porcelain Stoneware</h3>
                        <p>
                            The surface defines how your table looks, lives, and lasts. Porcelain stoneware offers the same visual richness with greater durability. Where marble requires care and wood can warp, porcelain remains consistent and refined. Fine dining tables made to order for those seeking long-term quality and refined design.
                        </p>
                        <ul>
                            <li>Resistant to stains, scratches, and heat</li>
                            <li>No sealing or maintenance</li>
                            <li>Designed for everyday living</li>
                        </ul>
                    </div>
                    <div class="porcelain-stoneware-image">
                        <img src="https://store.tailormade.uk/wp-content/uploads/luna-edge-curve-macchia-vecchia-05-280x200.jpg" alt="Porcelain Stoneware Table">
                    </div>
                </div>
                <div class="handcrafted">
                    <h3 class="handcrafted-title">Handcrafted& Delivered with Care</h3>
                    <p>Every table is made to order in the UK and carefully prepared for delivery by our specialist team. From production through to installation, each piece is handled with precision and attention to detail.</p>
                </div>
            </div>
        <?php

    }

}