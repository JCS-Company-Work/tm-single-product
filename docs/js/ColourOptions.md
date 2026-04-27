# TMPC_ColourOptions class

The class initializes, fetches data, sets up UI and listeners, and dynamically manages available options and UI state as the user interacts with the configurator.

**Purpose:**  
The purpoose of this class is to manage the dynamic updating of product options available to a user based on the selected top colour in the product configurator.

Available options for each top colour are fetched from the server, changes to the top colour selection listened for, and available options for the base and edge groups updated accordingly. It also handles setting the initial state of the configurator based on URL parameters or default selections in the HTML.
 
The mapping of available options for each top colour is defined in a Google Sheet and accessed via a custom REST API endpoint.


- Global colourOptions obejct created within constructor to hold colour options from server
- Global optionToClass object created to help with mapping between API data to our DOM css classes base -> 'base', metal -> 'metal-edge-veneer'
- 

## Lifecycle

### 1. Initialisation
- New class instance created on DOMContentLoaded
- Global variables created within constrcutor and init() method triggered
- init() triggers fetching of colour data, sends GET request to:
     `/wp-json/tmpc/v1/colour-options/`
- Payload:
``` js
    {
        arabescato: 
            edge: 
                base: Array(4)
                    0: "arabescato"
                    1: "jet black"
                    2: "cobolo"
                    3: "american walnut"
                    length: 4
                    [[Prototype]]: Array(0)
                metal: Array(4)
                    0: "brushed bronze"
                    1: "brushed steel"
                    2: "brushed gold"
                    3: "satin black"
                    length: 4
                    [[Prototype]]: Array(0)
                [[Prototype]]: Object
            slim: 
                base: Array(4)
                    0: "arabescato"
                    1: "jet black"
                    2: "cobolo"
                    3: "american walnut"
                    length: 4
                    [[Prototype]]: Array(0)
                [[Prototype]]: Object
            [[Prototype]]: Object
```
- Also adds interactivity to our config drawers and adding swatch listeners for user triggered changes
- Once data received from server URL is checked for any existing colour params and if any found attempts to serve this colour and corresponding options after checking that it is valid and updates the config drawer options to reflect it.

---

### 2. User Interaction
- User selects a top/base/metal option which triggers a recheck of available options via setDefaults(). If current combination is valid, if not the invalid base and/or metal layers are updated to the first valid option for that top colour and DOM elements have 'wapf-checked' class added. 
- Options in config drawers are updated to reflect the new available options.
- Image update request triggered to update 3D model, status image and gallery images.

---

### 3. API Request
- Sends POST request to:
  `/wp-json/tmpc/v1/update-product-images/`
- Payload:
Returned urls to go here and DOM elements to be updated.

### 4. Lifecycle summary

*** Initialization: ***

- On DOMContentLoaded, a new ColourOptions instance is created.
- The constructor sets up data structures and calls init().

*** Setup (init): ***

- Fetches colour options from the server.
- Sets up the configuration drawer UI.
- Adds event listeners to swatch inputs.
*** Data Fetching: ***

- fetchColourOptions() retrieves available options and triggers initial setup.

*** Initial State: ***

- setInitalColours() determines the starting selection (from URL or DOM), sets defaults, updates UI, and triggers image layer updates.

*** Event Handling: ***

- Listeners on swatch inputs update available options and UI when selections change.

*** Option Logic: ***

- When a top colour is selected, available base/metal options are updated.
- Defaults are enforced if the current selection is not valid.

*** UI Updates: ***

- Shows/hides swatches based on availability.
- Updates status image layers to reflect current selections.

*** Utility: ***

- Helper methods extract image filenames and determine product type.