# TMPC_ModelSelection class

*** Purpose ***
Manage selected model state and keep the model class on key DOM containers in sync.

## Lifecycle

### 1. Initialisation

- Instantiated on `DOMContentLoaded`.
- Reads model from URL or selected dropdown option.
- Applies model class to `.content-area` and related UI elements.
- Adds dropdown listener.


---

### 2. User Interaction

- Dropdown change updates selected model value.
- Model class is replaced on target containers.
- Dependent modules read updated model state from DOM/classes.


---

### 3. API Request

- No direct API requests in this class.


### 4. Lifecycle summary
*** Initialization: ***

- On DOMContentLoaded, a new ModelSelection instance is created.
- The constructor selects the content area, initializes the selected model, and calls init().

*** Setup (init): ***

- Checks for a model in the URL or uses the default from the HTML.
- Adds model-related classes to specification paragraphs.
- Sets up a change listener on the model dropdown.

*** Model Selection: ***

- On page load, determines the selected model from the URL or default option.
- Updates the .content-area class to reflect the selected model.

*** Event Handling: ***

- When the model dropdown changes, updates the selected model and the .content-area class accordingly.

*** UI Updates: ***

- Adds a model-specific class to each specification paragraph for styling or identification.