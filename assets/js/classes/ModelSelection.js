class ModelSelection {

  constructor() {

    // Select content area from DOM and make globally available
    this.contentArea = document.querySelector('.content-area');

    // Property to hold the selected model
    this.modelValue = null;

    this.init();
  }

  init() {

    // Activate page load functions
    this.modelCheck();
    this.addParaClass();
    this.updateOnChange();

  }

  /**
   * check for model in url on load or use default from HTML if none found
   */
  modelCheck() {

    // Get the current URL
    const urlParams = new URLSearchParams(window.location.search);

    // Check if model param exists
    if (urlParams.has('model')) {

        // Update modelValue class property
        this.modelValue = urlParams.get('model');
       
    } else {

        // Else select default value from HTML
        const defaultModel = document.querySelector('.obj-model option[selected]');

        // Update modelValue class property
        this.modelValue = defaultModel.getAttribute('data-wapf-label').trim();
    }

    // Add final class value to content area div
    this.updateContentArea();

  }

  /**
   *  add change listener to model selection dropdown and update .content-area on change
   */
  updateOnChange() {

    const selectEl = document.querySelector('.obj-model select');

    selectEl.addEventListener('change', (event) => {

        // event.target is the <select> element
        const selectedOption = event.target.selectedOptions[0]; // the selected <option>

        // Get text (e.g., "250cm") instead of value
        const newModel = selectedOption.getAttribute('data-wapf-label');

        // Remove existing class from content-area and paragraphs
        this.contentArea.classList.remove(`model-${this.modelValue}`);

        // Update the class property
        this.modelValue = newModel;

        // Add final class value to content area div
        this.updateContentArea();

    })

  }

  /**
   * Method to update content-area
   */
  updateContentArea() {

    this.contentArea.classList.add(`model-${this.modelValue}`);

  }

  /**
   * Method to add class name to paras on page load
   */
  addParaClass() {

    // Select specification paras from DOM
    const specParas = document.querySelectorAll('.woocommerce-product-attributes-item--attribute_specifications p');

    specParas.forEach(para => {

        // Get first five characters of paragraph text
        const paraStart = para.textContent.trim().substring(0, 5);

        // Add classlist to para
        para.classList.add(`model-${paraStart}`);

    });

  }
  
}

// Initialize once, after DOM ready
document.addEventListener("DOMContentLoaded", () => {
    new ModelSelection();
});