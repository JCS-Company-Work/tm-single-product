/**
 * Product class to manage the dynamic updating of available product options based on the selected top colour in the product configurator.
 * 
 * This class fetches the available options for each top colour from the server, listens for changes to the top colour selection, and updates the available options for the base and edge groups accordingly. It also handles setting the initial state of the configurator based on URL parameters or default selections in the HTML.
 * 
 * The mapping of available options for each top colour is defined in a Google Sheet and accessed via a custom REST API endpoint.
 */
class Product {

    // ===================== Init & Lifecycle ===================== //
    constructor() {

		// Object to hold colour options data from server
        this.colourOptions = {};

		// Map option types to css classes
		this.optionToClass = {
			base: 'base',
			metal: 'metal-edge-veneer'
		};

        // Object to hold currently available options for the selected top colour
        this.availableOptions = {};

        // Object to hold the currently selected options for product
        this.selectedOptions = {};

		// Init functions
		this.init();

    }

	init() {
		this.fetchColourOptions();
		this.setConfigDrawerState();
        this.addSwatchListeners();
        this.initCreatedByUs();
	}

    // ===================== Data Fetching & Setup ===================== //

	/**
	 * Fetch colour options data from the server and initialize the configurator with the fetched data.
	 */
    fetchColourOptions = () => {

		// Fetch colour options data from the server
        fetch('/wp-json/tmpc/v1/colour-options?product_id=' + TMPCPlugin.product_id)
		.then(response => response.json())
		.then(data => {

			// Store fetched colour options in class property
			this.colourOptions = data.colour_options;
			
			// Set initial options based on URL parameters or default selections in the HTML
			this.setInitalColours();

		})
		.catch(err => {

			// Log any errors that occur during the fetch operation
			console.error('Failed to fetch colour options:', err);
		});

    }

    /** 
     * Set initial options for base and edge groups based on URL parameters or default selections in the HTML. 
     */
    setInitalColours = () => {

        // Find the initially checked top colour swatch in the DOM
        const checkedTopColour = document.querySelector('.obj-top-colour .wapf-input:checked');

        // Extract the swatch name from the label of the checked input to identify the selected top colour
        const swatchName = checkedTopColour.closest('label').getAttribute('aria-label')?.trim();

        // Set available options for base and edge groups based on the initially selected top colour
         this.setColourOptions(swatchName);

    }

    // ===================== UI State & Event Handling ===================== //

	/**
	 * Set up event listeners to open and close the configuration drawer.
	 * 
	 */
    setConfigDrawerState = () => {

		// Select elements from DOM
        const configWrapper = document.querySelector('#configurator');
        const configCloseButton = document.getElementById('configCloseButton');
        const configMask = document.getElementById('configMask');

		// Array of selectors that should open the drawer on click
        const optionSelectors = [
            '#option-top-colour',
            '#option-metal-edge-veneer',
            '#option-base',
            '#option-model'
        ];

		// Add click event listeners to elements that should open the drawer. 
        optionSelectors.forEach(sel => {
			document.querySelector(sel)?.addEventListener('click', function () {
				configWrapper.classList.value = `configurator config-open ${this.id} last-opened-${this.id}`;
				configCloseButton.focus();
			});
        });

		// Add click event listeners to elements that should close the drawer
        [configCloseButton, configMask].forEach(el => {
            el?.addEventListener('click', () => {
                configWrapper.classList.remove('config-open');
            });
        });

    }

