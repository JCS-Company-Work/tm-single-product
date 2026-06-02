# TMPC_AJAX class

**Purpose:**  
Handle AJAX add-to-cart flows for configured products and swatches, and persist custom cart/order metadata.

## Expected Behavior
- AJAX endpoints allow products and swatches to be added to the cart from the frontend, including custom configuration data (colours, model, image, etc.).
- When a product is added, the mini cart and cart count are updated live via returned fragments.
- Swatch products can be added with a special note, and this note is shown in the cart, checkout, and saved to the order.
- All AJAX handlers work for both logged-in and guest users.

## Data Structure
Custom data is attached to cart items as associative arrays, e.g.:
```php
[
    'top_colour' => 'Arabescato New',
    'base' => 'cobolo',
    'model' => '250cm',
    'custom_image' => 'https://example.com/image.jpg',
    'metal_edge_veneer' => 'Brushed Gold',
    'swatch_note' => 'Refunded with furniture purchase'
]
```

## AJAX Handler Registration
- On plugin init, `TMPC_AJAX::init()` registers AJAX actions for:
  - `tm_add_to_cart` (for configured products)
  - `add_swatch_to_cart` (for swatch products)
- Both logged-in (`wp_ajax_`) and guest (`wp_ajax_nopriv_`) actions are registered.

## Adding Products to Cart
- `ajax_add_product_to_cart()`:
  - Validates product and quantity.
  - Sanitizes and maps custom fields from `$_POST`.
  - Adds the product to the cart with custom data.
  - Returns updated mini cart HTML and cart count fragments for live UI updates.

## Adding Swatches to Cart
- `ajax_add_swatch_to_cart()`:
  - Adds a swatch product to the cart with a hardcoded note.
  - Returns updated mini cart HTML and cart count fragments.

## Cart and Order Data Persistence
- `tm_add_swatch_note_to_cart_item()`:
  - Displays the swatch note in the cart and checkout.
- `tm_add_swatch_note_to_order_item()`:
  - Saves the swatch note as order item meta.
