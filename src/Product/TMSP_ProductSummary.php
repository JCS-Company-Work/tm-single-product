<?php

namespace TMSingleProduct\Product;

class TMSP_ProductSummary {

    public static function init() {
        
        // Add our product summary template to the top of the single product page
        add_action('woocommerce_before_single_product_summary', [__CLASS__, 'render_product_summary'], 5);

        // Add our table specialist template to the single product page
        add_action( 'woocommerce_single_product_summary', [ __CLASS__, 'render_table_specialist' ], 5 );

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

        // Get the current product
        $product = wc_get_product();

        // Get product category for collection name
        $category = self::get_collection();

        ?> 
        
            <div class="tm-top-summary">
                <div class="collection"><?php echo ucwords($category); ?></div>
                <h3 class="product-title"><?php echo $product->get_name(); ?></h3>
                <p class="description"><?php echo $product->get_short_description(); ?></p>
                <p class="price">From <?php echo $product->get_price_html(); ?></p>
                <p class="lead-time">Made to order in 4-6 weeks.</p>
                <div class="features-list">
                    <ul class="list-none">
                        <li>
                            <i class="fa-light fa-check" aria-hidden="true"></i>
                            Luxury Dining Surfaces Crafted for Everyday Living
                        </li>
                        <?php if (self::isSolidProduct()) : ?>
                        <li>
                            <i class="fa-light fa-check" aria-hidden="true"></i>
                            10+ Curated Colours & Styles
                        </li>
                        <?php else : ?>
                        <li>
                            <i class="fa-light fa-check" aria-hidden="true"></i>
                            20+ Curated Colours & Styles
                        </li>
                        <?php endif; ?>
                        <li>
                            <i class="fa-light fa-check" aria-hidden="true"></i>
                            Fully Customisable Design
                        </li>
                        <li>
                            <i class="fa-light fa-check" aria-hidden="true"></i>
                            Seats <span class="seats-number"></span> People
                        </li>
                        <li>
                            <i class="fa-light fa-check" aria-hidden="true"></i>
                            Design Guidance Included
                        </li>
                    </ul>
                </div>
                <div class="buttons">
                    <div class="button-wrapper">
                        <a href="#3d-model" class="tm-button">Create Your Table in 3D</a>
                        <p>Explore colour combinations in real time</p>
                    </div>
                    <div class="button-wrapper">
                        <a href="#" class="tm-button button-reverse whatsapp-chat-btn">Talk to a Table Specialist</a>
                        <div class="guidance-wrapper">
                            <p>Get guidance on size, colour and layout</p>
                            <img src="/wp-content/uploads/Digital_Glyph_Black_RGB_2026.svg" class="whatsapp-logo" alt="Whatsapp logo">
                        </div>
                    </div>
                </div>
            </div>
            <div class="floating-whatsapp"><a href="#" target="_blank" class="whatsapp-chat-btn buttonfloat" aria-label="Chat with us on WhatsApp" rel="noopener noreferrer"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i><br>WhatsApp</a></div>
        <?php

    }

    /**
     * Extract collection based on array of valid product collections
     *
     * @return string $category->name The name of the collection/category for this product
     */
    public static function get_collection() {

        // Array of product collections
        $collections = ['monarch', 'vanguard', 'luna', 'phantom'];

        $categories = get_the_terms(get_the_ID(), 'product_cat');

        if ($categories && !is_wp_error($categories)) {

            foreach ($categories as $category) {
                
                if (in_array(strtolower($category->slug), $collections)) {

                    return strtolower($category->name);
                }
            }
        }
    }