    /**
     * Add change event listeners to all swatch radio inputs (top, base, metal).
     * If input is in obj-top-colour, update options and images.
     * If input is in obj-base or obj-metal, only update images.
     */
    addSwatchListeners = () => {

        // Select all radio inputs in all swatch groups
        const allInputs = document.querySelectorAll('.wapf-swatch input[type="radio"]');

        allInputs.forEach(input => {

            input.addEventListener('change', () => {

                // Find which group this input belongs to
                const topGroup = input.closest('.obj-top-colour');
                const baseGroup = input.closest('.obj-base');
                const metalGroup = input.closest('.obj-metal-edge-veneer');

                if (topGroup) {

                    // Top colour changed: update options and images
                    const label = input.closest('label');
                    const swatchName = label ? label.getAttribute('aria-label')?.trim() : '';

                    this.setColourOptions(swatchName);
                    this.setSelectedOptions();
                    this.updateStatusLayer(input);

                } else if (baseGroup || metalGroup) {

                    // Base or metal changed: only update images
                    // Ensure selectedOptions is up to date
                    this.setSelectedOptions(); 
                    this.updateStatusLayer(input);

                }

                // Schedule a single composite image update regardless of which layer changed
                this.scheduleCompositeUpdate();

            });
        });
    }

    // ===================== Option Logic & State Management ===================== //

    /**
     * Set available options for base and edge groups based on the selected top colour swatch.
     * The mapping of available options for each swatch is defined in the colourOptions object.
     * @param {string} topColour - The name of the selected top colour swatch 
     */
    setColourOptions = (topColour) => {

        // If top colour is multi-word, convert spaces to underscores to match keys in colourOptions
        const formattedTopColour = topColour.toLowerCase().trim().replace(/\s+/g, '_');

        // Set available bases and edges based on the swatch name
        this.availableOptions = this.colourOptions[formattedTopColour] || {};

		// Convert available options object to an array of [optionType, optionsArray] pairs for easier iteration
        const availableOptionsArr = Object.entries(this.availableOptions);

        // Set the selected options based on the currently checked swatches in the DOM
        this.setSelectedOptions();

        // Update the options in the UI based on the available options for the selected top colour
        this.setDefaults();

        // Loop over available options and update the UI accordingly (e.g., show/hide or enable/disable options)
        this.showHideOptions(availableOptionsArr);

    };

    /**
     * Get available options for a given top colour.
     * @param {string} topColour - The name of the selected top colour swatch.
     * @returns {Object} An object containing available options for the selected top colour.
     */
    getAvailableOptions(topColour) {
         // If top colour is multi-word, convert spaces to underscores to match keys in colourOptions
        const formattedTopColour = topColour.toLowerCase().trim().replace(/\s+/g, '_');

        // Set available bases and edges based on the swatch name
        return this.colourOptions[formattedTopColour] || {};
    }

    /**
     * Show or hide options in the UI based on the available options for the selected top colour.
     * @param {Array} availableOptionsArr - An array of [optionType, optionsArray] pairs representing available options.
     */
    showHideOptions = (availableOptionsArr) => {

        // Loop over available options and update the UI accordingly (e.g., show/hide or enable/disable options)
        availableOptionsArr.forEach(([optionType, optionsArray]) => {

            // Find non-matching options in DOM and disable them
            const optionElements = document.querySelectorAll(`.obj-${this.optionToClass[optionType]} .wapf-swatch`);

            optionElements.forEach(el => {

                // Extract option name from label and compare with available options
                const label = el.querySelector('label').textContent.toLowerCase().trim();

				// Show/hide options
                el.style.display = optionsArray.includes(label) ? 'inline' : 'none';

            });

        });
    }

