# TMPC_CartData class

**Purpose:**  
Manage the storage, display, and persistence of custom product configuration data (colours, images, totals, etc.) in WooCommerce cart items and orders.

## Expected Behavior
- Adds custom data (e.g., colours, model, image, totals) to cart items when products are added.
- Displays custom data in the cart and checkout.
- Updates cart item names/URLs as needed.
- Applies configured totals to cart items before calculation.
- Saves all custom meta to order line items on checkout.

## Key Hooks & Methods
- `woocommerce_add_cart_item_data`: Store custom data in cart.
- `woocommerce_get_item_data`: Display custom data in cart/checkout.
- `woocommerce_cart_item_name`: Update cart item names/URLs.
- `woocommerce_before_calculate_totals`: Set custom prices.
- `woocommerce_checkout_create_order_line_item`: Save meta to order.

## Example Data
```php
[
  'top_colour' => 'Arabescato New',
  'base' => 'cobolo',
  'model' => '250cm',
  'custom_image' => 'base64string...',
  'configured_total' => '£123.45'
]
```
