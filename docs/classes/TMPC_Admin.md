# TMPC_Admin class

**Purpose:**  
Create custom fields in admin backend on per product basis to allow us to set default values for Top Colour, Base Colour and where appropriate, Metal Colour values.

## Expected Behavior
- User visits product backend and sees custom fields area called Default Colours, populated by top colour only dropdown initially. Values in dropdown are controlled by product type (slim, edge, solid).
- When user makes a top colour selection, base and metal dropdowns appear where applicable with corresponding values attached to that top colour, giving us granular control over colour pairings.
- Once selection is made, user saves the post using the standard save button, selections then persist on refresh and subsequent page visits.

## Data structure
Structure is slightly different to TM Colour Options data and is grouped by product type rather than colour as below:
```php
    Arabescato New' => 
    array (size=1)
      'base' => 
        array (size=2)
          0 => string 'cobolo' (length=6)
          1 => string 'jet black' (length=9)
```

## Custom field creation
On page load we have add_colour_dropdowns() method hooked to add_meta_boxes hook which adds our custom fields to the product admin area. This triggers the render_default_colours_box() callback which actually adds the fields.

## Colour selection and update
render_default_colours_box() contains the PHP and JS logic to conditionally render base/metal dropdowns based on user selection with the options presented to the user being consistent with our data in TM Store Available Options spreadsheet.

First thing the method does is create a nonce value for our data save, it then checks the database for existing selections to populate dropdowns with. There are selections for top colour and base for example, both dropdowns appear immediately rather than just top colour.

Next the method accesses the TM Store Available Options data via our TMPC_ColourOptions class as below:
```php
    TMPC_ColourOptions::getAdminColourOptions();
```

Once received it filters the data based on the product type via get_product_type() method which access the WP category ids and checks for id matches against static array of ids and returns the slug of the first match (slim, edge, solid). This is used to populate top colours dropdown and $availableColours data is then added to the Window to be accessible to JS for use conditionally rendering base and metal dropdowns with correct data:
```JS
    window.TMPC_COLOURS = <?php echo wp_json_encode($availableColours); ?>;
```
On DOMContentLoaded this JSON is saved into variable const = data, change listener is attached to top colour dropdown and helper functions capitliseWords, populate and updateOptions are instantiated to populate dropdowns from our data variable and convert colour labels to correct case.

## Saving selections
Selected options are saved via standard post save button. save_default_colours() is hooked to this via save_post and triggers the saving of separate values via update_post_meta().