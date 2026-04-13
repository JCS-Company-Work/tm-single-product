# TMPC_ColourOptions class

**Purpose:**  
Purpose of this class is to scaffold the colour-options (GET) and colour-options-update (POST/PATCH) endpoint at plugins_loaded and route requests to these endpoints to getColourOptions() and getDataFromGoogleSheets() in TMPC_ColourOptionsData class.

## Expected Behavior
- Routes scaffolded at plugins_loaded via register_rest_route()
- Requests to endpoints routed to corresponding callback methods in TMPC_ColourOptionsData.