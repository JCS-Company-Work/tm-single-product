# TMPC_Assets

**Purpose:**
Conditionally enqueue scripts and styles for TMPC plugin.

**Key Methods/Functions:**
- init() - add actions and filters to WP lifecycle. Sets up hooks for asset loading, filters for deferring non-critical scripts and convert assets to modules.
- enqueue_frontend_assets() - conditionally enqueues all necessary JS/CSS files for the TMPC on product pages, ensuring only relevant assets are loaded (e.g., AJAX handlers, rendering scripts, colour options, and Select2 for dropdowns), and skips swatch category products where appropriate.
- deferScripts() - defers non-critical JS not needed above the fold by adding 'defer' to script tag via script_loader_tag filter hook.
- scriptLoader() -  Convert resources to modules to allow for modern JS features (import/export) and better performance where possible, while ensuring compatibility with older browsers by only doing so for scripts that can support it.
- styleLoader() - defers loading of non-critical CSS (like Select2 styles) by setting media="print" and switching it back to "all" on load, allowing the page to render faster by loading these styles asynchronously without blocking the initial render.

**Interactions:**
- None

**Typical Flow:**
- None

**Test Ideas:**
1. init() correctly registers all actions and filters (wp_enqueue_scripts, script_loader_tag, style_loader_tag).
2. enqueue_frontend_assets() enqueues the right scripts/styles on product pages, and skips/enqueues as expected for swatch/non-swatch products.
3. enqueue_frontend_assets() localizes scripts with correct data (e.g., TMPCPlugin variables).
4. deferScripts() adds defer attribute only to specified scripts.
5. scriptLoader() adds defer or module attributes only to intended scripts/handles.
6. styleLoader() defers only the specified CSS handles and leaves others unchanged.
7. No scripts/styles are enqueued or modified outside intended contexts (e.g., non-product pages).
8. Select2 is only enqueued if not already present.
9. All enqueued assets use the correct version and paths.