# TMPC_Images class

**Purpose:**  
Build and serve composite product images for current layer selection in three frontend sizes: `1600`, `700`, and `400`.

The REST route is registered on `plugins_loaded` via `init()`. All methods are static.

## Expected Behavior
- Frontend selection changes request a matching composite image set.
- On page load, URL params or saved defaults are used to resolve initial layers.
- New composites are generated in three sizes:
  - 1600px → gallery
  - 700px → current status
  - 400px → basket/checkout/email

## Frontend Payload Example
Frontend sends:

```js
body: JSON.stringify({
  selectedLayers: selectedLayers,
  productID: TMPCPlugin.product_id
})
```

`productID` is used to resolve product SKU and layer paths.

## On Page Load
- If URL params exist, they are validated and used first.
- If URL params are missing/invalid, defaults are used.
- Valid configurations reuse existing composites when available.



## Request Handling: `buildImageUrl()`
The callback validates request params, resolves SKU data, and passes selected layers to `processLayers()`. Invalid payloads return an error response.

## Layer Processing: `processLayers()`
`processLayers()` parses the SKU, resolves required layer keys, normalizes colour names to lowercase slugs, and builds image file paths using environment paths.

```php
$images['top_layer'] = $base_folder . '/' . $top_layer . '-' . str_replace(' ', '-', strtolower($selectedLayers['top'])) . '.png';
```

Each layer path is added to an `$images` array and returned.

## Composite Image Creation: `buildCompositeImage()`
Layer path data is hashed to create a stable filename key.
If a matching output already exists, it is returned immediately.
If not, new composite files are generated.

## Image Generation with GD
When required, GD builds new composites and writes files using `{hash}-{size}.png` naming:
```php
rtrim($_ENV['IMAGE_LAYER_COMPOSITES_PATH'], '/') . "/{$hash}-{$size}.png"
```

Layers are composited from bottom to top (`shadow -> base -> metal -> top`).
The class uses an in-memory large render and then resizes down to the final saved outputs.
