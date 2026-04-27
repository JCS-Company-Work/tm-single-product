# TMPC_CartUI class

**Purpose:**  
Update the WooCommerce mini-cart and cart fragments in the site header to reflect the latest cart contents and item count after AJAX actions.

## Expected Behavior
- Updates the mini-cart and cart item count in the header when products are added via AJAX.

## Key Hooks & Methods
- `woocommerce_add_to_cart_fragments`: Update cart fragments for AJAX.

## Example
```php
$fragments['#site-header-cart'] = ...; // Updated mini-cart HTML
$fragments['.header-items-count'] = ...; // Updated item count
```
