class admin {

    constructor() {
        this.init();
    }

    init() {
       this.modelSizeInit();
       this.colourBoxInit(); 
       this.modelRemove();
    }

    /**
     * Initialize model size management functionality in admin product edit page, including:
     * - Adding new size rows with auto-populated dimensions and price based on selected size label
     * - Removing size rows
     * - Ensuring only one default size can be selected via radio buttons
     */
    modelSizeInit() {

        // Get initial index and size data from localized script
        let rowIdx = window.TMPA_MODEL_SIZE_DATA.rowIdx;
        
        // Get size data for dropdown options and auto-fill functionality
        const sizeData = window.TMPA_MODEL_SIZE_DATA.sizeData;
        
        // Extract just the labels for dropdown options
        const sizeOptions = sizeData.map(s => s.label);

        // Add event listener to "Add Size" button
        const addSizeBtn = document.getElementById('tmpa-add-size');

        // Check if button exists before adding event listener
        if(addSizeBtn) {

            addSizeBtn.addEventListener('click', function(e) {

                // Prevent default button action
                e.preventDefault();

                // Build options for size label dropdown
                let opts = '<option value="">Select size</option>';
                sizeOptions.forEach(function(label){
                    opts += `<option value="${label}">${label}</option>`;
                });

                // Append new row with dropdown and readonly fields for dims/price that auto-populate based on selected label
                let newRow = `<tr>
                    <td><select name="tmpa_model_sizes[${rowIdx}][label]" class="tmpa-size-label">${opts}</select></td>
                    <td><input type="text" name="tmpa_model_sizes[${rowIdx}][dims]" class="tmpa-size-dims" readonly /></td>
                    <td><input type="number" name="tmpa_model_sizes[${rowIdx}][price]" class="tmpa-size-price" /></td>
                    <td>
                        <label class="tmpa-toggle-switch">
                            <input type="radio" name="tmpa_model_sizes_default" value="${rowIdx}" />
                            <span class="tmpa-slider"></span>
                        </label>
                    </td>
                    <td><button type="button" class="button tmpa-remove-size">Remove</button></td>
                </tr>`;

                // Append new row to table (vanilla JS)
                const tbody = document.querySelector('#tmpa-model-sizes-table tbody');
                if (tbody) {
                    tbody.insertAdjacentHTML('beforeend', newRow);
                }

                // Increment index for next row
                rowIdx++;

            });
        }

        // Auto-fill dims and price based on selected label
        this.modelDimsAutoFill();
    }

    /**
     * Delegate event listener for changes to size label dropdowns to auto-fill dimensions 
     * and price fields based on selected label, using data from localized script. Also ensures 
     * only one default size can be selected via toggle buttons.
     */
    modelDimsAutoFill() {

        // Auto-fill dims and price based on selected label
        document.addEventListener('change', function(e) {
            if (e.target && e.target.classList.contains('tmpa-size-label')) {
                const label = e.target.value;
                const row = e.target.closest('tr');
                const found = sizeData.find(s => s.label === label);
                if (found) {
                    row.querySelector('.tmpa-size-dims').value = found.dims;
                    row.querySelector('.tmpa-size-price').value = found.price;
                } else {
                    row.querySelector('.tmpa-size-dims').value = '';
                    row.querySelector('.tmpa-size-price').value = '';
                }
            }
            // Only one radio can be checked
            if (
                e.target &&
                e.target.type === 'radio' &&
                e.target.name === 'tmpa_model_sizes_default'
            ) {
                const radios = document.querySelectorAll('input[type=radio][name=tmpa_model_sizes_default]');
                radios.forEach(radio => {
                    if (radio !== e.target) radio.checked = false;
                });
            }
        });
    }

    /**
     * Initialize colour box functionality in admin product edit page, including:
     * - Populating base and metal colour options based on selected top colour using data from localized script
     * - Restoring saved selections on page load
     */
    colourBoxInit() {

        // Get colour data and saved selections from localized script
        const data = window.TMPA_COLOURS;
        const saved = window.TMPA_SAVED;
        const baseType = window.TMPA_BASE_TYPE;

        // Get select elements
        const topSelect   = document.getElementById('top-colour');
        const baseSelect  = document.getElementById('base-colour');
        const metalSelect = document.getElementById('metal-colour');

        // Helper to capitalise words for display
        const capitaliseWords = str =>
            str.replace(/\b\w/g, char => char.toUpperCase());

        // Helper to normalize top colour key (lowercase, spaces to underscores)
        const normaliseTopKey = str => str.toLowerCase().replace(/\s+/g, '_');

        // Populate select options based on provided values and placeholder, and optionally set selected value
        const populate = (select, values, placeholder, selectedValue = '') => {
            
            // Clear existing options and add placeholder
            select.innerHTML = `<option value="">${placeholder}</option>`;

            // If no values provided, leave select with just placeholder
            if (!values) return;

            // Add new options from values array
            values[baseType].forEach(val => {

                // Create option element
                const opt = document.createElement('option');
                
                // Set option value and display text
                opt.value = val;
                
                // Capitalise words for display (e.g. "jet_black" to "Jet Black")
                opt.textContent = capitaliseWords(val);

                // If this value matches the saved selection, mark it as selected
                if (val === selectedValue) {
                    opt.selected = true;
                }

                // Append option to select
                select.appendChild(opt);

            });

            // Make select visible now that it's populated
            select.classList.remove('tmpa-hide');
            select.style.display = 'inline-block';

        };

        // Update base and metal options when top colour changes, and optionally restore saved selections on load
        const updateOptions = (selectedTop, restore = false) => {

            // Hide base and metal selects until we know if there are options to show
            baseSelect.classList.add('tmpa-hide');
            metalSelect.classList.add('tmpa-hide');

            // If no top colour selected, we can't show any options
            if (!selectedTop) return;

            // Normalize selectedTop to match data keys
            const key = normaliseTopKey(selectedTop);
            if (!data[key]) return;

            const config = data[key];

            // Populate base options, using saved selection if restoring
            populate(
                baseSelect,
                config.base,
                'Select base',
                restore ? saved.base : ''
            );

            if (config.metal) {
                // Populate metal options, using saved selection if restoring
                populate(
                    metalSelect,
                    config.metal,
                    'Select metal',
                    restore ? saved.metal : ''
                );
            }
        };

        // Change handler
        topSelect.addEventListener('change', () => {
            updateOptions(topSelect.value);
        });

        // Restore saved state on load
        if (saved.top) {
            topSelect.value = saved.top;
            updateOptions(saved.top, true);
        } else {
            // If no saved top colour, ensure base and metal selects are hidden
            baseSelect.classList.add('tmpa-hide');
            metalSelect.classList.add('tmpa-hide');
        }

    }

    /**
     * Delegate event listener for dynamically added "Remove" buttons to remove size rows from the model sizes table
     */
    modelRemove() {

        // Event delegation for dynamically added "Remove" buttons
        const tbody = document.querySelector('#tmpa-model-sizes-table tbody');
        if (tbody) {
            tbody.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('tmpa-remove-size')) {
                    e.target.closest('tr').remove();
                }
            });
        }
    }
}

window.addEventListener('DOMContentLoaded', () => {
    new admin();
});