# TMPC_CartPrice class

**Purpose:**  
Set custom unit prices for configured products so WooCommerce can calculate taxes and totals from that value.

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
