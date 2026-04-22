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
                'slim/edge' => [
                    [ 'label' => '200cm', 'dims' => '200cm L x 105cm W x 77cm H'],
                    [ 'label' => '220cm', 'dims' => '220cm L x 105cm W x 77cm H'],
                    [ 'label' => '250cm', 'dims' => '250cm L x 120cm W x 77cm H'],
                    [ 'label' => '300cm', 'dims' => '300cm L x 130cm W x 77cm H'],
                ],

                'solid' => [
                    [ 'label' => '200cm', 'dims' => '200cm L x 105cm W x 77.5cm H'],
                    [ 'label' => '220cm', 'dims' => '220cm L x 105cm W x 77.5cm H'],
                    [ 'label' => '250cm', 'dims' => '250cm L x 120cm W x 77.5cm H'],
                    [ 'label' => '300cm', 'dims' => '300cm L x 130cm W x 77.5cm H'],
                ],
            ]);

            // Ensure sizes is an array to prevent errors
            if (!is_array($sizes)) {
                $sizes = [];
            }

            // Handle form submission to save sizes
            if (
                isset($_POST['tmpc_save_sizes']) &&
                check_admin_referer('tmpc_save_sizes_action', 'tmpc_save_sizes_nonce')
            ) {

                // Process and sanitize submitted grouped sizes data
                $new_sizes = [];

                // Check if grouped sizes exist before processing
                if (isset($_POST['sizes']) && is_array($_POST['sizes'])) {

                    // Loop through each size group (example: slim/edge, solid)
                    foreach ($_POST['sizes'] as $group_key => $group_sizes) {

                        // Ensure group is valid
                        if (!is_array($group_sizes)) {
                            continue;
                        }

                        // Loop through sizes inside each group
                        foreach ($group_sizes as $size) {

                            // Only add size if label is not empty
                            if (empty($size['label'])) {
                                continue;
                            }

                            $new_sizes[$group_key][] = [
                                'label' => sanitize_text_field($size['label']),
                                'dims'  => sanitize_text_field($size['dims'] ?? ''),
                            ];
                        }
                    }
                }

                // Update option with new grouped sizes
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

                    <?php foreach ($sizes as $group_key => $group) : ?>

                        <h2><?php echo esc_html(ucwords($group_key)); ?></h2>

                        <table class="widefat">
                            <thead>
                                <tr>
                                    <th>Label</th>
                                    <th>Dimensions</th>
                                    <th>Remove</th>
                                </tr>
                            </thead>

                            <tbody id="tmpc-sizes-<?php echo esc_attr(sanitize_title($group_key)); ?>">

                                <?php foreach ($group as $index => $size) : ?>

                                    <tr>
                                        <td>
                                            <input
                                                type="text"
                                                name="sizes[<?php echo esc_attr($group_key); ?>][<?php echo esc_attr($index); ?>][label]"
                                                value="<?php echo esc_attr($size['label'] ?? ''); ?>"
                                            />
                                        </td>

                                        <td>
                                            <input
                                                type="text"
                                                name="sizes[<?php echo esc_attr($group_key); ?>][<?php echo esc_attr($index); ?>][dims]"
                                                value="<?php echo esc_attr($size['dims'] ?? ''); ?>"
                                            />
                                        </td>

                                        <td>
                                            <button type="button" class="button tmpc-remove-size">
                                                Remove
                                            </button>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>

                            </tbody>
                        </table>

                        <p>
                            <button
                                type="button"
                                class="button tmpc-add-size"
                                data-group="<?php echo esc_attr($group_key); ?>"
                                data-target="tmpc-sizes-<?php echo esc_attr(sanitize_title($group_key)); ?>"
                            >
                                Add Size to <?php echo esc_html($group_key); ?>
                            </button>
                        </p>

                    <?php endforeach; ?>

                    <p>
                        <input
                            type="submit"
                            class="button button-primary"
                            name="tmpc_save_sizes"
                            value="Save Sizes"
                        />
                    </p>
                </form>
            </div>

            <script>
            (function($){

                // Remove size row
                $(document).on('click', '.tmpc-remove-size', function(){
                    $(this).closest('tr').remove();
                });

                // Add new size row to correct group
                $(document).on('click', '.tmpc-add-size', function(e){
                    e.preventDefault();

                    let group = $(this).data('group');
                    let target = $('#' + $(this).data('target'));
                    let index = target.find('tr').length;

                    let row = `
                        <tr>
                            <td>
                                <input type="text" name="sizes[${group}][${index}][label]" />
                            </td>
                            <td>
                                <input type="text" name="sizes[${group}][${index}][dims]" />
                            </td>
                            <td>
                                <button type="button" class="button tmpc-remove-size">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    `;

                    target.append(row);
                });

            })(jQuery);
            </script>
            <?php
        }


    }