# TMPC_ProductRenders class

*** Purpose ***
Initialize and manage the 3D viewer, keep model materials in sync with UI selection, and keep URL/QR data aligned with the current configuration.

## Lifecycle

### 1. Initialisation

- Viewer start is lazy: it waits for `#obj3dviewer` to enter view using `IntersectionObserver`.
- If the browser does not support `IntersectionObserver`, viewer starts immediately.
- Initial layer values are resolved from:
	1. checked DOM swatches,
	2. URL params (`colour`, `veneer`, `base`),
	3. hardcoded defaults.
- `secondcolourname` is set from URL/DOM base swatch name for material mapping in PHP loaders.


---

### 2. User Interaction

- Reacts to `colourOptionsChanged` and updates model materials.
- Reacts to swatch and model dropdown changes.
- Updates URL query values and QR code when choices change.
- Supports fullscreen toggle and resize handling.


---

### 3. API Request

- Loads material and model via:
	- `mtl.php` with current query string.
	- `*-obj.php` matching current model texture name.
- Preloads texture assets before first scene render.


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
- Uses URL/DOM/default fallback resolution to ensure base/top/metal layers render correctly on first load.

*** Event Handling: ***

- Listens for swatch and model selection changes, updating the model and URL accordingly.
- Listens for custom colour option events to update the model’s appearance.
- Handles fullscreen toggling and synchronizes the QR code with the current configuration.

*** Dynamic Updates: ***

- When options change, updates the query string, reloads the model, and updates the browser URL and QR code.
- Handles camera animation and zoom controls for user interaction.

*** Rendering: ***

- Continuously renders the scene and updates controls in the animation loop.