        /**
     * Determine whether product is part of solid collection
     *
     * @return boolean
     */
    public static function isSolidProduct() {

        $categories = get_the_terms(get_the_ID(), 'product_cat');

        if ($categories && !is_wp_error($categories)) {

            foreach ($categories as $category) {
                
                if (strtolower($category->slug) === 'solid') {

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Render table specialist section
     *
     * @return void
     */
    public static function render_table_specialist() {

        ?>
            <div class="table-specialist text-center">
                <h3>Work With a Personal Table Specialist</h3>
                <p>Not sure which size, finish, or combination will work best? Our team can guide you through the process and help you refine your design.</p>
                <div class="table-specialist-button">
                    <a href="#" class="tm-button button-reverse whatsapp-chat-btn">Talk To A Table Specialist</a>
                </div>
                <div class="whatsapp-wrapper">
                    <p class="text-small m-0 border-0">Send us a photo of your space.</p>
                    <img src="/wp-content/uploads/Digital_Glyph_Black_RGB_2026.svg" class="whatsapp-logo" alt="Whatsapp logo">
                </div>
            </div>
            <div class="quote-divider-row">
                <hr class="quote-divider"> 
                <blockquote class="landing-section-quote text-center">
                    <p>"Exceptional delivery service<br> — careful and precise."</p>
                </blockquote>
                <hr class="quote-divider">
            </div>
        <?php
    }

    /**
     * Render explore collection section
     *
     * @return void
     */
    public static function render_explore_collection() {

        ?>
            <div class="quote-divider-row">
                <hr class="quote-divider"> 
                <blockquote class="landing-section-quote text-center">
                    <p>"Thrilled with the table<br> — it looks stunning."</p>
                </blockquote>
                <hr class="quote-divider">
            </div>
            <div class="explore-collection text-center">
                <h3>Explore The Collection</h3>
                <p>Discover curated interiors, finishes, and configurations from the Tailor-made dining table collection.</p>
                <a href="https://tailormade.uk/wp-content/uploads/pdf/brochure/Tailor-made-Luxury-Dining-Tables.pdf" target="_blank" class="text-center text-underline">Download Collection Brochure</a>
                <a class="landing-link-brochure" href="https://tailormade.uk/wp-content/uploads/pdf/brochure/Tailor-made-Luxury-Dining-Tables.pdf" target="_blank">
                <div class="brochure-img-wrapper">
                    <img loading="lazy" decoding="async" fetchpriority="low" width="700" height="505" alt="Download Our Brochure" src="https://store.tailormade.uk/wp-content/uploads/luxury-dining-table-catalogue-700.png?ver=2">
                </div>
                </a>
                <hr class="white-divider">
                <div class="porcelain-stoneware">
                    <div class="porcelain-stoneware-text">
                        <h3 class="porcelain-stoneware-title">Why Porcelain Stoneware</h3>
                        <p>
                            Porcelain stoneware combines the visual richness of natural materials with exceptional everyday durability. Resistant to stains, scratches, and heat, it is designed for modern living without the maintenance associated with marble or wood.
                        </p>
                        <div class="porcelain-stoneware-list-wrapper">
                            <ul class="porcelain-stoneware-list">
                                <li>
                                    <i class="fa-regular fa-circle-1" aria-hidden="true"></i>
                                    Resistant to stains, scratches, and heat
                                </li>
                                <li>
                                    <i class="fa-regular fa-circle-2" aria-hidden="true"></i>
                                    No sealing or maintenance
                                </li>
                                <li>
                                    <i class="fa-regular fa-circle-3" aria-hidden="true"></i>
                                    Designed for everyday living
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="porcelain-stoneware-image">
                        <img  width="700" height="420" loading="lazy" decoding="async" fetchpriority="low" src="https://store.tailormade.uk/wp-content/uploads/why-porcelain-stoneware.jpg" alt="Porcelain Stoneware Table">
                    </div>
                </div>
                
                <hr class="white-divider">
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
                        <p class="faq-question bold"><i class="fa-light fa-check" aria-hidden="true"></i> Can I customise size and shape?</p>
                        <p class="faq-answer">Absolutely. Our website offers four sizes and four shapes as standard but we can create tables in custom dimensions to fit your space perfectly. Contact our personal shopping advisors to discuss bespoke sizes and shapes.</p>
                    </div>
                    <div class="faq-item">
                        <p class="faq-question bold"><i class="fa-light fa-check" aria-hidden="true"></i> How do I choose a colour?</p>
                        <p class="faq-answer">Use the configurator or request samples.</p>
                    </div>
                    <div class="faq-item">
                        <p class="faq-question bold"><i class="fa-light fa-check" aria-hidden="true"></i> Can I see the tables in person?</p>
                        <p class="faq-answer">Yes, Please WhatsApp your personal table specialist for information and assistance.</p>
                    </div>
                    <div class="faq-item">
                        <p class="faq-question bold"><i class="fa-light fa-check" aria-hidden="true"></i> Where are they made?</p>
                        <p class="faq-answer">British Craftsmanship - All tables are made in the UK.</p>
                    </div>
                    <div class="faq-item">
                        <p class="faq-question bold"><i class="fa-light fa-check" aria-hidden="true"></i> Do I need to know exactly what I want?</p>
                        <p class="faq-answer">No - we'll guide you.</p>
                    </div>
                </div>
        <?php

    }

    /**
     * Render find the right table section
     *
     * @return void
     */
    public static function render_find_the_right_table() {
        
        ?>
            <div class="find-the-right-table text-center pt-2">
                <h3 class="find-the-right-table-title">Find the Right Table for your Space</h3>
                <p class="find-the-right-table-text">Whether you already have a configuration in mind or need guidance choosing finishes and sizes, our team will be delighted to help.</p>
                <div class="find-the-right-table-buttons">
                    <div class="find-the-right-table-button-wrapper">
                        <a href="#product-add-to-cart-section" class="tm-button">Continue To Purchase</a>
                    </div>
                    <div class="find-the-right-table-button-wrapper">
                        <a href="#" class="tm-button button-reverse whatsapp-chat-btn">Talk To A Table Specialist</a>
                        <div class="whatsapp-wrapper">
                            <p class="text-small m-0 border-0">One-to-one guidance from our design team</p>
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