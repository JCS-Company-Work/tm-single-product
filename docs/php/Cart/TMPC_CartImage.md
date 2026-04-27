# TMPC_CartImage class

**Purpose:**  
Generate and display custom Base64-encoded thumbnails for configured products in the WooCommerce cart and mini-cart.

## Expected Behavior
- Converts image URLs to Base64 thumbnails for use in the cart and emails.
- Displays custom thumbnails in the cart and mini-cart.
- Allows data URLs for images in cart output.

## Key Hooks & Methods
- `woocommerce_cart_item_thumbnail` / `woocommerce_mini_cart_item_thumbnail`: Display custom thumbnail.
- `tm_get_custom_base64_thumbnail($image_url, $max_width)`: Generate Base64 thumbnail.
- `kses_allowed_protocols`: Allow `data:` URLs for images.

## Example
```php
$base64 = TMPC_CartImage::tm_get_custom_base64_thumbnail($image_url, 150);
```
