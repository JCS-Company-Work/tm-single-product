# Product.js (Colour Options Logic)

**Purpose:**
Control colour/base/metal availability, apply valid defaults, and keep image/status layers in sync with current selection.

## Lifecycle

### 1. Initialisation
- Instantiated on `DOMContentLoaded`.
- Fetches available options from `/wp-json/tmpc/v1/colour-options?product_id=...`.
- Sets initial top colour from checked swatch and applies valid options.
- Adds listeners for top/base/metal swatch changes.

---

### 2. User Interaction
- Top colour change:
  - Rebuilds available base/metal options.
  - Re-selects valid defaults when needed.
  - Dispatches `colourOptionsChanged`.
  - Updates status layers and composite images.
- Base/metal change:
  - Updates selected state.
  - Updates status layers and composite images.

---

### 3. API Request
- Sends POST to `/wp-json/tmpc/v1/update-product-images/` with selected layers and product id.
- Uses returned image URLs to update status and gallery images.

### 4. Lifecycle summary

*** Initialization: ***
- Load option data.
- Set initial state from current checked values.

*** Option Logic: ***
- `setColourOptions()` maps top colour to valid base/metal lists.
- `setSelectedOptions()` captures current selections.
- `setDefaults()` dispatches current defaults to other modules.

*** UI Updates: ***
- `showHideOptions()` toggles swatch visibility by availability.
- `updateStatusLayer()` and `updateCompositeImages()` refresh live preview assets.