    /**
     * Set the selected options for the product based on the currently checked swatches in the DOM. 
     * This function is used to determine the default selections for base and edge groups based on 
     * the selected top colour and to update the selectedOptions object with the current selections.
     */
    setSelectedOptions() {

        // Loop over optionToClass and log key/class
        Object.entries(this.optionToClass).forEach(([key, className]) => {

            // Find swatches in DOM
            const swatchesGroup = document.querySelector(`.wapf-field-group .obj-${className}`);

            // If no swatches found for this group, skip to next iteration
            if(!swatchesGroup) {
                return;
            }

            // If there are swatches find the currently checked option for this group
            const checkedSwatch = swatchesGroup.querySelector('input[type="radio"]:checked')?.closest('.wapf-swatch');

            // If no checked swatch exists yet, skip safely.
            if (!checkedSwatch) {
                return;
            }

            // If there is a checked option, extract the value and check if it's available for the selected top colour
            const input = checkedSwatch.querySelector('input');

            if (!input) {
                return;
            }

            // Extract the value of the checked option and format it for comparison
            const value = input.value.toLowerCase().trim();

            // Get the list of available options for this group from the availableOptions object
            const availableList = Array.isArray(this.availableOptions[key]) ? this.availableOptions[key] : [];

            if (availableList.length === 0) {
                return;
            }

            // Check if the currently checked option is in the list of available options
            const isAvailable = availableList.includes(value);

            // Set up selectedOption variable to hold final value
            let selectedOption;
            
            if(!isAvailable) {
                // If the current option is not available for the top colour, find the first available option and set selectedOption to that
                selectedOption = this.getFirstAvailableOption(swatchesGroup, availableList);

                // If no available options are found, log a warning and return early to avoid errors
                if (!selectedOption) {
                    console.warn(`No available options found for ${key} with the selected top colour.`);
                    return;
                }

                // Check the input inside the swatch to update the form state
                const input = selectedOption.querySelector('input');
                if (input) {
                    input.checked = true;
                }
                
            }

            // If the currently checked option is available, use it as the default selection
            if (isAvailable) {
                selectedOption = checkedSwatch;
            }

            // Extract the image file name from the selected option to use as the default option value
            // Add colour and file name to object of defaults to be sent in the custom event
            const swatchImage = selectedOption.querySelector('.swatch');
            const imgFileName = this.getImageFileName(swatchImage);
            const selectedLabel = selectedOption.querySelector('label')?.textContent?.trim();

            if (!imgFileName || !selectedLabel) {
                return;
            }

            // Build object with options for each layer
            this.selectedOptions[key] = {
                filename: imgFileName,
                swatchName: selectedLabel
            };

        });

        // Also include the selected top colour as part of the defaults sent in the custom event
        const topColour = document.querySelector('.obj-top-colour input[type="radio"]:checked');

        if (!topColour) {
            return;
        }

        // Extract the image file name from the selected top colour swatch to use as the default option value
        const topSwatchImage = topColour.parentElement?.querySelector('.swatch');
        const topFileName = this.getImageFileName(topSwatchImage);

        if (!topFileName) {
            return;
        }

        this.selectedOptions.top = { 
            filename: topFileName,
            swatchName: topColour.value.trim()
        };

    }

	/**
	 * Enforce defaults if the currently selected options for base and edge 
     * are not available for the selected top colour.
	 */
    setDefaults = () => {

        // Dispatch custom event with the default selections for base and edge groups based on the selected top colour
        const event = new CustomEvent('colourOptionsChanged', {
            detail: {
                defaults: this.selectedOptions
            }
        });

        window.dispatchEvent(event);
    }

    /**
     * Update the status layer images based on the currently selected options for top, base and metal. 
     * If the change was triggered by a top colour selection, update all layers based on the new available options for base and edge. If the change was triggered by a base or edge selection, only update the corresponding layer.
     * @param {HTMLElement} checkedInput 
     */
    updateStatusLayer(checkedInput) {

        // Find swatch group to determine which layer to update
        const swatchGroup = checkedInput ? checkedInput.closest('[class*="obj-"]') : null;

        // Determine which status image to change based on the swatch group
        let objClass = null;
        if (swatchGroup) {
            objClass = Array.from(swatchGroup.classList).find(cls => cls.startsWith('obj-'));
        }

        // If top colour changed update all status layers based on selected options
        if (objClass === 'obj-top-colour') {

            // Get all checked inputs
            const checkedInputs = document.querySelectorAll('.obj-top-colour .wapf-input:checked, .obj-base .wapf-input:checked, .obj-metal-edge-veneer .wapf-input:checked');

            // Update each layer based on the checked inputs
            checkedInputs.forEach(input => {
                this.updateSingleLayer(input, input.closest('.obj-top-colour') ? 'obj-top-colour' : (input.closest('.obj-base') ? 'obj-base' : 'obj-metal-edge-veneer'));
            });

        } else if (objClass) {

            // Update single layer based on the checked input in the base or metal groups
            this.updateSingleLayer(checkedInput, objClass);

        }
        
    }

