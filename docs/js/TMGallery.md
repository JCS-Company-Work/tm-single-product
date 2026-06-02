# TailormadeGallery class

**Purpose:**
Initialize and manage the product image lightbox using PhotoSwipe, with dynamic module loading to keep initial page weight low.

## Lifecycle

### 1. Initialisation
- Runs on `DOMContentLoaded`.
- Finds gallery containers (`.tm-gallery`, `.tm-gallery-grid`).
- Dynamically imports PhotoSwipe modules.
- Creates and initializes one lightbox instance per gallery container.

---

### 2. User Interaction
- Clicking a gallery image opens the lightbox viewer.
- User can navigate images inside the lightbox.
- Module-level handling keeps gallery behavior isolated from product option logic.

---

### 3. API Request
- No backend API calls are made by this class.
- Uses local JS module imports only:
	- `photoswipe.esm.min.js`
	- `photoswipe-lightbox.esm.min.js`

### 4. Lifecycle summary

*** Initialization: ***
- Build gallery instances after DOM is ready.
- Load PhotoSwipe only when needed.

*** Runtime: ***
- Lightbox handles image open/close/navigation.
- Failures in module loading are logged to console.