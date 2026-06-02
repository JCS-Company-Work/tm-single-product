# TMPC_CurrentStatus class

**Purpose:**
Keep the on-page status panel in sync with the active product configuration (price, dimensions, specification text, image, and QR code).


## Lifecycle

### 1. Initialisation

- Instantiated on `DOMContentLoaded`.
- Resolves current model and selected swatches from DOM.
- Binds listeners for model/swatch changes.
- Performs first render of status fields and QR code.

---

### 2. User Interaction

- On model or swatch updates, recalculates the summary values.
- Refreshes dimensions/spec text and image references.
- Regenerates QR code for the current configuration URL.


---

### 3. API Request

- No direct data fetch in this class.
- Consumes current state from DOM values and shared frontend data.


### 4. Lifecycle summary

*** Initialization: ***

- On DOMContentLoaded, a singleton CurrentStatus instance is created.
- The constructor sets up references and calls init().

*** Setup (init): ***

- Determines the current model.
- Adds listeners for model dropdown and swatch changes.
- Updates price, dimensions, specification text, and generates QR code.

*** Event Handling: ***

- Listeners on the model dropdown and swatch inputs trigger updates to the status recap (price, dimensions, spec, image, QR code) when selections change.

*** State Management: ***

- Tracks the selected model and swatch options.
- Gathers selected options dynamically from the DOM.

*** UI Updates: ***

- Writes latest values into current status fields.
- Updates QR image source and supporting labels.

- Updates the status image, price, dimensions, and specification text based on current selections.
- Generates and synchronizes a QR code reflecting the current configuration.