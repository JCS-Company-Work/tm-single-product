<?php

    namespace TMProductConfigurator\Admin;

    use TMProductConfigurator\ColourOptions\TMPC_ColourOptionsService;

    /**
     * Class to handle admin functionality for the product configurator plugin
     */
    class TMPC_Admin {

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
         * @return void
         */
        public static function enqueue_admin_assets($hook) {
            
            // Only load on product edit pages
            if ($hook === 'post.php' || $hook === 'post-new.php') {
                wp_enqueue_script('tmpc-admin-js', TMPC_URL . 'assets/js/admin/admin.js', [], TMPC_VERSION, true);
                wp_enqueue_style('tmpc-admin-css', TMPC_URL . 'assets/css/admin/admin.css', [], TMPC_VERSION);
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
            return apply_filters('tmpc_is_autosave', $autosave);
        }

        /**
         * Add custom tabs for the product configurator settings to the WooCommerce product data metabox
         *
         * @param array $tabs Existing product data tabs
         * @return array Modified product data tabs
         */
        public static function add_configurator_tabs($tabs) {

            // Add custom tabs for colours
            $tabs['tmpc_colours'] = [
                'label'    => __('Select Colours', 'tm-product-configurator'),
                'target'   => 'tmpc_colours_panel',
                'class'    => [],
                'priority' => 60,
            ];

            // Add custom tab for model sizes
            $tabs['tmpc_model_size'] = [
                'label'    => __('Model Sizes', 'tm-product-configurator'),
                'target'   => 'tmpc_model_size_panel',
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
            echo '<div id="tmpc_colours_panel" class="panel woocommerce_options_panel">';
            
            // Render default colours panel
            self::render_default_colours_box($post, true);
            
            // Close first panel
            echo '</div>';
            
            //Open second panel and render model size options
            echo '<div id="tmpc_model_size_panel" class="panel woocommerce_options_panel">';
            
            // Render model size panel
            self::render_model_size_box($post, true);
            
            // Close second panel
            echo '</div>';

        }

        /**
         * Save configurator fields when product is saved
         *
         * @param  $product
         * @return void
         */
        public static function save_configurator_fields($product) {

            // Get post ID from product object
            $post_id = $product->get_id();
            
            // Save default colours
            self::save_default_colours($post_id, true);

            // Save model size options
            self::save_model_size($post_id, true);
        }

        /**
         * Add model size fields to product admin area
         *
         * @param WP_Post $post
         * @return void
         */
        public static function render_model_size_box($post) {

            // Nonce for security
            wp_nonce_field('tmpc_save_model_size', 'tmpc_model_size_nonce');

            // Existing value (if saved)
            $saved_sizes = get_post_meta($post->ID, '_tmpc_model_size', true);
            if (!is_array($saved_sizes)) {
                $saved_sizes = [];
            }

            // Get product object
            $product = function_exists('wc_get_product') ? wc_get_product($post->ID) : null;

            // Determine product type for conditional options (solid, slim, edge)
            $product_type = TMPC_ColourOptionsService::get_product_type($product);

            // Use combined options for slim and edge products
            if($product_type === 'slim' || $product_type === 'edge') {
                $product_type = 'slim/edge'; 
            }

            // Get default sizes for dropdown options
            $size_options = get_option('tmpc_model_default_sizes', []);

            //
            $default_sizes = isset($size_options[$product_type]) ? $size_options[$product_type] : [];

            // Use saved sizes if present, otherwise default
            $sizes = !empty($saved_sizes) ? $saved_sizes : $default_sizes;

            ?>
            <table id="tmpc-model-sizes-table" class="widefat">
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
                            <select name="tmpc_model_sizes[<?php echo $i; ?>][label]" class="tmpc-size-label">
                                <option value="">Select size</option>
                                <?php foreach ($default_sizes as $opt) : ?>
                                    <option value="<?php echo esc_attr($opt['label']); ?>" <?php selected($size['label'], $opt['label']); ?>><?php echo esc_html($opt['label']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="text" name="tmpc_model_sizes[<?php echo $i; ?>][dims]" value="<?php echo esc_attr($size['dims']); ?>" class="tmpc-size-dims" readonly /></td>
                        <td><input type="number" name="tmpc_model_sizes[<?php echo $i; ?>][price]" value="<?php echo esc_attr($size['price']); ?>" class="tmpc-size-price" /></td>
                        <td>
                            <label class="tmpc-toggle-switch">
                                <input type="radio" name="tmpc_model_sizes_default" value="<?php echo $i; ?>" <?php checked(!empty($size['is_default'])); ?> />
                                <span class="tmpc-slider"></span>
                            </label>
                        </td>
                        <td><button type="button" class="button tmpc-remove-size">Remove</button></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <button type="button" class="button button-primary" id="tmpc-add-size">Add Size</button>
            
            <!-- Pass model size data to JS -->
            <script>
                (function($){
                    window.TMPC_MODEL_SIZE_DATA = {
                        rowIdx: <?php echo count($sizes); ?>,
                        sizeData: <?php echo json_encode($default_sizes); ?>
                    };
                })(jQuery);
            </script>
            
            <?php
            
        }

        /**
         * Save model size selection to wp_postmeta when product is saved
         *
         * @param int $post_id The ID of the post being saved
         * @return void
         */
        public static function save_model_size() {

            // Execute security checks
            if (!isset($_POST['tmpc_model_size_nonce']) || !wp_verify_nonce($_POST['tmpc_model_size_nonce'], 'tmpc_save_model_size')) {
                return;
            }
            // Avoid autosave overwrite
            if (self::is_autosave()) {
                return;
            }

            // Check if user has permission to edit the post
            $post_id = isset($_POST['post_ID']) ? intval($_POST['post_ID']) : 0;
            if (!$post_id || !current_user_can('edit_post', $post_id)) {
                return;
            }

            // Get sizes array
            $sizes = isset($_POST['tmpc_model_sizes']) && is_array($_POST['tmpc_model_sizes']) ? $_POST['tmpc_model_sizes'] : [];
            $default = isset($_POST['tmpc_model_sizes_default']) ? $_POST['tmpc_model_sizes_default'] : null;
            
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
            update_post_meta($post_id, '_tmpc_model_size', $cleaned);
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
            
            // Get product object            
            $product = function_exists('wc_get_product') ? wc_get_product($post->ID) : null;

            if (!$product) {
                return;
            }

            // Get colour options data
            $colourOptions = TMPC_ColourOptionsService::getColourOptionsRaw('standard');

            $availableColours = $colourOptions['colour_options'];

            if(!empty($availableColours)) : ?>
             <select id="top-colour" name="tmpc_top_colour">
                <option value="">Select a colour</option>
                <?php foreach ($availableColours as $topColour) : ?>
                    <option value="<?php echo esc_attr($topColour['top']['name']); ?>">
                        <?php echo esc_html(ucwords($topColour['top']['name'])); ?>
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
            if (!isset($_POST['tmpc_colours_nonce']) ||
                !wp_verify_nonce($_POST['tmpc_colours_nonce'], 'tmpc_save_colours')) {
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
                '_tmpc_top_colour'   => 'tmpc_top_colour',
                '_tmpc_base_colour'  => 'tmpc_base_colour',
                '_tmpc_metal_colour' => 'tmpc_metal_colour',
            ];

            foreach ($fields as $meta_key => $post_key) {
                if (isset($_POST[$post_key])) {
                    update_post_meta($post_id, $meta_key, sanitize_text_field(strtolower($_POST[$post_key])));
                }
            }
        }

    }