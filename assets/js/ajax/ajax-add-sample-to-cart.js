document.addEventListener('DOMContentLoaded', function () {
    const ajaxurl = '/wp-admin/admin-ajax.php';

    const button = document.querySelector('.product-add-sample-button');
    const select = document.querySelector('.product-add-sample-select');
    const message = document.querySelector('.swatch-add-message');

    if (!button || !select) return;

    button.addEventListener('click', function (e) {
        e.preventDefault();

        const productId = select.value;

        // Validate selection
        if (!productId || isNaN(productId)) {
            message.style.color = 'red';
            message.textContent = 'Please select a swatch first.';
            return;
        }

        // Show loading message
        message.style.color = 'inherit';
        message.textContent = 'Adding to cart...';

        const formData = new FormData();
        formData.append('action', 'add_swatch_to_cart');
        formData.append('product_id', productId);

        // Check optional checkbox
        const metalCheckbox = document.getElementById('metal-edge-checkbox');
        if (metalCheckbox?.checked) {
            formData.append('metal-edge-checkbox', 'Metal Edge Veneer Included');
        }

        fetch(ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data && data.data.fragments) {
                // Update WooCommerce fragments
                for (const selector in data.data.fragments) {
                    const html = data.data.fragments[selector];
                    const target = document.querySelector(selector);

                    if (target) {
                        const temp = document.createElement('div');
                        temp.innerHTML = html;
                        const replacement = temp.querySelector(selector);
                        if (replacement) target.replaceWith(replacement);
                    } else if (selector === 'span.header-items-count') {
                        const cartLink = document.querySelector('#site-header-cart a[href*="/basket"]');
                        if (cartLink) {
                            const temp = document.createElement('div');
                            temp.innerHTML = html;
                            const newCount = temp.querySelector('span.header-items-count');
                            if (newCount) cartLink.appendChild(newCount);
                        }
                    }
                }

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

                // Success message
                message.style.color = 'green';
                message.textContent = data.data.message || 'Swatch added to cart!';
            } else {
                throw new Error(data.data || 'Unknown error');
            }
        })
        .catch(error => {
            console.error('AJAX error:', error);
            message.style.color = 'red';
            message.textContent = error.message || 'Something went wrong.';
        });
    });
});