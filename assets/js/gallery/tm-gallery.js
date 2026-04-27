class TailormadeGallery {

    constructor() {
        this.lightbox = null;

        // Variable to hold currently selected layer images
        this.selectedLayers = null;
    }

    async initGallery() {

        try {
            // Dynamically import PhotoSwipe modules at runtime
            const { default: PhotoSwipe } = await import('./photoswipe/photoswipe.esm.min.js');
            const { default: PhotoSwipeLightbox } = await import('./photoswipe/photoswipe-lightbox.esm.min.js');

            this.lightbox = new PhotoSwipeLightbox({
                gallery: '.tm-gallery',
                children: 'li > a',
                pswpModule: () => PhotoSwipe,
            });

            this.lightbox.init();

            // Update current layers
            this.currentLayers();

            // update gallery thumbs
            this.updateGalleryThumbs();

        } catch (err) {
            console.error('PhotoSwipe modules failed to load', err);
        }

    }

    currentLayers() {

        // Select the currently checked inputs (not just .wapf-checked)
        const selectedLayers = document.querySelectorAll(".wapf-input:checked");

        // Add image ids
        const images = [...selectedLayers].map(layer => {

            // Isolate image layer by data-value attribute
            const imageDataValue = layer.value;
            const imageLayer = document.querySelector(`[data-value="${imageDataValue}"]`);

            // Extract image ID from layer and return
            return imageLayer ? imageLayer.getAttribute('data-id') : null; 

        }).filter(Boolean); // remove any nulls

        this.selectedLayers = images;

    }

    updateLayersOnClick() {

        // Listen for changes to any WAPF input
        document.addEventListener('change', (event) => {

            // Check if change was triggered by a WAPF input
            const input = event.target;
            if (!input.classList.contains('wapf-input')) return;

            // Update current layers dynamically
            this.currentLayers();

            // Update visible layer images in gallery
            this.updateGalleryThumbs();

            // Update merged image via AJAX
            this.sendRequest();
            
        });
    }

    sendRequest() {

        // Select WAPF product total hidden input from DOM
        const productTotals = document.querySelector(".wapf-product-totals");

        // Extract product id from hidden input
        const productId = productTotals?.getAttribute("data-product-id");
        if (!productId) return;

        // Extract base id from DOM
        const baseLayer = document.querySelector(".wapf-layer-base");
        const baseID = baseLayer?.getAttribute("data-base-id");
        if (!baseID) return;

        // Build settings object
        const settings = {
            base: baseID,
            layers: {}
        };

        // Add image ids to settings.layers.images, reversed for correct stacking
        settings.layers.images = [...this.selectedLayers].reverse();

        // Send AJAX request
        fetch(tmGalleryConfig.ajax, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: new URLSearchParams({
                action: 'tm_gallery_generate_layered_image',
                product_id: productId,
                settings: JSON.stringify(settings),
                _ajax_nonce: tmGalleryConfig.nonce
            })
        })
        .then(res => res.json())
        .then(response => {
            if (response.success && response.data?.url) {
                //console.log('✅ WAPF merged image:', response.data.url);
                const anchor = document.querySelector('.wapf-lightbox-link');
                if (anchor) {
                    anchor.href = response.data.url.status;
                    anchor.setAttribute('data-pswp-src', response.data.url.full);
                }
            } else {
                console.warn('WAPF failed:', response.data?.message);
            }
        })
        .catch(err => console.error('WAPF AJAX error', err));
    }

    updateGalleryThumbs() {

        // Update both sets of layered images
        const selectors = [
            ".wapf-layer-field .wapf-layer-image",
            ".status-image .wapf-layer-image"
        ];

        selectors.forEach(selector => {
            const layerImages = document.querySelectorAll(selector);

            layerImages.forEach(img => {
                if (
                    img.dataset.value === "_base" || // always show base
                    this.selectedLayers.includes(img.dataset.id) // show if selected
                ) {
                    img.style.display = "block";
                } else {
                    img.style.display = "none";
                }
            });
        });
    }

    init() {
        this.initGallery();
        this.updateLayersOnClick();
    }
}

// Initialize gallery once DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const gallery = new TailormadeGallery();
    gallery.init();
});
