<?php

    namespace TMSingleProduct\Admin;

    // use TMSingleProduct\ColourOptions\TMSP_ColourOptionsService; // Removed - replaced by tm-three-viewer

    /**
     * Class to handle admin functionality for the product configurator plugin
     */
    class TMSP_Admin {

        /**
         * Add action hooks
         *
         * @return void
         */
        public static function init() {

            // Register custom tabs for colours and model sizes
            add_filter('woocommerce_product_data_tabs', [self::class, 'add_configurator_tabs']);

            // Render panels for custom tabs
            add_action('woocommerce_product_data_panels', [self::class, 'render_configurator_panels']);

            // Save custom fields when product is saved
            add_action('woocommerce_admin_process_product_object', [self::class, 'save_configurator_fields']);

            // Enqueue admin assets
            add_action('admin_enqueue_scripts', [self::class, 'enqueue_admin_assets']);

        }

        /**
         * Enqueue admin assets
         * 
         * @param string $hook The current admin page hook
         * @return void
         */
        public static function enqueue_admin_assets($hook) {
            
            // Only load on product edit pages
            if ($hook === 'post.php' || $hook === 'post-new.php') {
                wp_enqueue_script('tmpc-admin-js', TMSP_URL . 'assets/js/admin/Admin.js', [], TMSP_VERSION, true);
                wp_enqueue_style('tmpc-admin-css', TMSP_URL . 'assets/css/admin/admin.css', [], TMSP_VERSION);
            }
        }

        /**
         * Check if the current save action is an autosave
         *
         * @return boolean
         */
        protected static function is_autosave() {

            // Check if doing autosave
            $autosave = defined('DOING_AUTOSAVE') && DOING_AUTOSAVE;
            
            // Apply filter for testing purposes
            return apply_filters('tmpa_is_autosave', $autosave);
        }

        /**
         * Add custom tabs for the product configurator settings to the WooCommerce product data metabox
         *
         * @param array $tabs Existing product data tabs
         * @return array Modified product data tabs
         */
        public static function add_configurator_tabs($tabs) {

            // Add custom tabs for colours
            $tabs['tmpa_colours'] = [
                'label'    => __('Select Colours', 'tm-product-configurator'),
                'target'   => 'tmpa_colours_panel',
                'class'    => [],
                'priority' => 60,
            ];

            // Add custom tab for model sizes
            $tabs['tmpa_model_size'] = [
                'label'    => __('Model Sizes', 'tm-product-configurator'),
                'target'   => 'tmpa_model_size_panel',
                'class'    => [],
                'priority' => 61,
            ];

            // Return modified tabs array
            return $tabs;

        }

        /**
         * Render colour and model size options panels in product area
         *
         * @return void
         */
        public static function render_configurator_panels() {

            // Get current post ID
            $post_id = get_the_ID();
            
            // Get post object
            $post = get_post($post_id);
            
            // Render opening div tag and link to registered panels
            echo '<div id="tmpa_colours_panel" class="panel woocommerce_options_panel">';
            
            // Render default colours panel
            self::render_default_colours_box($post);
            
            // Close first panel
            echo '</div>';
            
            //Open second panel and render model size options
            echo '<div id="tmpa_model_size_panel" class="panel woocommerce_options_panel">';
            
            // Render model size panel
            self::render_model_size_box($post);
            
            // Close second panel
            echo '</div>';

        }

        /**
         * Save configurator fields when product is saved
         *
         * @param object $product
         * @return void
         */
        public static function save_configurator_fields($product) {

            // Get post ID from product object
            $post_id = $product->get_id();
            
            // Save default colours
            self::save_default_colours($post_id);

            // Save model size options
            self::save_model_size($post_id);
        }

        /**
         * Add model size fields to product admin area
         *
         * @param \WP_Post $post
         * @return void
         */
        public static function render_model_size_box($post) {

            // Nonce for security
            wp_nonce_field('tmpa_save_model_size', 'tmpa_model_size_nonce');

            // Existing value (if saved)
            $saved_sizes = get_post_meta($post->ID, '_tmpa_model_size', true);
            if (!is_array($saved_sizes)) {
                $saved_sizes = [];
            }

            // Get product object
            $product = function_exists('wc_get_product') ? wc_get_product($post->ID) : null;

            // Determine product type for conditional options (solid, slim, edge)
            $product_type = 'slim/edge'; // Default fallback
            $product_cats = get_the_terms($post->ID, 'product_cat');
            if ($product_cats && !is_wp_error($product_cats)) {
                foreach ($product_cats as $cat) {
                    if (in_array($cat->slug, ['solid', 'slim', 'edge', 'circular'])) {
                        $product_type = $cat->slug;
                        break;
                    }
                }
            }

            // Use combined options for slim and edge products
            if($product_type === 'slim' || $product_type === 'edge') {
                $product_type = 'slim/edge'; 
            }

            // Set default sizes based on product type and circular term
            $default_sizes = self::set_default_model_sizes($product, $product_type);

            // Use saved sizes if present, otherwise default
            $sizes = !empty($saved_sizes) ? $saved_sizes : $default_sizes;

            ?>
            <h3>Select available models for: <?php echo esc_html($product->get_name()); ?></h3>
            <table id="tmpa-model-sizes-table" class="widefat">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>Dimensions</th>
                        <th>Price</th>
                        <th>Default</th>
                        <th>Remove</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($sizes as $i => $size) : ?>
                    <tr>
                        <td>
                            <select name="tmpa_model_sizes[<?php echo $i; ?>][label]" class="tmpa-size-label">
                                <option value="">Select size</option>
                                <?php foreach ($default_sizes as $opt) : ?>
                                    <option value="<?php echo esc_attr($opt['label']); ?>" <?php selected($size['label'], $opt['label']); ?>><?php echo esc_html($opt['label']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="text" name="tmpa_model_sizes[<?php echo $i; ?>][dims]" value="<?php echo esc_attr($size['dims']); ?>" class="tmpa-size-dims" readonly /></td>
                        <td><input type="number" name="tmpa_model_sizes[<?php echo $i; ?>][price]" value="<?php echo esc_attr($size['price']); ?>" class="tmpa-size-price" /></td>
                        <td>
                            <label class="tmpa-toggle-switch">
                                <input type="radio" name="tmpa_model_sizes_default" value="<?php echo $i; ?>" <?php checked(!empty($size['is_default'])); ?> />
                                <span class="tmpa-slider"></span>
                            </label>
                        </td>
                        <td><button type="button" class="button tmpa-remove-size">Remove</button></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <button type="button" class="button button-primary" id="tmpa-add-size">Add Size</button>
            
            <!-- Pass model size data to JS -->
            <script>
                (function($){
                    window.TMPA_MODEL_SIZE_DATA = {
                        rowIdx: <?php echo count($sizes); ?>,
                        sizeData: <?php echo json_encode($default_sizes); ?>
                    };
                })(jQuery);
            </script>
            
            <?php
            
        }

        /**
         * Set default model sizes for circulr or standard product
         *
         * @param object $product The product object
         * @param string $product_type The type of the product
         * @return array The default sizes for the product
         */
        public static function set_default_model_sizes($product, $product_type) {

            $default_sizes = "";

            // Get default sizes for dropdown options
            $size_options = get_option('tmpa_model_default_sizes', []);
            
            // Check if product also has the 'circular' term (by slug)
            $is_circular = false;

            if ($product && taxonomy_exists('product_cat')) {
                $terms = get_the_terms($product->get_id(), 'product_cat');
                if ($terms && !is_wp_error($terms)) {
                    foreach ($terms as $term) {
                        if ($term->slug === 'circular') {
                            $is_circular = true;
                            break;
                        }
                    }
                }
            }
            if ($is_circular) {
                              
                // Merge circular sizes with existing values for this product type
                $default_sizes = array_merge(
                    isset($size_options['circular']) ? $size_options['circular'] : [],
                    isset($size_options[$product_type]) ? $size_options[$product_type] : []
                );
            
            } else {
                // For non-circular products, use options based on product type
                $default_sizes = isset($size_options[$product_type]) ? $size_options[$product_type] : [];
            }

            return $default_sizes;
        }

        /**
         * Save model size selection to wp_postmeta when product is saved
         *
         * @param int $post_id The ID of the post being saved
         * @return void
         */
        public static function save_model_size($post_id) {

            // Execute security checks
            if (!isset($_POST['tmpa_model_size_nonce']) || !wp_verify_nonce($_POST['tmpa_model_size_nonce'], 'tmpa_save_model_size')) {
                return;
            }
            // Avoid autosave overwrite
            if (self::is_autosave()) {
                return;
            }

            // Check if user has permission to edit the post
            if (!$post_id || !current_user_can('edit_post', $post_id)) {
                return;
            }

            // Get sizes array
            $sizes = isset($_POST['tmpa_model_sizes']) && is_array($_POST['tmpa_model_sizes']) ? $_POST['tmpa_model_sizes'] : [];
            $default = isset($_POST['tmpa_model_sizes_default']) ? $_POST['tmpa_model_sizes_default'] : null;
            
            // Clean and structure data
            $cleaned = [];

            // Loop through submitted sizes and sanitize
            foreach ($sizes as $i => $row) {
                $cleaned[] = [
                    'label' => isset($row['label']) ? sanitize_text_field($row['label']) : '',
                    'dims' => isset($row['dims']) ? sanitize_text_field($row['dims']) : '',
                    'price' => isset($row['price']) ? floatval($row['price']) : 0,
                    'is_default' => (string)$i === (string)$default
                ];
            }

            // Save cleaned data to post meta
            update_post_meta($post_id, '_tmpa_model_size', $cleaned);
        }

        /**
         * Render colours dropdown based on product type (slim, solid, edge)
         * Base and metal colour dropdowns update depending on options available for current top colour
         *
         * @param \WP_Post $post
         * @return void
         */
        public static function render_default_colours_box($post) {

            // Nonce for security
            wp_nonce_field('tmpa_save_colours', 'tmpa_colours_nonce');

            // Existing values (if saved)
            $saved_top   = get_post_meta($post->ID, '_tmpa_top_colour', true);
            $saved_base  = get_post_meta($post->ID, '_tmpa_base_colour', true);
            $saved_metal = get_post_meta($post->ID, '_tmpa_metal_colour', true);
            
            // Get product object            
            $product = function_exists('wc_get_product') ? wc_get_product($post->ID) : null;

            if (!$product) {
                return;
            }
            // Determine product type for fetching relevant colour options
            $product_type = self::get_product_type($product);

            // Determine base type (wood/tile)
            $baseType = has_term(199, 'product_cat', $post->ID) ? 'wood' : 'tile';

            // Get colours transient and extract data based on product type (solid, slim/edge)
            $colours_transient = get_transient('tm3d_colour_options_all');
            $availableColours = $colours_transient[$product_type]['colour_options'] ?? [];

            if(!empty($availableColours)) : ?>
            <h3>Select default colours for: <?php echo esc_html($product->get_name()); ?></h3>
             <select id="top-colour" name="tmpa_top_colour">
                <option value="">Select a colour</option>
                <?php foreach ($availableColours as $topColour) : ?>
                    <option value="<?php echo esc_attr($topColour['top']['name']); ?>">
                        <?php echo esc_html(ucwords($topColour['top']['name'])); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="base-colour" name="tmpa_base_colour" class="tmpa-hide">
                <option value="">Select base</option>
            </select>

            <select id="metal-colour" name="tmpa_metal_colour" class="tmpa-hide">
                <option value="">Select metal</option>
            </select>

            <script>
                window.TMPA_COLOURS = <?php echo wp_json_encode($availableColours); ?>;
                window.TMPA_SAVED = {
                    top: "<?php echo esc_js($saved_top); ?>",
                    base: "<?php echo esc_js($saved_base); ?>",
                    metal: "<?php echo esc_js($saved_metal); ?>"
                };
                window.TMPA_BASE_TYPE = "<?php echo esc_js($baseType); ?>";
            </script>

            <?php endif;
            
        }

        /**
         * Save default colour selections to wp_postmeta when product is saved
         *
         * @param int $post_id The ID of the post being saved
         * @return void
         */
        public static function save_default_colours($post_id) {

            // Security checks
            if (!isset($_POST['tmpa_colours_nonce']) ||
                !wp_verify_nonce($_POST['tmpa_colours_nonce'], 'tmpa_save_colours')) {
                return;
            }

            // Avoid autosave overwrite
            if (self::is_autosave()) {
                return;
            }

            // Check permissions
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }

            // Save fields
            $fields = [
                '_tmpa_top_colour'   => 'tmpa_top_colour',
                '_tmpa_base_colour'  => 'tmpa_base_colour',
                '_tmpa_metal_colour' => 'tmpa_metal_colour',
            ];

            // Loop through fields and save if present in POST data
            foreach ($fields as $meta_key => $post_key) {
                if (isset($_POST[$post_key])) {
                    update_post_meta($post_id, $meta_key, sanitize_text_field(strtolower($_POST[$post_key])));
                }
            }
        }

        /**
         * Determine product type from WP categories
         *
         * @param object $product
         * @return string|null Returns 'solid', 'slim', 'edge' or null if no match
         */
        public static function get_product_type($product) {

            // Guard against invalid product
            if (!$product || !is_object($product) || !method_exists($product, 'get_id')) {
                return null;
            }

            // Get product category slugs
            $terms = get_the_terms($product->get_id(), 'product_cat');

            if (empty($terms) || is_wp_error($terms)) {
                return null;
            }

            // Define slugs of types to check
            $slugs = ['solid', 'slim', 'edge'];

            // Return the slug of the first matching category (ensure term_id is cast to int for comparison)
            foreach($terms as $term) {
                if (in_array($term->slug, $slugs)) {
                    return $term->slug; 
                }

            }

            // Return null if no matching category found
            return null; 

        }

    }