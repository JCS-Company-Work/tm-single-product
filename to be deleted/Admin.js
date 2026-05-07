class admin {

    constructor() {
        this.init();
    }

    init() {
       this.modelSizeInit();
       this.colourBoxInit(); 
    }

    /**
     * Initialize model size management functionality in admin product edit page, including:
     * - Adding new size rows with auto-populated dimensions and price based on selected size label
     * - Removing size rows
     * - Ensuring only one default size can be selected via radio buttons
     */
    modelSizeInit() {

        // Get initial index and size data from localized script
        let rowIdx = window.TMPC_MODEL_SIZE_DATA.rowIdx;
        
        // Get size data for dropdown options and auto-fill functionality
        const sizeData = window.TMPC_MODEL_SIZE_DATA.sizeData;
        
        // Extract just the labels for dropdown options
        const sizeOptions = sizeData.map(s => s.label);
        console.log('Model Size Data:', sizeData);

        // Add event listener to "Add Size" button
        const addSizeBtn = document.getElementById('tmpc-add-size');

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
                    <td><select name="tmpc_model_sizes[${rowIdx}][label]" class="tmpc-size-label">${opts}</select></td>
                    <td><input type="text" name="tmpc_model_sizes[${rowIdx}][dims]" class="tmpc-size-dims" readonly /></td>
                    <td><input type="number" name="tmpc_model_sizes[${rowIdx}][price]" class="tmpc-size-price" /></td>
                    <td>
                        <label class="tmpc-toggle-switch">
                            <input type="radio" name="tmpc_model_sizes_default" value="${rowIdx}" />
                            <span class="tmpc-slider"></span>
                        </label>
                    </td>
                    <td><button type="button" class="button tmpc-remove-size">Remove</button></td>
                </tr>`;

                // Append new row to table
                $('#tmpc-model-sizes-table tbody').append(newRow);

                // Increment index for next row
                rowIdx++;

            });
        }

        // Delegate event listener for dynamically added "Remove"
        const removeSizeBtn = document.querySelector('.tmpc-remove-size');

        if(removeSizeBtn) {
            // Remove size row
            removeSizeBtn.addEventListener('click', function(){
                this.closest('tr').remove();
            });
        }

        // Auto-fill dims and price based on selected label
        document.addEventListener('change', function(e) {
            if (e.target && e.target.classList.contains('tmpc-size-label')) {
                const label = e.target.value;
                const row = e.target.closest('tr');
                const found = sizeData.find(s => s.label === label);
                if (found) {
                    row.querySelector('.tmpc-size-dims').value = found.dims;
                    row.querySelector('.tmpc-size-price').value = found.price;
                } else {
                    row.querySelector('.tmpc-size-dims').value = '';
                    row.querySelector('.tmpc-size-price').value = '';
                }
            }
            // Only one radio can be checked
            if (
                e.target &&
                e.target.type === 'radio' &&
                e.target.name === 'tmpc_model_sizes_default'
            ) {
                const radios = document.querySelectorAll('input[type=radio][name=tmpc_model_sizes_default]');
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
        const data = window.TMPC_COLOURS;
        const saved = window.TMPC_SAVED;

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
            values.forEach(val => {

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
            select.style.display = '';

        };

        // Update base and metal options when top colour changes, and optionally restore saved selections on load
        const updateOptions = (selectedTop, restore = false) => {

            // Hide base and metal selects until we know if there are options to show
            baseSelect.style.display = 'none';
            metalSelect.style.display = 'none';

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
        }
    }
}

window.addEventListener('DOMContentLoaded', () => {
    new admin();
});