# TMPC_ColourOptions class

**Purpose:**  
Purpose of class is to function as the service between our REST routes, the frontend (getColurOptions) and admin area (getAdminColourOptions), checking for existing cached data, serving that if it exists, otheriwse triggering data fetch from Google Sheets.

All class methods are static.

## Expected Behavior
- Request to /colour-options is routed to getColourOptions() which checks to see if cached data exists at 'tmpc_sheets_data' via get_transient and serves it is exists with rest_ensure_response(), triggers data update via TMPC_ColourOptionsData::getDataFromGoogleSheets(true) if not. Boolean true is bypass token as this is a direct internal request so security key not required.
- Request to /colour-options-update routed to getAdminColourOptions() which works exaclt the same as the above except transient key is 'tmpc_admin_sheets_data' and returned data does not use rest_ensure_response().
- get_product_type() method added here at source as was required by mulitple external classes (TMPC_Admin etc) and our WC content-single-product template in order to render selection swatches in config drawers.