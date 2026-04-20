/**
 * CurrentStatus class manages the dynamic updates to the "Current Status" recap section of the product configurator.
 * It listens for changes in the model selection, swatch options, price updates, and QR code generation, 
 * and updates the corresponding elements in the status recap to reflect the current configuration.
 */
class CurrentStatus {
    
    constructor() {

        // Ensure CurrentStatus is only initialized once per page load (singleton type)
        if (window.__CurrentStatusInitialized) return;
        window.__CurrentStatusInitialized = true;

        // Store content area
        this.contentArea = document.querySelector('.content-area');

        // Model dropdown element
        this.modelSelect = document.querySelector('.obj-model select');

        // Variable to store current model
        this.modelClass = "";
        
        this.init();

    }

    init() {

        this.determineModel();
        this.addListeners();
        //this.monitorSwatchSelections();
        // this.updateImage();
        this.updatePrice();
        this.updateDimensions();
        this.updateSpecText();
        this.createQR();
        this.updateQRCode();

    }

    determineModel() {

        // Determine model based on content area classes
        if (this.contentArea) {

            // Loop through classes to find one that starts with 'model-'
            this.contentArea.classList.forEach(cls => {
                if (cls.startsWith('model-')) {
                    this.modelClass = cls;
                }
            });
        }
    }

    /**
     * Add event listeners to model dropdown and swatch selections to trigger 
     * updates in the status recap when user changes options.
     * @returns {void}
     */
    addListeners() {

        // Model dropdown listener
        const modelSelect = document.querySelector('.obj-model select');

        // Update values on change
        modelSelect.addEventListener('change', () => {

            this.updatePrice();
            this.determineModel();
            this.updateDimensions();
            this.updateSpecText();

        })

    }


    /**
     * Updates the status image based on the current selections.
     * @returns {void}
     */
    // updateImage() {
        
    //     const statusImg = document.querySelector(".status-image img");
    //     const compositeLink = document.querySelector(".wapf-lightbox-link");

    //     if (!statusImg || !compositeLink) return;

    //     // Initial load: poll until real image is loaded
    //     this.pollImage(statusImg, compositeLink.href, 'tt02-shadow.png', 20, 100);

    //     // Watch for subsequent changes to href (user selects new options)
    //     if (!compositeLink._observer) {
    //         const observer = new MutationObserver(() => {
    //             statusImg.src = compositeLink.href;
    //         });

    //         observer.observe(compositeLink, { attributes: true, attributeFilter: ['href'] });
    //         compositeLink._observer = observer; // store ref to avoid duplicates
    //     }
    // }

    // /**
    //  * Polls an image element until it changes from a placeholder to the target source.
    //  * @param {HTMLImageElement} img - The image element to monitor.
    //  * @param {string} targetSrc - The final image source to set.
    //  * @param {string} placeholder - The placeholder image source to wait for.
    //  * @param {number} maxAttempts - The maximum number of polling attempts.
    //  * @param {number} interval - The interval between polling attempts in milliseconds.
    //  */
    // pollImage(img, targetSrc, placeholder = 'tt02-shadow.png', maxAttempts = 20, interval = 100) {
    //     let attempts = 0;

    //     const check = () => {
    //         attempts++;
    //         if (!img.src.includes(placeholder) || attempts >= maxAttempts) {
    //             img.src = targetSrc; // final fallback
    //             return;
    //         }
    //         setTimeout(check, interval);
    //     };

    //     check();
    // }

    /**
     * Monitor swatch selections for each group and update the status layers accordingly. 
     * Uses a combination of MutationObserver to detect class changes (e.g. .wapf-checked) 
     * and event listeners on radio inputs to ensure all interactions are captured.
     * @returns {void}
     */
    // monitorSwatchSelections() {

    //     const groups = ['.obj-top-colour', '.obj-base', '.obj-metal-edge-veneer'];

    //     groups.forEach(selector => {
    //         const container = document.querySelector(selector);
    //         if (!container) return;

    //         // MutationObserver fallback in case any class changes occur elsewhere
    //         const observer = new MutationObserver(() => {
    //             this.updateLayers(selector);
    //         });
    //         observer.observe(container, { attributes: true, subtree: true, attributeFilter: ['class'] });