    /**
     * Update single status layer image
     * @param {HTMLElement} checkedInput 
     * @param {string} objClass 
     */
    updateSingleLayer(checkedInput, objClass) {

        // Get new layer from DOM based on the checked input
        const newLayer = checkedInput ? checkedInput.parentElement.querySelector('.swatch') : null;

        // Get label text for the checked input to use as the layer name
        const layerName = checkedInput ? checkedInput.closest('.wapf-swatch').querySelector('label').textContent.trim() : null;

        // Get status image element to update
        const statusImg = document.querySelector(`.status-layer-images .${objClass} img`);

        // Get status image text element to update
        const statusImgText = document.querySelector(`.status-layer-images .${objClass} .status-layer-colour`);

        // Update status image src and alt attributes based on the new layer
        if (statusImg && newLayer) {

            // Update the status image source
            statusImg.src = newLayer.src;
            
            // Update the alt text of the status image
            statusImg.alt = layerName;

            // Update class names to reflect new selected layer
            statusImgText.className = `status-layer-colour ${layerName.toLowerCase().replace(/\s+/g, '-')}-finish`;
            
            // If there is a text element for the status image, update its text content with the layer name
            if (statusImgText) {
                statusImgText.textContent = layerName;
            }
        }

        // Update product info text in status container
        const statusPriceContainer = document.querySelector(`.status-price-container .${objClass}`);

        statusPriceContainer.textContent = layerName;


    }

    /**
     * Schedule a single call to updateCompositeImages, debounced so multiple
     * rapid changes (e.g. from a "created by us" click) only trigger one update.
     */
    scheduleCompositeUpdate = () => {
        clearTimeout(this._compositeUpdateTimer);
        this._compositeUpdateTimer = setTimeout(() => {
            this.updateCompositeImages();
        }, 100);
    }

