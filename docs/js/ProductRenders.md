# TMPC_ProductRenders class

*** Purpose ***
The class initializes the 3D viewer, loads and renders the model, and keeps the scene and UI in sync with user selections and interactions.

## Lifecycle

### 1. Initialisation


---

### 2. User Interaction


---

### 3. API Request


### 4. Lifecycle summary

*** Initialization: ***

- On DOMContentLoaded, a new ProductRenders instance is created for the 3D viewer container.
- The constructor sets up properties, determines model and texture names, and binds event handlers.

*** Setup (init): ***

- Preloads required textures.
- Initializes the Three.js scene, renderer, camera, lights, controls, ground, and shadow map viewers.
- Sets up event listeners for UI and window resize.
- Loads the initial 3D model and starts the animation loop.
- Fades out the loading screen.

*** Event Handling: ***

- Listens for swatch and model selection changes, updating the model and URL accordingly.
- Listens for custom colour option events to update the model’s appearance.
- Handles fullscreen toggling and synchronizes the QR code with the current configuration.

*** Dynamic Updates: ***

- When options change, updates the query string, reloads the model, and updates the browser URL and QR code.
- Handles camera animation and zoom controls for user interaction.

*** Rendering: ***

- Continuously renders the scene and updates controls in the animation loop.