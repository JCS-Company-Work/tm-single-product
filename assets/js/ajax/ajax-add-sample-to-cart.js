class SampleAddToCart {

    constructor() {

        this.ajaxurl = '/wp-admin/admin-ajax.php';
        this.button = document.querySelector('.swatch-order-button');
        this.message = document.querySelector('.swatch-add-message');

        if (this.button) {
            this.button.addEventListener('click', (e) => this.handleClick(e));
        }
    }

    /**
     * Handle click event on "Add Sample to Cart" button
     * 
     * @param {object} e 
     * @returns 
     */
    handleClick(e) {

        // Prevent default form submission or link behavior
        e.preventDefault();

        // Initialize product IDs array
        let productIds = null;

        // Fetch data-sample-id from currently selected top and base
        const selectedTop = document.querySelector('input[name="top_colour"]:checked').getAttribute('data-sample-id');
        const selectedBase = document.querySelector('input[name="base_colour"]:checked').getAttribute('data-sample-id');

        // Check if top and base are the same, only pass through one ID if they are
        if (selectedTop === selectedBase) {
            productIds = [selectedTop];
        } else {
            productIds = [selectedTop, selectedBase];
        }

        // Validate selection
        if (!productIds || productIds.length === 0 || productIds.some(id => isNaN(id))) {
            this.message.style.color = 'red';
            this.message.textContent = 'Please select a swatch first.';
            return;
        }

        // Show loading message
        this.message.style.color = 'inherit';
        this.message.textContent = 'Adding to cart...';

        const formData = new FormData();
        formData.append('action', 'add_swatch_to_cart');
        formData.append('product_ids', JSON.stringify(productIds));

        fetch(this.ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => this.handleResponse(data))
        .catch(error => this.handleError(error));
    }

    /**
     * Handle response from AJAX request
     * 
     * @param {object} data 
     */
    handleResponse(data) {

        if (data.success && data.data && data.data.fragments) {

            // Update WooCommerce fragments
            for (const selector in data.data.fragments) {

                // Get the HTML for this fragment
                const html = data.data.fragments[selector];

                // Find the target element on the page
                const target = document.querySelector(selector);

                // If the target exists, replace it with the new HTML
                if (target) {

                    // Create a temporary container to parse the HTML
                    const temp = document.createElement('div');

                    // Set the HTML of the temporary container
                    temp.innerHTML = html;

                    // Find the replacement element within the temporary container
                    const replacement = temp.querySelector(selector);
                    if (replacement) target.replaceWith(replacement);

                } else if (selector === 'span.header-items-count') {

                    // If the selector is for the cart count, we need to update it within the cart link
                    const cartLink = document.querySelector('#site-header-cart a[href*="/basket"]');
                    
                    // If the cart link exists, update the cart count within it
                    if (cartLink) {
                    
                        // Create a temporary container to parse the HTML
                        const temp = document.createElement('div');
                    
                        // Set the HTML of the temporary container
                        temp.innerHTML = html;
                    
                        // Find the new cart count element within the temporary container
                        const newCount = temp.querySelector('span.header-items-count');
                    
                        // If the new cart count element exists, replace the old one in the cart link
                        if (newCount) cartLink.appendChild(newCount);

                    }
                }
            }

            // Animate cart counter for visual feedback
            this.animateCartCounter();

            // Show success message
            this.showSuccessMessage(data);
            
        } else {
            throw new Error(data.data || 'Unknown error');
        }
    }

    /**
     * Animate the cart counter to provide visual feedback when an item is added to the cart
     */
    animateCartCounter() {

        // Animate cart counter
        const cartCounter = document.querySelector('span.header-items-count');
        if (cartCounter) {
            cartCounter.classList.add('cart-animate');

            // Remove the class automatically when animation finishes
            cartCounter.addEventListener('animationend', function handler() {
                cartCounter.classList.remove('cart-animate');
                cartCounter.removeEventListener('animationend', handler);
            });
        }
    }

    /**
     * Show success message after adding to cart
     * 
     * @param {object} data 
     */
    showSuccessMessage(data) {

        // Success message
        this.message.style.color = 'green';
        this.message.textContent = data.data.message || 'Swatch added to cart!';

        // Clear message after 3 seconds
        this.clearMessage();

    }

    clearMessage() {

        // Clear message after 3 seconds
        setTimeout(() => {
            this.message.textContent = '';
        }, 3000);

    }

    /**
     * Handle errors from AJAX request
     * 
     * @param {object} error 
     */
    handleError(error) {
        console.error('AJAX error:', error);
        this.message.style.color = 'red';
        this.message.textContent = error.message || 'Something went wrong.';

        // Clear message after 3 seconds
        this.clearMessage();
    }

}

document.addEventListener('DOMContentLoaded', function () {
    new SampleAddToCart();
});