    //         // Manual click/change handling on inputs
    //         container.querySelectorAll('input[type="radio"]').forEach(input => {
    //             input.addEventListener('change', () => {
                    
    //                 // Remove .wapf-checked from all option divs
    //                 container.querySelectorAll('.wapf-swatch--image').forEach(div => div.classList.remove('wapf-checked'));

    //                 // Add .wapf-checked to the parent div of the selected input
    //                 const parentDiv = input.closest('.wapf-swatch--image');
    //                 if (parentDiv) {
    //                     parentDiv.classList.add('wapf-checked');
    //                 }

    //                 // Trigger layer update for this group
    //                 this.updateLayers(selector);
    //             });
    //         });
    //     });

    //     // Run once on page load for all groups
    //     this.updateLayers();
    // }

    /**
     * Update the layers in the status recap based on the currently selected options.
     * @param {string|null} swatchGroupSelector - The CSS selector for a specific swatch group, or null to update all groups.
     * @returns {void}
     */
    // updateLayers(swatchGroupSelector = null) {

    //     // Array of swatch groups to check
    //     const swatchGroups = ['.obj-top-colour', '.obj-base', '.obj-metal-edge-veneer'];

    //     // Select layers element from DOM, abort if not present
    //     const layersEl = document.querySelector(".status-layer-images");
    //     if (!layersEl) return;

    //     if(swatchGroupSelector === null) {

    //         // Loop through groups and update each group in turn
    //         swatchGroups.forEach(group => {

    //             this.updateSingleLayer(layersEl, group);
                
    //         });

    //     } else {

    //         // Update single layer
    //         this.updateSingleLayer(layersEl, swatchGroupSelector);

    //     }
    // }

    /**
     * Update a single layer in the status recap based on the currently selected options.
     * @param {HTMLElement} layersEl - The container element for all layers.
     * @param {string} selector - The CSS selector for the specific layer group.
     * @returns {void}
     */
    // updateSingleLayer(layersEl, selector) {

    //     // Select group from DOM
    //     const layer = layersEl.querySelector(`${selector}`);

    //     // Select image from DOM
    //     const layerImg = layer.querySelector('img');

    //     // Find newly selected wapf image
    //     const newLayer = document.querySelector(`${selector} .wapf-checked img`);

    //     // If no layer exists, end (e.g. metal edge)
    //     if (!newLayer) return;

    //     // Update group image with new src
    //     layerImg.src = newLayer.src;

    //     // Show layer in status
    //     layer.style.display = 'block';

    //     // Select colour name from DOM
    //     const colour = document.querySelector(`${selector} .wapf-checked label`);

    //     // Take colour name and trim, lowercase and hyphenate
    //     const colourClass = colour.textContent.trim()
    //     .toLowerCase()
    //     .replace(/\s+/g, '-');

    //     // Status colour element
    //     const colourEl = document.querySelector(`${selector} .status-layer-colour`);

    //     // Remove any existing '-finish' class
    //     const finishClass = Array.from(colourEl.classList).find(cls => cls.includes('-finish'));
    //     if (finishClass) {
    //         colourEl.classList.remove(finishClass);
    //     }

    //     // Add colour class appended with '-finish
    //     colourEl.classList.add(`${colourClass}-finish`);

    //     // Update colour text with new value
    //     colourEl.textContent = colour.textContent;

    // }

    /**
     * Update the product price in the status recap based on the currently selected options.
     * @returns {void}
     */
    updatePrice() {

        // Inc-VAT cost to be displayed to user
        const statusPrice = document.querySelector(".status-price");

        // Ex-VAT cost to be added to hidden input
        const configuredTotal = document.getElementById('configured-total');

        // Select currently active option el
        const selectedOption = this.modelSelect.selectedOptions[0];

        // Get base price (ex VAT)
        const basePrice = parseFloat(statusPrice.getAttribute("data-ex-vat-price-base") || "0");

        // Get model price (fallback to 0)
        const modelPrice = parseFloat(selectedOption.getAttribute("data-ex-vat") || "0");

        // Ex-VAT total
        const exVatTotal = basePrice + modelPrice;

        // Apply VAT to base as this is set ex VAT in the backend and add model price
        const displayPrice = exVatTotal * 1.2;

        // Update DOM elements
        configuredTotal.value = exVatTotal;

        // Display price to two decimal places
        statusPrice.textContent = `£${displayPrice.toFixed(2)}`;

    }

