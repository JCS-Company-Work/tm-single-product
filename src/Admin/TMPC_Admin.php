<?php

    namespace TMProductConfigurator\Admin;

    use TMProductConfigurator\ColourOptions\TMPC_ColourOptionsService;

    /**
     * Class to handle admin functionality for the product configurator plugin
     */
    class TMPC_Admin {

        public static function init() {

            // Add colour dropdowns for product configuration settings
            add_action('add_meta_boxes', [self::class, 'add_colour_dropdowns']);

            // Save meta box data when product is saved
            add_action('save_post', [self::class, 'save_default_colours']);

        }

        /**
         * Add colour dropdowns for product configuration settings
         *
         * @return void
         */
        public static function add_colour_dropdowns() {

            add_meta_box(
                'tmpc_default_colours',
                'Default Colours',
                [ '\\TMProductConfigurator\\Admin\\TMPC_Admin', 'render_default_colours_box' ],
                'product',
                'normal'
            );
        }

        /**
         * Render colours dropdown based on product type (slim, solid, edge)
         * Base and metal colour dropdowns update depending on options available for current top colour
         *
         * @param WP_Post $post
         * @return void
         */
        public static function render_default_colours_box($post) {

            // Nonce for security
            wp_nonce_field('tmpc_save_colours', 'tmpc_colours_nonce');

            // Existing values (if saved)
            $saved_top   = get_post_meta($post->ID, '_tmpc_top_colour', true);
            $saved_base  = get_post_meta($post->ID, '_tmpc_base_colour', true);
            $saved_metal = get_post_meta($post->ID, '_tmpc_metal_colour', true);

            // Checkif product belongs to slim, solid or edge categories
            $productType = self::get_product_type(wc_get_product($post->ID));

            // Get colour options data
            $colourOptions = TMPC_ColourOptionsService::getAdminColourOptions();

            // Get colour options for this product type
            $availableColours = $colourOptions[$productType] ?? [];

            if(!empty($availableColours)) : ?>
             <select id="top-colour" name="tmpc_top_colour">
                <option value="">Select a colour</option>
                <?php foreach ($availableColours as $topColour => $options) : ?>
                    <option value="<?php echo esc_attr($topColour); ?>">
                        <?php echo esc_html($topColour); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="base-colour" name="tmpc_base_colour" style="display:none;">
                <option value="">Select base</option>
            </select>

            <select id="metal-colour" name="tmpc_metal_colour" style="display:none;">
                <option value="">Select metal</option>
            </select>

            <script>
                window.TMPC_COLOURS = <?php echo wp_json_encode($availableColours); ?>;
                window.TMPC_SAVED = {
                    top: "<?php echo esc_js($saved_top); ?>",
                    base: "<?php echo esc_js($saved_base); ?>",
                    metal: "<?php echo esc_js($saved_metal); ?>"
                };

                document.addEventListener('DOMContentLoaded', () => {

                    const data = window.TMPC_COLOURS;
                    const saved = window.TMPC_SAVED;

                    const topSelect   = document.getElementById('top-colour');
                    const baseSelect  = document.getElementById('base-colour');
                    const metalSelect = document.getElementById('metal-colour');

                    const capitaliseWords = str =>
                        str.replace(/\b\w/g, char => char.toUpperCase());

                    const populate = (select, values, placeholder, selectedValue = '') => {
                        select.innerHTML = `<option value="">${placeholder}</option>`;

                        if (!values) return;

                        values.forEach(val => {
                            const opt = document.createElement('option');
                            opt.value = val;
                            opt.textContent = capitaliseWords(val);

                            if (val === selectedValue) {
                                opt.selected = true;
                            }

                            select.appendChild(opt);
                        });

                        select.style.display = '';
                    };

                    const updateOptions = (selectedTop, restore = false) => {

                        baseSelect.style.display = 'none';
                        metalSelect.style.display = 'none';

                        if (!selectedTop || !data[selectedTop]) return;

                        const config = data[selectedTop];

                        populate(
                            baseSelect,
                            config.base,
                            'Select base',
                            restore ? saved.base : ''
                        );

                        if (config.metal) {
                            populate(
                                metalSelect,
                                config.metal,
                                'Select metal',
                                restore ? saved.metal : ''
                            );
                        }
                    };

                    // Change handler
                    topSelect.addEventListener('change', () => {
                        updateOptions(topSelect.value);
                    });

                    // Restore saved state on load
                    if (saved.top) {
                        topSelect.value = saved.top;
                        updateOptions(saved.top, true);
                    }

                });

            </script>

            <?php endif;
        }

        /**
         * Determine product type from WP categories
         *
         * @param object $product
         * @return string|null Returns 'solid', 'slim', 'edge' or null if no match
         */
        public static function get_product_type($product) {

            // Get product category slugs
            $terms = get_the_terms($product->get_id(), 'product_cat');

            if (empty($terms) || is_wp_error($terms)) {
                return null;
            }

            // Define IDs of types to check
            $ids = [242, 243, 244];

            // Return the slug of the first matching category (ensure term_id is cast to int for comparison)
            foreach($terms as $term) {
                if (in_array((int) $term->term_id, $ids)) {
                    return $term->slug; 
                }

            }

            // Return null if no matching category found
            return null; 

        }

        /**
         * Save default colour selections to wp_postmeta when product is saved
         *
         * @param int $post_id The ID of the post being saved
         * @return void
         */
        public static function save_default_colours($post_id) {

            // Security checks
            if (!isset($_POST['tmpc_colours_nonce']) ||
                !wp_verify_nonce($_POST['tmpc_colours_nonce'], 'tmpc_save_colours')) {
                return;
            }
error_log(print_r($_POST, true));
            // Avoid autosave overwrite
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            // Check permissions
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }

            // Save fields
            $fields = [
                '_tmpc_top_colour'   => 'tmpc_top_colour',
                '_tmpc_base_colour'  => 'tmpc_base_colour',
                '_tmpc_metal_colour' => 'tmpc_metal_colour',
            ];

            foreach ($fields as $meta_key => $post_key) {
                if (isset($_POST[$post_key])) {
                    update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$post_key]));
                }
            }
        }

    }