# TMPC_ColourOptionsService class

**Purpose:**  
Bridge REST route callbacks and colour option data storage.
Serve cached transients when available, otherwise trigger Google Sheets refresh.

All class methods are static.

## Expected Behavior
- `getColourOptions()`:
	- Reads `tmpc_sheets_data`.
	- Returns cached data with `rest_ensure_response()` when available.
	- Calls `TMPC_ColourOptionsData::getDataFromGoogleSheets(true)` when cache is missing.
- `getAdminColourOptions()`:
	- Reads admin cache (`tmpc_sheets_admin_data`).
	- Triggers refresh if needed.
- `get_product_type()`:
	- Shared helper used by frontend templates and admin classes to map product categories to product type (for example `slim`, `edge`, `solid`).