    /**
     * Update the dimensions text in the status recap based on the currently selected model.
     * @returns {void}
     */
    updateDimensions() {

        // Select model dims values from DOM
        const modelDims = document.querySelector(".model-dims");

        // Determine current dimensions
        const currentDims = modelDims.querySelector(`.${this.modelClass}`);

        // Status recap dimensions element
        const dimsEl = document.querySelector(".status-dimensions");

        // Update status el with new value
        dimsEl.textContent = currentDims.textContent;

    }

    /**
     * Update the specification text in the status recap based on the currently selected model.
     */
    updateSpecText() {

        // Select specification text from DOM
        const specText = document.querySelector(`.woocommerce-product-attributes-item__value .${this.modelClass}`);

        // Select container for status spec text
        const statusSpecContainer = document.querySelector(".status-specification-text");

        if (statusSpecContainer && specText) {
            // Clear any previous content
            statusSpecContainer.innerHTML = '';

            // Create h3 heading
            const heading = document.createElement('h3');
            heading.textContent = 'Specification:';
            statusSpecContainer.appendChild(heading);

            // Create paragraph for spec text
            const paragraph = document.createElement('p');
            paragraph.innerHTML = specText.innerHTML;
            statusSpecContainer.appendChild(paragraph);
        }
    }

    /**
     * Generate a QR code based on the current page URL (without tvembed parameter) and display it in the .qrcode element.
     */
    createQR = () => {

        // Select QR code container from DOM
        const qr = document.querySelector(".qrcode");

        // Reset QR Code
        qr.innerHTML = "";

        // Clone current URL and remove tvembed
        const url = new URL(window.location.href);
        url.searchParams.delete('tvembed');

        // Generate QR
        new QRCode(qr, {
            text: url.toString(),
            width: 128,
            height: 128,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    };

    /**
     * Copy the generated QR code from the .qrcode element to the .status-qrcode element, 
     * and set up a MutationObserver to keep it in sync with any changes.
     * @returns {void}
     */
    updateQRCode() {
        
        const qrCode = document.querySelector(".qrcode");
        const qrEl   = document.querySelector(".status-qrcode");

        if (!qrCode || !qrEl) return;

        // Initial copy
        this.copyQRCode(qrCode, qrEl);

        // Watch for subsequent changes
        if (!qrCode._observer) {
            const observer = new MutationObserver(() => {
                this.copyQRCode(qrCode, qrEl);
            });

            observer.observe(qrCode, {
                childList: true,
                subtree: true,
                attributes: true,
                characterData: true
            });

            qrCode._observer = observer; // prevent duplicates
        }
    }

    /**
     * Copy the QR code from the source element to the target element.
     * @param {*} source - The source element containing the QR code.
     * @param {*} target - The target element where the QR code should be copied.
     * @returns {void}
     */
    copyQRCode(source, target) {

        // Clear existing content in target
        target.innerHTML = "";

        // Try to find an img element first
        const qrImg = source.querySelector("img");

        // If an img is found, clone it and append to target
        if (qrImg) {
            target.appendChild(qrImg.cloneNode(true));
            return;
        }

        // If no img is found, look for a canvas element
        const qrCanvas = source.querySelector("canvas");

        // If a canvas is found, clone it and append to target
        if (qrCanvas) {
            const cloneCanvas = document.createElement("canvas");
            cloneCanvas.width  = qrCanvas.width;
            cloneCanvas.height = qrCanvas.height;
            cloneCanvas.getContext("2d").drawImage(qrCanvas, 0, 0);
            target.appendChild(cloneCanvas);
        }
    }
}

// Initialize once, after DOM ready
document.addEventListener("DOMContentLoaded", () => {
    new CurrentStatus();
});