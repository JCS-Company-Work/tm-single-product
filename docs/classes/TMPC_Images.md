# TMPC_Images class

**Purpose:**  
Purpose of this class is to create composite images in the three sizes that we need for the frontend 
(1600, 700 and 400px). To accomplish this the class creates the 'tmpc/v1/update-product-images/ API to allow image creation to be triggered on the frontend  each time a user makes a change by clicking a new top/base/metal image. 

API is scaffolded at 'plugins_loaded' hook via init() method. All class methods are static.

## Expected Behavior
- User selections on frontend JS trigger creation of new/serving of existing images to DOM
- PHP creates/serves first images on page load via URL param check
- New image creation generates three sizes:
  - 1600px → gallery
  - 700px → current status
  - 400px → basket/checkout/email

## Frontend Payload Example
The frontend payload is

```js
    body: JSON.stringify({
        selectedLayers: selectedLayers,
        productID: TMPCPlugin.product_id
    })
```
which is the productID which allows us to access the product SKU via WooCommerce object and the names of the currently selected layers.

## On page load PHP image update
- On page load we check if there are params in the URL
- If params we serve/create images based on those. We check our Available options endpoint/transient to check if the current config is valid and either serve it or correct it based on top layer value
- If no params in url we check database for the default values for that product and serve/create those.



## Request Handling: buildImageUrl()
When request received buildImageUrl() callback is called which extracts and verifies params included in the request, only proceeding on if they are valid and returning error to JS if not. Callback method uses supplied product ID to extract product SKU via global product and passes supplied top/base/metal names to static method processLayers();

## Layer Processing: processLayers()
ProcessLayers() breaks up the product SKU via regex rules and assigns required fragments to $top_layer, $base_layer, $shadow_layer and (where applicable), $metal_layer, converts each colour name to a lowercased, hyphenated string, i.e. Golden Ambra -> golden-ambra and concatenates this data together with the base folder address held in the plugin .env file to create the filepath of the image for that layer:

```php
$images['top_layer'] = $base_folder . '/' . $top_layer . '-' . str_replace(' ', '-', strtolower($selectedLayers['top'])) . '.png';
```

Each image is assigned to the $images layer with its layer value as the key ($images['top_layer'] as above) and the completed array is returned.

## Composite Image Creation: buildCompositeImage()
Returned data is passed to buildCompositeImage() method which gets image final destination path IMAGE_LAYER_COMPOSITES_PATH from .env and creates an md5 hash based on supplied images and concatenates them together. 

The idea with the hash is that the same combination of images will produce the same hash value so we can use this to only create new images where required, if the images already exist we serve those immediately. The method accomplishes this by checking if the completed path alreadys exists for our largest size (1600) exists.

## Image Generation with GD
If new images are required we use built-in GD (Graphic Draw) library to create our three sizes, creating file names for each which comprise of the hash and size as below:
```php
rtrim($_ENV['IMAGE_LAYER_COMPOSITES_PATH'], '/') . "/{$hash}-{$size}.png"
```

GD library builds from the base upwards so we supply our layer in effectively reverse order: shadow->base->metal->top and we notify GD that the shadow is our base as it has no effective canvas so uses the bottom layer.

The first image created is full size (2000px) which is held in memory and never saved. The 1600 is created from this and then is saved, with the remaining sizes being based on that and the 2000px image being discarded. This is why we have both resizeImageFromResource() and resizeImage() methods, the former handles creating the 1600px image from memory and the latter resizing the saved 1600px image to create our other two sizes.
