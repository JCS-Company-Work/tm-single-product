# TMPC_Assets class

**Purpose:**  
Manage the registration, enqueueing, and optimization of all frontend scripts and styles for the TM Product Configurator plugin, ensuring efficient loading and compatibility across WooCommerce product pages.

## Expected Behavior
- Enqueues all required JS and CSS assets on product pages, including:
  - AJAX handlers for cart actions
  - 3D renderer and supporting libraries
  - Colour options, model selection, and status recap classes
  - QR code and PDF generation scripts
  - Enhanced dropdowns (Select2)
- Passes dynamic data (e.g., product ID, plugin URL) to JS via `wp_localize_script`.
- Ensures scripts are loaded only where needed (e.g., not on swatch product pages for certain assets).
- Optimizes asset loading by deferring non-critical scripts and styles, and converting compatible scripts to modules for better performance.

## Asset Registration & Enqueueing
- `enqueue_frontend_assets()` is hooked to `wp_enqueue_scripts` and:
  - Enqueues scripts and styles based on page context (product, swatch, etc.).
  - Localizes data for use in JS (e.g., `TMPCPlugin`).
  - Ensures Select2 is loaded for enhanced dropdowns.

## Script Optimization
- `deferScripts()`:
  - Adds `defer` attribute to selected non-critical scripts to improve page load performance.
- `scriptLoader()`:
  - Adds `defer` or `type="module"` to specific scripts for modern JS support and async loading.
- `styleLoader()`:
  - Defers non-critical CSS by using the `media="print"` trick, switching to `all` on load.

## Example Data Passed to JS
```js
TMPCPlugin = {
    url: 'https://example.com/wp-content/plugins/tm-product-configurator/',
    product_id: 1234
}
```

## Summary
The TMPC_Assets class ensures all plugin assets are loaded efficiently and only when needed, improving both user experience and site performance on WooCommerce product pages.