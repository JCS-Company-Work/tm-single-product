# TMPC_CartPrice class

**Purpose:**  
Ensure custom ex-VAT unit prices are set for configured products in the WooCommerce cart, allowing WooCommerce to handle all tax and total calculations.

## Expected Behavior
- Sets a custom unit price for cart items based on a `grand_total` value.
- WooCommerce uses this price for all calculations (subtotal, VAT, total).

## Key Hooks & Methods
- `woocommerce_add_cart_item`: Inject custom unit price into cart item.

## Example
```php
if (!empty($cart_item['grand_total'])) {
    $unit_price = ...;
    $cart_item['data']->set_price($unit_price);
}
```
