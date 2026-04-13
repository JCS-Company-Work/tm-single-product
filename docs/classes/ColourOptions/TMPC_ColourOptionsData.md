# TMPC_ColourOptions class

**Purpose:**  
Purpose of this class is to fetch new data from Google Sheets and return it in the correct format. New fetch request updates both frontend and admin data to ensure that both are fully up to date and in sync at all times.

All class methods are static.

## Expected Behavior
- Frontend request to /colour-options or admin area call to TMPC_ColourOptionsService::getAdminColourOptions() checks for existing cached data and finds it empty/invalid. This triggers call to getDataFromGoogleSheets() to update data which updates both frontend and admin data and sets it as a 30 day transient at 'tmpc_sheets_data' and 'tmpc_sheets_admin_data' respectively.
- Request to /colour-options-update triggers call to getDataFromGoogleSheets() to update data as above.

## Google Sheets Data Update Example
Data update is triggered in Google Sheet using Update -> Update Colour Option Data in menu. This POSTS to /wp-json/tmpc/v1/colour-options-update with the key value required to trigger update via $_SERVER['HTTP_X_TMPC_TOKEN']. POST request triggers getDataFromGoogleSheets() callback which checks the supplied update key against the corresponding value $_ENV['TMPC_UPDATE_TOKEN'] held in our .env file. If values match, data update is trggered, if not new 403 'Invalid or missing token' error is returned.

In the case of update all credentials for access of Google Sheet data are held and accessed in our .env and values returned are formatted and saved as 30 day transients for frontend and admin via formatFrontEndData() and formatAdminData() respectively.

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