# TMPC_Admin class

**Purpose:**  
Provide product-level admin controls for default Top, Base, and Metal colours.

## Expected Behavior
- Product editor shows a Default Colours meta box.
- Top colour options are filtered by product type (`slim`, `edge`, `solid`).
- Base and metal fields are conditionally shown based on selected top colour and available mappings.
- Saved values persist via post meta and are reloaded on edit.

## Data Structure
Admin data is grouped by product type and top colour, for example:
```php
'Arabescato New' => [
  'base' => ['cobolo', 'jet black']
]
```

## Custom Field Creation
- `add_colour_dropdowns()` is hooked to `add_meta_boxes`.
- `render_default_colours_box()` renders the actual fields.

## Colour Selection and Update
- `render_default_colours_box()`:
  - Creates nonce data for secure saves.
  - Loads existing post meta to pre-populate fields.
  - Pulls admin colour data via:
```php
TMPC_ColourOptions::getAdminColourOptions();
```
  - Filters data by product type using `get_product_type()`.
  - Exposes filtered values to JS:
```JS
window.TMPC_COLOURS = <?php echo wp_json_encode($availableColours); ?>;
```

- On `DOMContentLoaded`, admin JS updates available dropdown options when top colour changes.

## Saving selections
- `save_default_colours()` is hooked to `save_post`.
- Each selected value is saved using `update_post_meta()`.