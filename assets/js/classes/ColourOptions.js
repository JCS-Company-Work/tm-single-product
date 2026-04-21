/**
 * ColourOptions class to manage the dynamic updating of available product options based on the selected top colour in the product configurator.
 * 
 * This class fetches the available options for each top colour from the server, listens for changes to the top colour selection, and updates the available options for the base and edge groups accordingly. It also handles setting the initial state of the configurator based on URL parameters or default selections in the HTML.
 * 
 * The mapping of available options for each top colour is defined in a Google Sheet and accessed via a custom REST API endpoint.
 */
class ColourOptions {

    // ===================== Init & Lifecycle ===================== //
    constructor() {

		// Object to hold colour options data from server
        this.colourOptions = {};

		// Map option types to css classes
		this.optionToClass = {
			base: 'base',
			metal: 'metal-edge-veneer'
		};

		// Init functions
		this.init();

    }

	init() {
		this.fetchColourOptions();
		this.setConfigDrawerState();
        this.addSwatchListeners();
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
			console.log('Fetched colour options:', this.colourOptions);
			
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

        //this.updateImages();

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
				configWrapper.classList.add('config-open', this.id);
				configCloseButton.focus();
			});
        });

		// Add click event listeners to elements that should close the drawer
        [configCloseButton, configMask].forEach(el => {
            el?.addEventListener('click', () => {
                configWrapper.classList.value = 'configurator';
            });
        });

    }

    /**
     * Add click event listeners to swatches in the top colour group. 
     * When a swatch is clicked, update the available options for the other groups based on the selected colour.
     * 
     */
    addSwatchListeners = () => {

        // Select all radio inputs in the top colour swatches
        const inputs = document.querySelectorAll('.obj-top-colour .wapf-swatch input[type="radio"]');
        
        // Add change event listener to each input
        inputs.forEach(input => {
        
            input.addEventListener('change', () => {

                // When a top colour swatch is selected, get the corresponding label text to identify the selected colour
                const label = input.closest('label');
                const swatchName = label ? label.textContent.trim() : '';
                this.setColourOptions(swatchName);

                // Update images on page
                this.updateImages();

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
        const availableOptions = this.colourOptions[formattedTopColour] || {};

		// Convert available options object to an array of [optionType, optionsArray] pairs for easier iteration
        const availableOptionsArr = Object.entries(availableOptions);

        // Update the options in the UI based on the available options for the selected top colour
        this.setDefaults(availableOptions);

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
	 * Enforce defaults if the currently selected options for base and edge are not available for the selected top colour.
	 * @param {Object} availableOptions - An object of available options for the selected top colour.
	 */
    setDefaults = (availableOptions) => {

		// Array of option types and their corresponding DOM classes
        const optionGroups = [
            { key: 'base', className: 'base' },
            { key: 'metal', className: 'metal-edge-veneer' }
        ];

        // Object to hold default selections for each option group
        const defaults = {};
	
        optionGroups.forEach(group => {

			// Get the currently checked option for this group from the DOM
            const checked = document.querySelector(`.obj-${group.className} input[type="radio"]:checked`).parentElement;
			// Extract the label of the checked option and format it for comparison
			const checkedLabel = checked?.getAttribute('aria-label')?.toLowerCase().trim();

			// Get the list of available options for this group from the availableOptions object
			const availableList = availableOptions[group.key] || [];


			// Check if the currently checked option is in the list of available options
			const isAvailable = availableList.includes(checkedLabel);

            // Variable to hold colour for current group - either the currently checked option if it's available, 
            // or the first available option if the checked one is not available
            let groupSwatch;

			if (!isAvailable) {

                // Select swatches for current group from DOM
                const groupSwatches = document.querySelectorAll(`.obj-${group.className} .wapf-swatch`);
            
				// Find first available option for this top colour
                groupSwatch = this.getFirstAvailableOption(groupSwatches, availableList);

                // If no available options are found, log a warning and return early to avoid errors
                if (!groupSwatch) {
                    console.warn(`No available options found for ${group.key} with the selected top colour.`);
                    return;
                }

                // Check the input inside the swatch to update the form state
                const input = groupSwatch.querySelector('input');
                if (input) {
                    input.checked = true;
                }
            }

            // If the currently checked option is available, use it as the default selection
            if (isAvailable) {
                groupSwatch = checked;
            }

            // Add colour and file name to object of defaults to be sent in the custom event
            // Extract the image file name from the selected swatch to use as the default option value
            const swatchImage = groupSwatch.querySelector('.swatch');
            const imgFileName = this.getImageFileName(swatchImage);

            // Store the default option value in the defaults object to be sent with the custom event
            defaults[group.key] = {
                filename: imgFileName,
                swatchName: groupSwatch.getAttribute('aria-label')?.trim()
            };

        });

        // Also include the selected top colour as part of the defaults sent in the custom event
        const topColour = document.querySelector('.obj-top-colour input[type="radio"]:checked').parentElement;

        // Extract the image file name from the selected top colour swatch to use as the default option value
        defaults.top = { 
            filename: this.getImageFileName(topColour.querySelector('.swatch')),
            swatchName: topColour.getAttribute('aria-label')?.trim()
        };

        // Other required layer data
        const otherLayers = {
            undercolour: 'black',
            profilecolour: 'cream',
            meshcolour: 'mesh'
        }

        // Combine defaults with other required layer data
        Object.assign(defaults, otherLayers);

        // Dispatch custom event with the default selections for base and edge groups based on the selected top colour
        const event = new CustomEvent('colourOptionsChanged', {
            detail: {
                defaults: defaults
            }
        });
        console.log('Dispatching colourOptionsChanged event with defaults:', defaults);
        window.dispatchEvent(event);
    }

    updateImages = () => {

        // Extract all currently selected layers from the DOM to send in the request to the server
        const selectedLayers = {
            top: document.querySelector('.obj-top-colour input[type="radio"]:checked')?.parentElement.getAttribute('aria-label')?.trim(),
            base: document.querySelector('.obj-base input[type="radio"]:checked')?.parentElement.getAttribute('aria-label')?.trim(),
            metal: document.querySelector('.obj-metal-edge-veneer input[type="radio"]:checked')?.parentElement.getAttribute('aria-label')?.trim()
        };

		// Send data via POST to your WP REST endpoint
        fetch('/wp-json/tmpc/v1/update-product-images', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                selectedLayers: selectedLayers,
                productID: TMPCPlugin.product_id
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);

        })
            .catch(err => {
            console.error('Failed to fetch colour options:', {
                error: err,
                url: url,
                product_id: TMPCPlugin.product_id,
                time: new Date().toISOString()
            });
        });

    }

    /**
     * Get the first available option from a list of swatches.
     * @param {NodeList} groupSwatches - The list of swatch elements.
     * @param {Array} availableList - The list of available option names.
     * @returns {HTMLElement|null} - The first available swatch element or null if none found.
     */
    getFirstAvailableOption = (groupSwatches, availableList) => {

        // Find the first available option in the DOM and select it
        return Array.from(groupSwatches).find(el => {

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
}

// Init class
window.addEventListener('DOMContentLoaded', () =>  new ColourOptions());