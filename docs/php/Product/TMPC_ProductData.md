# TMPC_ProductData class

**Purpose:**  
Aggregate and provide all relevant product data (colour options, model sizes, defaults, and current selections) for use in the configurator UI and current status display.

## Expected Behavior
- Gathers all product configuration data from colour options, post meta, and URL parameters.
- Provides a single data structure for use in config drawers and the current status section.
- Determines the initial state (selected options) based on URL or product defaults.

## Key Methods
- `getProductData()`: Returns all product data for the current product.
- `productInitialState()`: Determines selected options from URL or defaults.
- `setProductDataFromURL($query)`: Sets selected options from URL params, with fallbacks.
- `setDefaultProductData()`: Sets selected options from product defaults.

## Example Data Structure
```php
[
  'colour_options' => [...],
  'selected' => [...],
  'model_sizes' => [...]
]
```
