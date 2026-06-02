# TMPC_ColourOptionsData class

**Purpose:**  
Fetch colour option rows from Google Sheets, format them, and store frontend/admin transients.

All class methods are static.

## Expected Behavior
- When cache is missing/invalid, service calls `getDataFromGoogleSheets()`.
- Method updates both transients:
    - `tmpc_sheets_data` (frontend)
    - `tmpc_sheets_admin_data` (admin)
- `POST /colour-options-update` also triggers this refresh path.

## Google Sheets Data Update Example
Google Sheet update action sends a POST request to `/wp-json/tmpc/v1/colour-options-update` with `HTTP_X_TMPC_TOKEN`.
The callback validates this against `$_ENV['TMPC_UPDATE_TOKEN']`.
If token check fails, it returns a `403 Invalid or missing token` response.

Credentials are read from environment values. Returned data is formatted and stored with 30-day transients for frontend and admin consumers.

## Admin Area Data Example
Admin area data is grouped by top type with base/metal data where applicable for use in conditionally rendered dropdowns based on top colour selection:

```php
array (size=3)
  'slim' => 
    array (size=21)
    'Arabescato' => 
        array (size=1)
        'base' => 
            array (size=4)
            0 => string 'american walnut' (length=15)
            1 => string 'arabescato' (length=10)
            2 => string 'cobolo' (length=6)
            3 => string 'jet black' (length=9)
    'Calacatta Luxury' => 
        array (size=1)
        'base' => 
            array (size=4)
            0 => string 'american walnut' (length=15)
            1 => string 'calacatta luxury' (length=16)
            2 => string 'moro' (length=4)
            3 => string 'mulberry' (length=8)
```

## Frontend Data Example
Frontend data is grouped by colour name with top types nested below each containing the base/metal values for that colour at the next level.

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