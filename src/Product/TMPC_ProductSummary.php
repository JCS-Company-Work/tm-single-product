<?php

namespace TMProductConfigurator\Product;

use TMProductConfigurator\Product\TMPC_ProductData;
use TMProductConfigurator\Images\TMPC_Images;

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
        
        // Add find the right table section to the single product page
        add_action('woocommerce_after_single_product', [__CLASS__, 'render_find_the_right_table'], 10);


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
                        <div class="guidance-wrapper">
                            <p>Get guidance on size, colour and layout</p>
                            <img src="/wp-content/uploads/Digital_Glyph_Black_RGB_2026.svg" class="whatsapp-logo" alt="Whatsapp logo">
                        </div>
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
                    <p class="text-small m-0 border-0">Send us a photo of your space.</p>
                    <img src="/wp-content/uploads/Digital_Glyph_Black_RGB_2026.svg" class="whatsapp-logo" alt="Whatsapp logo">
                </div>
            </div>
            <div class="quote-divider-row">
                <hr class="quote-divider"> 
                <blockquote class="landing-section-quote text-center pb-1">
                    <p>"Exceptional delivery service<br> — careful and precise."</p>
                </blockquote>
                <hr class="quote-divider">
            </div>
        <?php
    }

    /**
     * Render created by us section
     *
     * @return void
     */
    public static function render_created_by_us() {

        // Fetch product data for current product
        $product_data = TMPC_ProductData::getProductData();

        ?>
            <div class="created-by-us text-center">
                <h3>Created By Us</h3>
                <p>A selection of our most popular colour and finish pairings. Click to load configuration.</p>
            </div> 
        <?php

        // Get product and SKU for current product
        $product = wc_get_product(get_the_ID());
        $sku = $product->get_sku();

        // Array to hold configs
        $configs = [];

        // Re-index array keys
        $colour_options = array_values($product_data['colour_options']);

        // Limit to 8 configurations
        $colour_options = array_slice($colour_options, 0, 8);   

        foreach($colour_options as $colour_option) {

            // Assign top colour
            $top = $colour_option['top']['name'];

            // Randomly select a base colour from the available options for this product
            $base_key = array_rand($colour_option['base']);
            $base = $colour_option['base'][$base_key];

            // Build config array for this combination
            $config = [
                'top' => $top,
                'base' => $base
            ];

            // If a metal option exists for this product, randomly select one and add to config
            if (!empty($colour_option['metal'])) {

                $metal_key = array_rand($colour_option['metal']);
                $metal = $colour_option['metal'][$metal_key];
                $config['metal'] = $metal;
            }

            // Add this config to the configs array
            $configs[] = $config;

        }

        ?>

        <div class="created-by-us-configurations">

            <?php foreach ($configs as $layers) {

                // Generate image paths for this configuration
                $paths = TMPC_Images::processLayers($sku, $layers);
                
                // Build composite image for this configuration and get URL
                TMPC_Images::buildCompositeImage($paths);
                
                // Generate a unique hash for this configuration to use in the image filename
                $hash = md5(json_encode($paths));
                
                // Construct the image URL using the hash
                $dir = site_url('wp-content/themes/tm-shop-child/assets/layers/composites');
                
                // Get 700 size image
                $img_url = "$dir/{$hash}-700.png";

            ?>
                <div class="created-by-us-configuration"
                data-top="<?php echo esc_attr($layers['top']); ?>"
                data-base="<?php echo esc_attr($layers['base']); ?>"
                <?php if (isset($layers['metal'])) : ?>
                    data-metal="<?php echo esc_attr($layers['metal']); ?>"
                <?php endif; ?>
                >
                    <img src="<?php echo esc_url($img_url); ?>" class="created-by-us-img" alt="">
                    <ul class="created-by-us-product-details">
                        <li class="top-layer">
                            <?php echo esc_html(ucwords($layers['top'])); ?>
                        </li>
                        <li class="base-layer">
                            Base: <?php echo esc_html(ucwords($layers['base'])); ?>
                        </li>
                        <?php if (isset($layers['metal'])) : ?>
                            <li class="metal-layer">
                                Edge Veneer: <?php echo esc_html(ucwords($layers['metal'])); ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

            <?php } ?>

        </div>

        <?php

    }

    public static function render_explore_collection() {

        ?>
            <div class="quote-divider-row">
                <hr class="quote-divider"> 
                <blockquote class="landing-section-quote text-center pb-1">
                    <p>"Thrilled with the table<br> — it looks stunning."</p>
                </blockquote>
                <hr class="quote-divider">
            </div>
            <div class="explore-collection text-center">
                <h3>Explore The Collection</h3>
                <p>Discover curated interiors, finishes, and configurations from the Tailor-made dining table collection.</p>
                <a href="#" class="text-center text-underline">Download Collection Brochure</a>
                <a class="landing-link-brochure" href="https://tailormade.uk/wp-content/uploads/pdf/brochure/Tailor-made-Luxury-Dining-Tables.pdf" target="_blank">
                <div class="brochure-img-wrapper">
                    <img width="768" height="497" alt="Download Our Brochure" src="/wp-content/uploads/tm-luxury-dining-table-catalogue-768x497-1.png">
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
                        <img src="https://store.tailormade.uk/wp-content/uploads/luna-solid-curve-taj-mahal-pearl-03.jpg" alt="Porcelain Stoneware Table">
                    </div>
                </div>
                <div class="handcrafted">
                    <h3 class="handcrafted-title">Handcrafted & Delivered with Care</h3>
                    <p>Every table is made to order in the UK and carefully prepared for delivery by our specialist team. From production through to installation, each piece is handled with precision and attention to detail.</p>
                    <ul class="handcrafted-features m-0 list-none bold">
                        <li>Made to order in the UK</li>
                        <li>Specialist delivery team</li>
                        <li>Carefully positioned and installed</li>
                        <li>White glove delivery experience</li>
                    </ul>
                </div>
                <hr class="white-divider">
                <div class="faq">
                    <h3 class="faq-title">Frequently Asked Questions</h3>
                    <div class="faq-item">
                        <p class="faq-question bold">Can I customise size and shape?</p>
                        <p class="faq-answer">Absolutely. Our website offers four sizes and four shapes as standard but we can create tables in custom dimensions to fit your space perfectly. Contact our personal shopping advisors to discuss bespoke sizes and shapes.</p>
                    </div>
                    <div class="faq-item">
                        <p class="faq-question bold">How do I choose a colour?</p>
                        <p class="faq-answer">Use the configurator or request samples.</p>
                    </div>
                    <div class="faq-item">
                        <p class="faq-question bold">Can I see the tables in person?</p>
                        <p class="faq-answer">Yes. Collections are available to view at our North London and Wimbledon partner showrooms.</p>
                    </div>
                    <div class="faq-item">
                        <p class="faq-question bold">Where are you based?</p>
                        <p class="faq-answer">Design and manufacturing is in Chatteris, Cambridgeshire.</p>
                    </div>
                    <div class="faq-item">
                        <p class="faq-question bold">Do I need to know exactly what I want?</p>
                        <p class="faq-answer">No - we'll guide you.</p>
                    </div>
                </div>
        <?php

    }

    public static function render_find_the_right_table() {
        
        ?>
            <div class="find-the-right-table text-center pt-2">
                <h3 class="find-the-right-table-title">Find The Right Table For Your Space</h3>
                <p class="find-the-right-table-text">Not sure which size, finish, or combination will work best? Our team can guide you through the process and help you refine your design.</p>
                <div class="find-the-right-table-buttons">
                    <div class="find-the-right-table-button-wrapper">
                        <a href="#" class="tm-button">Continue To Purchase</a>
                    </div>
                    <div class="find-the-right-table-button-wrapper">
                        <a href="#" class="tm-button button-reverse">Talk To A Table Specialist</a>
                        <div class="whatsapp-wrapper">
                            <p class="text-small m-0 border-0">Send us a photo of your space.</p>
                            <img src="/wp-content/uploads/Digital_Glyph_Black_RGB_2026.svg" class="whatsapp-logo" alt="Whatsapp logo">
                        </div>
                    </div>
                </div>
                <div class="find-the-right-table-call-back">
                    <div class="call-back-content">
                        <div class="call-back-header">
                            <ul>
                                <li><a href="https://wa.me/447782274315?text=Hi,%20I%20have%20a%20question%20about%20your%20dining%20table%20-%20<?php echo get_permalink();?>" target="_blank" class="call-back-link wa-link"><i class="fa-brands fa-whatsapp"></i> WhatsApp us</a></li>
                                <li><a href="tel:020 3848 5212" class="call-back-link tel-no"><i class="fa-light fa-phone"></i> Call us on 020 3848 5212</a></li> 
                                <li>Or share your details below and we'll be in touch.</li>
                            </ul>
                        </div>
                        <div class="call-back-form">
                            <div id="cognito-form"></div>
                                <script src="/wp-content/themes/tm-shop-child/assets/js/cognito-loader.js" data-form-id="60"></script> 
                        </div>
                        <div class="call-back-footer"></div>
                    </div>
                </div>
            </div>
        <?php
    }
    

}