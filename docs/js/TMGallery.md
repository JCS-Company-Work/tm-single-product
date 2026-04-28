# TMGallery.js — Gallery Class & Module Rationale

## 1. Initialisation

- The gallery is initialized as a dedicated class (`TailormadeGallery`) when the DOM is ready.
- The class encapsulates all PhotoSwipe (lightbox) setup and logic, keeping gallery code modular and maintainable.
- ES6 module syntax is used to enable dynamic imports and scoped execution.

---

## 2. User Interaction

- Users interact with the gallery via the `.tm-gallery` markup; clicking images triggers the PhotoSwipe lightbox.
- All event handling and PhotoSwipe instance management is contained within the gallery class.

---

## 3. API/Module Rationale

### Why Use a Class for the Gallery?

- **Encapsulation:** Gallery/lightbox logic is grouped in one place, making it easier to maintain, extend, or debug. If you need to add more features (custom events, teardown, state, etc.), the class structure is ready for it.
- **Future-Proofing:** As requirements grow (e.g., custom gallery controls, dynamic updates, or integration with other modules), a class provides a scalable foundation.
- **Clear Responsibility:** The class makes it explicit that all gallery-related logic is handled here, rather than being scattered or mixed with unrelated code.

### Why Not Incorporate into Product.js?

- **Separation of Concerns:** Product.js handles product configuration, options, and UI logic. Gallery/lightbox functionality is a distinct concern and should be modular.
- **Independent Loading:** The gallery may need to be loaded only on certain pages or under certain conditions, independent of the main product logic.
- **Reduced Coupling:** Keeping gallery logic separate avoids unnecessary dependencies between product configuration and gallery display, making both easier to test and maintain.

### Why an ES6 Module?

- **Dynamic Import:** PhotoSwipe and its lightbox are imported dynamically as ES modules, which is only possible in a module context. This enables code-splitting and faster initial page loads.
- **Modern Best Practice:** ES6 modules are the standard for modern JS development, supporting better tooling, tree-shaking, and encapsulation.
- **Scoped Execution:** Using a module ensures that variables and logic do not leak into the global scope, reducing the risk of conflicts.

---

## 4. Lifecycle Summary

- The gallery class is initialized on DOM ready.
- PhotoSwipe modules are dynamically imported and the lightbox is attached to `.tm-gallery`.
- All gallery logic remains encapsulated and modular, ready for future extension.