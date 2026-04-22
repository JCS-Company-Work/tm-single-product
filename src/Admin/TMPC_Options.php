<?php

    namespace TMProductConfigurator\Admin;

    class TMPC_Options {

        /**
         * Initialize options page
         */
        public static function init() {
            
            add_action('admin_menu', [self::class, 'add_options_page']);

        }

        /**
         * Add options page under main plugin menu
         */
        public static function add_options_page() {
            add_menu_page(
                'TMPC Model Sizes',
                'Model Sizes',
                'manage_options',
                'tmpc-model-sizes',
                [self::class, 'render_options_page'],
                'dashicons-editor-table',
                56
            );
        }

        /**
         * Render options page content
         */
        public static function render_options_page() {

            // Set option name
            $option_name = 'tmpc_model_default_sizes';
            
            // Default sizes to populate options page with if no saved options exist
            $sizes = get_option($option_name, [
                [ 'label' => '200cm', 'dims' => '200cm L x 105cm W x 77cm H', 'price' => 0 ],
                [ 'label' => '220cm', 'dims' => '220cm L x 105cm W x 77cm H', 'price' => 150 ],
                [ 'label' => '250cm', 'dims' => '250cm L x 120cm W x 77cm H', 'price' => 20 ],
                [ 'label' => '300cm', 'dims' => '300cm L x 130cm W x 77cm H', 'price' => 20 ],
            ]);

            // Ensure sizes is an array to prevent errors
            if (!is_array($sizes)) $sizes = [];

            // Handle form submission to save sizes
            if (isset($_POST['tmpc_save_sizes']) && check_admin_referer('tmpc_save_sizes_action', 'tmpc_save_sizes_nonce')) {
                
                // Process and sanitize submitted sizes data
                $new_sizes = [];
                
                // Check if size_label is set and is an array before processing
                if (isset($_POST['size_label']) && is_array($_POST['size_label'])) {
                
                    // Loop through submitted size labels and construct new sizes array
                    foreach ($_POST['size_label'] as $i => $label) {

                        // Only add size if label is not empty
                        if (empty($label)) continue;
                        $new_sizes[] = [
                            'label' => sanitize_text_field($label),
                            'dims' => sanitize_text_field($_POST['size_dims'][$i] ?? ''),
                            'price' => floatval($_POST['size_price'][$i] ?? 0),
                        ];
                    }
                }

                // Update option with new sizes
                update_option($option_name, $new_sizes);
                
                // Update local variable to reflect saved changes immediately
                $sizes = $new_sizes;
                
                // Display success message
                echo '<div class="updated notice"><p>Model sizes updated.</p></div>';
            }

            ?>
            <div class="wrap">
                <h1>Model Sizes Options</h1>
                <form method="post">
                    <?php wp_nonce_field('tmpc_save_sizes_action', 'tmpc_save_sizes_nonce'); ?>
                    <table class="widefat">
                        <thead>
                            <tr><th>Label</th><th>Dimensions</th><th>Price</th><th>Remove</th></tr>
                        </thead>
                        <tbody id="tmpc-sizes-tbody">
                        <?php foreach ($sizes as $i => $size) : ?>
                            <tr>
                                <td><input type="text" name="size_label[]" value="<?php echo esc_attr($size['label']); ?>" /></td>
                                <td><input type="text" name="size_dims[]" value="<?php echo esc_attr($size['dims']); ?>" /></td>
                                <td><input type="number" name="size_price[]" value="<?php echo esc_attr($size['price']); ?>" step="1" /></td>
                                <td><button type="button" class="button tmpc-remove-size">Remove</button></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="button" class="button" id="tmpc-add-size">Add Size</button>
                    <input type="submit" class="button button-primary" name="tmpc_save_sizes" value="Save Sizes" />
                </form>
            </div>
            <script>
            (function($){
                $(document).on('click', '.tmpc-remove-size', function(){
                    $(this).closest('tr').remove();
                });
                $(document).on('click', '#tmpc-add-size', function(e){
                    e.preventDefault();
                    $('#tmpc-sizes-tbody').append('<tr><td><input type="text" name="size_label[]" /></td><td><input type="text" name="size_dims[]" /></td><td><input type="number" name="size_price[]" step="1" /></td><td><button type="button" class="button tmpc-remove-size">Remove</button></td></tr>');
                });
            })(jQuery);
            </script>
            <?php
        }


    }