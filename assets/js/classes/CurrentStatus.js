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
        
        // Initialize the class by setting up listeners and updating the status recap
        this.init();

    }

    init() {

        this.determineModel();
        this.addModelListeners();
        this.addSwatchListeners();
        this.updatePrice();
        this.updateDimensions();
        this.updateSpecText();
        this.createQR();
        this.updateQRCode();

    }

    /**
     * Determine the current model based on the classes present in the content area 
     * and store it in this.modelClass for later use.
     */
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
    addModelListeners() {

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
     * Add event listeners to the swatch radio inputs for colour, base and metal options.
     * When a swatch option is changed, it triggers an update to the status image to reflect the new selections.
     */
    addSwatchListeners = () => {

        // Select all radio inputs in the top colour, base and metal swatches
        const inputs = document.querySelectorAll('.wapf-field-group .wapf-swatch input[type="radio"]');

        // Add change event listener to each input
        inputs.forEach(input => {
            input.addEventListener('change', () => {

                const selected = this.getSelectedOptions(inputs);

                // Update status image
                this.updateStatusImage(selected);
            });
        });
    }

    /**
     * Get the currently selected options from a set of radio inputs.
     * @param {NodeList} inputs - A NodeList of radio input elements.
     * @returns {Object} An object containing the selected options keyed by their logical names.
     */
    getSelectedOptions(inputs) {

        // Map parent group classes to logical keys
        const classMap = {
            'obj-top-colour': 'colour',
            'obj-base': 'base',
            'obj-metal-edge-veneer': 'metal'
        };

        // Build selected object by looping over all inputs
        const selected = {};
        inputs.forEach(radio => {
            if (radio.checked) {
                // Find the parent group class
                const group = Object.keys(classMap).find(cls => radio.closest(`.${cls}`));
                if (group) {
                    selected[classMap[group]] = radio.parentElement.getAttribute('aria-label')?.trim() || radio.value;
                }
            }
        });

        return selected;
    }

    /**
     * Updates the status image based on the current selections.
     * @returns {void}
     */
    updateStatusImage(selected) {

        // Select the image element within the status container
        const statusImg = document.querySelector(".status-image img");

        // If no image element exists, exit the function
        if (!statusImg) return;

        // Get product id TMPCPlugin.product_id set on window
        const productID = window.TMPCPlugin ? window.TMPCPlugin.product_id : null;
        
        // Trigger image update
        fetch('/wp-json/tmpc/v1/update-product-images/', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                product_id: productID,
                selectedLayers: {
                    top: selected.colour,
                    base: selected.base,
                    metal: selected.metal
                }
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data && data.images && data.images['700']) {
                statusImg.src = data.images['700'];
            }
        })
        .catch(error => {
            console.error('Error fetching image:', error);
        });
    }

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