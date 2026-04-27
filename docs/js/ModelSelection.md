# TMPC_ModelSelection class

*** Purpose ***
The class initializes, determines the selected model, updates the DOM to reflect the selection, and keeps the UI in sync with model changes.

## Lifecycle

### 1. Initialisation


---

### 2. User Interaction


---

### 3. API Request


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