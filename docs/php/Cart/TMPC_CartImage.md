# TMPC_CartImage class

**Purpose:**  
Display custom image size for configured products in the WooCommerce cart and mini-cart.

## Expected Behavior
- Displays custom images (from URL or Base64) in the cart and mini-cart for configured products.
- If a custom image is set on the cart item, it replaces the default product thumbnail.
- If a custom product URL is set, the image is wrapped in a link to that URL.

## Key Hooks & Methods
- `woocommerce_cart_item_thumbnail` / `woocommerce_mini_cart_item_thumbnail`: Filters to display custom thumbnail.
- `TMPC_CartImage::set_cart_image($thumbnail, $cart_item)`: Main method to output the custom image or fallback.

## Implementation Notes
- The current implementation expects a `custom_image` key (URL or data URI) on the cart item.
- If `_tm_custom_product_url` is set, the image is wrapped in a link.
- No direct Base64 conversion is performed in this class; images are expected to be pre-encoded if needed.

## Example
```php
// In cart item data:
$cart_item['custom_image'] = 'data:image/png;base64,...' // or a URL
$cart_item['_tm_custom_product_url'] = 'https://example.com/configured-product';
// Output in cart:
add_filter('woocommerce_cart_item_thumbnail', [TMPC_CartImage::class, 'set_cart_image'], 10, 3);
```
