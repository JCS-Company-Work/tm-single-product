/**
 * CurrentStatus class manages the dynamic updates to the "Current Status" recap section of the product configurator.
 * It listens for changes in the model selection, swatch options, price updates, and QR code generation, 
 * and updates the corresponding elements in the status recap to reflect the current configuration.
 */
class CurrentStatus {
    
    constructor() {

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
        this.updatePrice();
        this.updateSpecText();
        // this.createQR();
        this.updateDimensions();
        this.updateQRCode();
        this.showHideFullSpec();
        this.shareToWhatsapp();
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
            this.updateSpecText();
            this.updateDimensions();

        })

    }

    /**
     * Update the product price in the status recap based on the currently selected options.
     * @returns {void}
     */
    updatePrice() {

        // Inc-VAT cost to be displayed to user
        const statusPrice = document.querySelector(".status-price");

        // Get add to basket price element from DOM
        const addToBasketPrice = document.querySelector(".add-to-basket-price");

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
        addToBasketPrice.textContent = `£${displayPrice.toFixed(2)}`;

        // Display price to two decimal places
        statusPrice.textContent = `£${displayPrice.toFixed(2)}`;

    }

    /**
     * Update the dimensions text in the status recap based on the currently selected model.
     * @returns {void}
     */
    updateDimensions() {

        // Select status price container from DOM
        const statusSpecs = document.querySelector(".status-specifications");

        // Find the active spec list item
        const activeLi = statusSpecs.querySelector('li.d-block');
        
        // Select the table size and seats elements within the active list item
        const tableSizeEl = activeLi ? activeLi.querySelector(".table-size") : null;
        
        // Select the seats element within the active list item
        const seatsEl = activeLi ? activeLi.querySelector(".table-seats") : null;

        // If either element is missing, exit the function
        if (!tableSizeEl || !seatsEl) return;

        // Construct the size string using the text content of the selected elements
        const sizeString = `<p class="bold">Size:</p> ${tableSizeEl.textContent.trim()} - Seats ${seatsEl.textContent.trim()}`;

        // Select the container where the size string should be inserted
        const statusPriceContainer = document.querySelector(".status-price-container");

        // Insert as HTML so tags render
        statusPriceContainer.insertAdjacentHTML('beforeend', sizeString);

    }

    /**
     * Update the specification text in the status recap based on the currently selected model.
     * @returns {void}
     */
    updateSpecText() {

        // Select specification texts from DOM
        const specTexts = document.querySelectorAll(".status-specifications > li");
    
        // Determine active spec text based on model class
        const activeSpecText = document.querySelector(`.status-specifications .${this.modelClass}`);

        // Hide all spec texts first
        specTexts.forEach(spec => spec.classList.add("d-none"));

        // Show only the active spec text
        if(activeSpecText) {
            activeSpecText.classList.remove("d-none");
            activeSpecText.classList.add("d-block");
        }
    }

    /**
     * Show/hide the full technical specifications when the toggle link is clicked.
     * @returns {void}
     */
    showHideFullSpec() {

        // Select toggle link and specifications container from DOM
        const toggleLink = document.querySelector(".full-tech-specs-toggle");

        // Select specifications container from DOM
        const statusSpecs = document.querySelector(".status-specifications");

        // If either element is missing, exit the function
        if (!toggleLink || !statusSpecs) return;

        // Ensure fade class is present for animation
        statusSpecs.classList.add("fade");
        // If not hidden, ensure .show is present
        if (!statusSpecs.classList.contains("d-none")) {
            statusSpecs.classList.add("show");
        }

        // Add click event listener to toggle link
        toggleLink.addEventListener("click", (e) => {

            // Prevent default link behavior
            e.preventDefault();

            // Animate fade in/out
            if (statusSpecs.classList.contains("show")) {
                // Fade out
                statusSpecs.classList.remove("show");
                setTimeout(() => {
                    statusSpecs.classList.add("d-none");
                    // Update toggle link text based on visibility
                    toggleLink.textContent = "View Full Technical Specification";
                }, 400); // match CSS transition duration
            } else {
                // Show and fade in
                statusSpecs.classList.remove("d-none");
                setTimeout(() => {
                    statusSpecs.classList.add("show");
                }, 10); // allow reflow for transition
                // Update toggle link text based on visibility
                toggleLink.textContent = "Hide Full Technical Specification";
            }

        });

    }

    /**
     * Generate a QR code based on the current page URL (without tvembed parameter) and display it in the .qrcode element.
     * @returns {void}
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

    /**
     * Share the current product configuration to WhatsApp.
     * @returns 
     */
    shareToWhatsapp() {

        // Select the WhatsApp share button from the DOM
        const shareBtn = document.querySelector('.share-whatsapp-btn');

        // If the button doesn't exist, exit the function
        if (!shareBtn) return;

        // Add click event listener to the share button
        shareBtn.addEventListener('click', async (e) => {
            // Prevent default link behavior
            e.preventDefault();

            // Get the preview image filename (hash + optional suffix)
            let previewImg = document.querySelector('.status-image .preview-image');
            if (!previewImg) {
                // fallback to any img in .status-image
                previewImg = document.querySelector('.status-image img');
            }
            let filename = '';
            if (previewImg) {
                // Extract filename without extension
                filename = previewImg.src.split('/').pop().replace(/\.(jpg|png)$/i, '');
            }

            // Build the /share/{hash} URL for Open Graph preview
            const shareUrl = `${window.location.origin}/share/${filename}`;

            // Get product details for sharing
            const productTitle = document.querySelector('.product-title')?.textContent.trim() || 'My Table Design';
            const tableSize = document.querySelector('li.d-block .table-size')?.textContent.trim() || '';
            const seats = document.querySelector('li.d-block .table-seats')?.textContent.trim() || '';

            // WhatsApp prefers the preview link to be the first/only link
            let shareText = `${productTitle} - ${tableSize} Table - Seats ${seats}\n${shareUrl}`;

            // Encode the share text for a valid WhatsApp link
            const whatsappLink = `https://wa.me/?text=${encodeURIComponent(shareText)}`;

            shareBtn.setAttribute('href', whatsappLink);
            window.open(whatsappLink, '_blank');
        });
    }
}

// Initialize once, after DOM ready
document.addEventListener("DOMContentLoaded", () => {
    new CurrentStatus();
});