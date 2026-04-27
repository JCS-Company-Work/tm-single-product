# TMPC_Options class

**Purpose:**  
Manage custom admin fields for setting default colour options (Top, Base, Metal) on a per-product basis in the WooCommerce backend, these top level admin options serve as the default (based on product type) until options are saved for a specific product which then take precedence.

## Expected Behavior
- When editing a product in the admin, a “Default Colours” meta box appears.
- The Top Colour dropdown is shown first, with options filtered by product type (slim, edge, solid).
- When a Top Colour is selected the relevant Base and Metal dropdowns appear (if applicable), populated with options corresponding to the selected Top Colour.
- Selections persist after saving the product and are restored on subsequent visits from the postmeta table.

## Data Structure
Data is grouped by product type, mapping top colours to available base/metal options:
```php
'Arabescato New' => [
    'base' => ['cobolo', 'jet black'],
    // ...other keys
]
```

## Custom Field Creation
- On page load, `add_colour_dropdowns()` is hooked to `add_meta_boxes` to add the meta box to the product admin.
- The `render_default_colours_box()` callback renders the dropdown fields.

## Colour Selection and Update
- `render_default_colours_box()`:
  - Generates a nonce for secure saving.
  - Loads existing selections from the database to pre-populate dropdowns.
  - Fetches available colour options via:
    ```php
    TMPC_ColourOptions::getAdminColourOptions();
    ```
  - Filters options by product type using `get_product_type()`, which checks category IDs and returns the type slug.
  - Passes filtered options to JS:
    ```js
    window.TMPC_COLOURS = <?php echo wp_json_encode($availableColours); ?>;
    ```
  - On `DOMContentLoaded`, JS:
    - Loads the colour data.
    - Attaches a change listener to the Top Colour dropdown.
    - Uses helper functions (`capitaliseWords`, `populate`, `updateOptions`) to update dropdowns and format labels.

## Saving Selections
- Selections are saved via the standard post save button.
- `save_default_colours()` is hooked to `save_post` and updates post meta for each selected value.
