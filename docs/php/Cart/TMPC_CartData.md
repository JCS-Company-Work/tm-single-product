# TMPC_CartData class

**Purpose:**  
Manage the storage, display, and persistence of custom product configuration data (colours, model, metal, image, totals, etc.) in WooCommerce cart items and orders.

## Expected Behavior
- Adds custom data (top_colour, base, model, metal_edge_veneer, metal_edge_checkbox, custom URL, totals, etc.) to cart items when products are added.
- Displays custom data in the cart and checkout (with labels).
- Updates cart item names/URLs if a custom URL is set.
- Applies configured totals to cart items before calculation.
- Saves all custom meta to order line items on checkout (using programmatic keys).

## Key Hooks & Methods
- `woocommerce_add_cart_item_data`: Store custom data in cart (`tm_add_custom_product_values_to_cart`).
- `woocommerce_get_item_data`: Display custom data in cart/checkout (`tm_display_custom_product_values_cart`).
- `woocommerce_cart_item_name`: Update cart item names/URLs (`tm_update_cart_urls`).
- `woocommerce_before_calculate_totals`: Set custom prices (`tm_apply_configured_total`).
- `woocommerce_checkout_create_order_line_item`: Save meta to order (`tm_save_meta_to_order`).

## Implementation Notes
- Accepts POST fields: top_colour, base, model, metal_edge_veneer, metal_edge_checkbox, _tm_custom_product_url, configured_total, options_total.
- Displays fields in cart/checkout: top_colour, base, model, metal_edge_veneer, metal_edge_checkbox, note, options_total.
- Applies `configured_total` as the cart item price if set and > 0.
- Saves all meta fields to order line items using their programmatic keys.

## Example Data
```php
[
  'top_colour' => 'Arabescato New',
  'base' => 'cobolo',
  'model' => '250cm',
  'metal_edge_veneer' => 'Brass',
  'metal_edge_checkbox' => 'Yes',
  '_tm_custom_product_url' => 'https://example.com/configured-product',
  'configured_total' => '123.45',
  'options_total' => '10.00',
  'custom_image' => 'data:image/png;base64,...'
]
```
