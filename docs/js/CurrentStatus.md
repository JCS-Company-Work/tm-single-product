# TMPC_CurrentStatus class


## Lifecycle

### 1. Initialisation

---

### 2. User Interaction


---

### 3. API Request


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

- Updates the status image, price, dimensions, and specification text based on current selections.
- Generates and synchronizes a QR code reflecting the current configuration.