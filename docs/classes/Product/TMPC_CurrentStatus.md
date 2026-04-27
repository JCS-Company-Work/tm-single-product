# TMPC_CurrentStatus class

**Purpose:**  
Render and manage the "Current Status" section on the WooCommerce product page, displaying the user's current configuration (selected options, preview image, price, QR code, etc.) in real time.

## Expected Behavior
- Injects a "Current Status" UI block after the add-to-cart button on the product page.
- Shows selected options (top, base, metal colours), preview image, configured price, model, and QR code.
- Updates dynamically as the user interacts with the configurator.

## Key Hooks & Methods
- `woocommerce_after_add_to_cart_button`: Adds the status section to the product page.
- `add_current_status()`: Renders the status UI, including preview image and all selected options.

## Example Output
- Title and price for the configured product
- Preview image and QR code
- Selected model, dimensions, and specification text
- Layered colour breakdown (top, base, metal)
