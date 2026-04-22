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

            // Add colour dropdowns for product configuration settings
            add_action('add_meta_boxes', [self::class, 'add_colour_dropdowns']);

            // Save meta box data when product is saved
            add_action('save_post', [self::class, 'save_default_colours']);

            // Add dropdown for model sizes
            add_action('add_meta_boxes', [self::class, 'add_model_size_dropdown']);

            // Save model size when product is saved
            add_action('save_post', [self::class, 'save_model_size']);

        }

        /**
         * Check if the current save action is an autosave
         *
         * @return boolean
         */
        protected static function is_autosave() {
            $autosave = defined('DOING_AUTOSAVE') && DOING_AUTOSAVE;
            // Allow tests to override autosave check via filter
            return apply_filters('tmpc_is_autosave', $autosave);
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
         * Add model size dropdown
         *
         * @return void
         */
        public static function add_model_size_dropdown() {

            add_meta_box(
                'tmpc_model_size',
                '3D Model Size',
                [ '\\TMProductConfigurator\\Admin\\TMPC_Admin', 'render_model_size_box' ],
                'product',
                'normal'
            );
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

            // Get default sizes for dropdown options
            $default_sizes = get_option('tmpc_model_default_sizes', []);

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
                        <td><input type="number" name="tmpc_model_sizes[<?php echo $i; ?>][price]" value="<?php echo esc_attr($size['price']); ?>" class="tmpc-size-price" readonly /></td>
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
            <script>
            (function($){
                let rowIdx = <?php echo count($sizes); ?>;
                
                // Inline PHP array as JS object
                const sizeData = <?php echo json_encode($default_sizes); ?>;
                const sizeOptions = sizeData.map(s => s.label);
                
                // Add new size row with dropdown for existing size options and auto-fill dims/price based on selection
                $(document).on('click', '#tmpc-add-size', function(e){

                    // Prevent default button action
                    e.preventDefault();

                    // Build options for size label dropdown
                    let opts = '<option value="">Select size</option>';
                    sizeOptions.forEach(function(label){
                        opts += `<option value="${label}">${label}</option>`;
                    });

                    // Append new row with dropdown and readonly fields for dims/price that auto-populate based on selected label
                    let newRow = `<tr>
                        <td><select name="tmpc_model_sizes[${rowIdx}][label]" class="tmpc-size-label">${opts}</select></td>
                        <td><input type="text" name="tmpc_model_sizes[${rowIdx}][dims]" class="tmpc-size-dims" readonly /></td>
                        <td><input type="number" name="tmpc_model_sizes[${rowIdx}][price]" class="tmpc-size-price" readonly /></td>
                        <td>
                            <label class="tmpc-toggle-switch">
                                <input type="radio" name="tmpc_model_sizes_default" value="${rowIdx}" />
                                <span class="tmpc-slider"></span>
                            </label>
                        </td>
                        <td><button type="button" class="button tmpc-remove-size">Remove</button></td>
                    </tr>`;

                    // Append new row to table
                    $('#tmpc-model-sizes-table tbody').append(newRow);

                    // Increment index for next row
                    rowIdx++;
                });

                // Remove size row
                $(document).on('click', '.tmpc-remove-size', function(){
                    $(this).closest('tr').remove();
                });

                // Auto-fill dims and price based on selected label
                $(document).on('change', '.tmpc-size-label', function(){
                    let label = $(this).val();
                    let $row = $(this).closest('tr');
                    let found = sizeData.find(s => s.label === label);
                    if(found) {
                        $row.find('.tmpc-size-dims').val(found.dims);
                        $row.find('.tmpc-size-price').val(found.price);
                    } else {
                        $row.find('.tmpc-size-dims').val('');
                        $row.find('.tmpc-size-price').val('');
                    }
                });
                // Only one radio can be checked
                $(document).on('change', 'input[type=radio][name=tmpc_model_sizes_default]', function(){
                    $('input[type=radio][name=tmpc_model_sizes_default]').not(this).prop('checked', false);
                });
            })(jQuery);
            </script>

            <style>
                /* Styling for table and toggle switch */
                #tmpc-model-sizes-table select, #tmpc-model-sizes-table input[type="text"], #tmpc-model-sizes-table input[type="number"] { width: 100%; }
                #tmpc-model-sizes-table th, #tmpc-model-sizes-table td { text-align: left; }
                .tmpc-toggle-switch {
                    position: relative;
                    display: inline-block;
                    width: 40px;
                    height: 22px;
                }
                .tmpc-toggle-switch input[type="radio"] {
                    opacity: 0;
                    width: 0;
                    height: 0;
                }
                .tmpc-slider {
                    position: absolute;
                    cursor: pointer;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background-color: #ccc;
                    transition: .4s;
                    border-radius: 22px;
                }
                .tmpc-toggle-switch input[type="radio"]:checked + .tmpc-slider {
                    background-color: #2196F3;
                }
                .tmpc-slider:before {
                    position: absolute;
                    content: "";
                    height: 16px;
                    width: 16px;
                    left: 3px;
                    bottom: 3px;
                    background-color: white;
                    transition: .4s;
                    border-radius: 50%;
                }
                .tmpc-toggle-switch input[type="radio"]:checked + .tmpc-slider:before {
                    transform: translateX(18px);
                }
            </style>
            
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

                document.addEventListener('DOMContentLoaded', () => {

                    const data = window.TMPC_COLOURS;
                    const saved = window.TMPC_SAVED;

                    const topSelect   = document.getElementById('top-colour');
                    const baseSelect  = document.getElementById('base-colour');
                    const metalSelect = document.getElementById('metal-colour');

                    const capitaliseWords = str =>
                        str.replace(/\b\w/g, char => char.toUpperCase());

                    // Helper to normalize top colour key (lowercase, spaces to underscores)
                    const normaliseTopKey = str => str.toLowerCase().replace(/\s+/g, '_');

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

                        if (!selectedTop) return;
                        // Normalize selectedTop to match data keys
                        const key = normaliseTopKey(selectedTop);
                        if (!data[key]) return;

                        const config = data[key];

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