    /**
     * Update status and gallery composite images based on the default selections for the selected top colour
     * @returns {void}
     */
    updateCompositeImages() {

        // Select the image element within the status container
        const statusImg = document.querySelector(".status-image img");

        // Select composite image element in gallery        
        // const galleryCompositeImg = document.querySelector(".composite-image");

        // If image elements missing, exit the function
        if (!statusImg /* || !galleryCompositeImg */) return;

        // Get product id TMPCPlugin.product_id set on window
        const productID = window.TMPCPlugin ? window.TMPCPlugin.product_id : null;

        // Build payload with selected options for top, base and metal (if metal exists)
        const payload = {
            top: this.selectedOptions.top.swatchName,
            base: this.selectedOptions.base.swatchName,
        }

        // If metal set add to payload
        if(this.selectedOptions.metal) {
            payload.metal = this.selectedOptions.metal.swatchName;
        }
        
        // Trigger image update
        fetch('/wp-json/tmpc/v1/update-product-images/', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                product_id: productID,
                selectedLayers: payload
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            // Update status and gallery images in one block
            if (data.images) {
                if (data.images['700'] && statusImg) {
                    statusImg.src = data.images['700'];
                }
                // if (data.images['1600'] && galleryCompositeImg) {
                // this.updateGalleryCompositeImage(galleryCompositeImg, data.images['1600']);
                // }
            }
        })
        .catch(error => {
            console.error('Error fetching image:', error);
        });
    }

    // updateGalleryCompositeImage = (imgElement, newSrc) => {

    //     const img = imgElement.querySelector('img');
    //     const link = imgElement.querySelector('a');
    //     if (img) img.src = newSrc;
    //     if (link) {
    //         link.setAttribute('data-pswp-src', newSrc);
    //         link.setAttribute('href', newSrc);
    //     }

    // }

    /**
     * Get the first available option from a list of swatches.
     * @param {NodeList} groupSwatches - The list of swatch elements.
     * @param {Array} availableList - The list of available option names.
     * @returns {HTMLElement|null} - The first available swatch element or null if none found.
     */
    getFirstAvailableOption = (groupSwatches, availableList) => {

        // Get swatch elements from group
        const swatches = groupSwatches.querySelectorAll('.wapf-swatch');

        // Find the first available option in the DOM and select it
        return Array.from(swatches).find(el => {

            // Extract option name from label and compare with available options
            const label = el.querySelector('label')?.textContent.toLowerCase().trim();

            // Return true if this option is in the list of available options for the selected top colour
            return availableList.includes(label);

        });

    }

    /**
	 * Determine the product type based on product category classes.
	 * @returns {string} - The product type (e.g., 'solid', 'slim', 'edge', 'slim-edge')
	 */
	productType = () => {

		// Get product categories from body classes (e.g., product_cat-solid, product_cat-slim, etc.)
		const productCategories = document.querySelector('.product').className.split(' ').filter(c => c.startsWith('product_cat-')).map(c => c.replace('product_cat-', ''));

		// Determine product type based on categories
		const types = ['solid', 'slim', 'edge'];

		// Check which types are present in the product categories
		const matched = types.filter(type => productCategories.includes(type));

		// If only one type is matched, return that type; if both 'slim' and 'edge' are matched, return 'slim-edge'
		if (matched.length === 1) {
			return matched[0];
		}

		// If both 'slim' and 'edge' are present, return 'slim-edge'
		if (matched.length === 2 && matched.includes('slim') && matched.includes('edge')) {
			return 'slim-edge';
		}

	}

    // ===================== Utility Functions ===================== //

    /**
     * @param {HTMLElement} swatchImage 
     * @returns {string|null} - The extracted image file name or null if not found
     */
    getImageFileName = (swatchImage) => {

        // Get the src of the image inside the option element
        const imgSrc = swatchImage?.src;

        // Extract swatch name from image URL using regex matches the part after "uploads/" and before "-{width}x{height}.jpg"
        const swatchName = imgSrc?.match(/uploads\/(.+?)-\d+x\d+\.jpg/);

        // If the regex matches, swatchName[1] will contain the swatch name, otherwise it will be null
        const result = swatchName ? swatchName[1] : null;

        //Return result
        return result;
    }

    initCreatedByUs() {

        // Select all configuration items from DOM
        const configItems = document.querySelectorAll('.created-by-us-configuration');

        configItems.forEach(config => {

            // Add click event listener to each configuration item
            config.addEventListener('click', () => {

                // Get data attributes for clicked item
                const top = config.getAttribute('data-top');
                const base = config.getAttribute('data-base');
                const metal = config.getAttribute('data-metal');

                // Unset all currently checked inputs in the DOM to reset the state before applying the new configuration
                document.querySelectorAll('.wapf-swatch input[type="radio"]:checked').forEach(input => input.checked = false);

                // Find corresponding swatches in the DOM based on data attributes and check them
                let topSwatch = null;

                if (top) {
                    topSwatch = document.querySelector(`.obj-top-colour .wapf-swatch label[aria-label="${top}"] input`);
                    if (topSwatch) {
                        topSwatch.checked = true;
                        topSwatch.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
                if (base) {
                    const baseSwatch = document.querySelector(`.obj-base .wapf-swatch label[aria-label="${base}"] input`);
                    if (baseSwatch) {
                        baseSwatch.checked = true;
                        baseSwatch.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
                if (metal) {
                    const metalSwatch = document.querySelector(`.obj-metal-edge-veneer .wapf-swatch label[aria-label="${metal}"] input`);
                    if (metalSwatch) {
                        metalSwatch.checked = true;
                        metalSwatch.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }

                // After updating the checked state of the swatches, ensure the UI updates accordingly
                this.setColourOptions(top);
                this.updateStatusLayer(topSwatch);

            });
        });

    }
}

// Init class
window.addEventListener('DOMContentLoaded', () =>  new Product());