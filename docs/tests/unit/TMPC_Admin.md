# TMPC_Admin tests

*** Test list ***
Below is a list of the tests in TMPC_Admin test suite and the features they test.

- test_init_registers_admin_hooks()
Verifies that admin hooks for meta boxes and saving are registered.

- test_add_colour_dropdowns_adds_meta_box()
Checks that the colour dropdown meta box is added to product admin.

- test_save_default_colours_saves_postmeta()
Ensures selected colour values are saved to post meta.

- test_save_default_colours_does_not_save_if_nonce_missing()
Ensures post meta is not saved if the nonce is missing.

- test_save_default_colours_does_not_save_if_nonce_invalid()
Ensures post meta is not saved if the nonce is invalid.

- test_save_default_colours_does_not_save_if_user_cannot_edit()
Ensures post meta is not saved if the user lacks edit permissions.

- test_save_default_colours_does_not_save_if_doing_autosave()
Ensures post meta is not saved during an autosave.

- test_save_default_colours_does_not_save_if_fields_missing()
Ensures post meta is not saved if required fields are missing.

- test_get_product_type_returns_expected_type()
Checks that the correct product type is returned based on category.

- test_get_product_type_returns_null_for_no_categories()
Ensures null is returned if the product has no categories.

- test_get_product_type_returns_null_for_unrelated_category()
Ensures null is returned if the product has unrelated categories.

- test_render_default_colours_box_outputs_dropdowns_for_valid_product_slug()
Checks that dropdowns are rendered for valid product/category slugs.

- test_render_default_colours_box_outputs_nothing_for_invalid_product_slug()
Checks that dropdowns are not rendered for invalid product/category slugs.