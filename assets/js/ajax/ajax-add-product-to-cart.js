document.addEventListener('DOMContentLoaded', () => {
    
    const ajaxurl = '/wp-admin/admin-ajax.php';

    document.querySelectorAll('.single_add_to_cart_button').forEach(button => {
        const state = {
            isLoading: false,
            feedbackTimeout: null,
            defaultButtonText: button && button.textContent.trim()
                ? button.textContent.trim()
                : 'Add to Basket'
        };

        button.addEventListener('click', async (e) => {
            e.preventDefault();

            if (state.isLoading) {
                return;
            }

            if (state.feedbackTimeout) {
                clearTimeout(state.feedbackTimeout);
                state.feedbackTimeout = null;
            }

            button.textContent = state.defaultButtonText;
            setLoadingState(button, state, true);

            const productId = button.value || null;

             // Build current page URL with query params
            const currentUrl = window.location.href;

            // Get top colour from checked radio input
            const topColour = document.querySelector('.obj-top-colour input[type="radio"]:checked').value || '';

            // Get base colour from checked radio input
            const baseColour = document.querySelector('.obj-base input[type="radio"]:checked')?.value || '';

            // Get model size
            const model = document.querySelector('.obj-model select option:checked')?.value || '';

            // Get metal edge veneer value if set (optional field)
            const veneer = document.querySelector('.obj-metal-edge-veneer input[type="radio"]:checked')?.value || '';

            // Get quantity
            const quantity = document.querySelector('input.qty[name="quantity"]')?.value || 1;

            // Get current configuration image from DOM
            const imageUrl = getBasketImageUrl() || '';

            // Get configured total
            const configuredTotal = parseFloat(document.getElementById('configured-total')?.value.replace(/[^0-9.]/g, '') || 0);

            if (!productId || isNaN(productId)) {
                handleError(new Error('Invalid or missing product ID'), button, state);
                return;
            }

            // Build payload object dynamically
            const payload = {
                action: 'tm_add_to_cart', // Custom WooCommerce AJAX action
                product_id: productId,
                configured_total: configuredTotal,
                top_colour: topColour,
                base: baseColour,
                model: model,
                quantity: quantity,
                _tm_custom_product_url: currentUrl,
                img_url: imageUrl,
                note: 'Refunded with furniture purchase'
            };

            // Add veneer only if it exists
            if (veneer) {
                payload['metal_edge_veneer'] = veneer;
            }

            // Create FormData from payload
            const formData = new FormData();
            Object.entries(payload).forEach(([key, val]) => formData.append(key, val));

            try {
                const response = await fetch(ajaxurl, { method: 'POST', body: formData });
                const data = await response.json();
                handleAjaxResponse(data, button, state);
            } catch (error) {
                handleError(error, button, state);
            }
        });
    });

    function getBasketImageUrl() {

        // Get status image from DOM
        const statusImage = document.querySelector('.status-image img');

        // Remove -700 suffix and replace with -400 for basket image
        if (statusImage) {
            const src = statusImage.getAttribute('src');
            return src ? src.replace('-700', '-400') : '';
        }

        return '';

    }

    /**
     * Handles the WooCommerce AJAX response and updates the UI
     */
    function handleAjaxResponse(data, button, state) {
      if (data.success && data.data?.fragments) {
          console.log('Product added to cart successfully:', data.data.message);
          updateFragments(data.data.fragments);
          animateCartCounter();
          setLoadingState(button, state, false);
          showSuccessMessage(button, state);
          return;
      }

      throw new Error(data?.data?.message || 'Unknown error');
    }

    function setLoadingState(button, state, isLoading) {
        state.isLoading = isLoading;
        button.disabled = isLoading;
        button.classList.toggle('button-spinner', isLoading);
    }

    function showSuccessMessage(button, state) {
        button.textContent = 'Product added';

        state.feedbackTimeout = setTimeout(() => {
            button.textContent = state.defaultButtonText;
            state.feedbackTimeout = null;
        }, 1500);
    }

    function handleError(error, button, state) {
        console.error('AJAX cart error:', error);
        setLoadingState(button, state, false);
        button.textContent = 'Error';

        state.feedbackTimeout = setTimeout(() => {
            button.textContent = state.defaultButtonText;
            state.feedbackTimeout = null;
        }, 1500);
    }

    /**
     * Replace WooCommerce fragments dynamically in the DOM
     */
    function updateFragments(fragments) {
        for (const selector in fragments) {
            const html = fragments[selector];
            const target = document.querySelector(selector);

            if (target) {
                const temp = document.createElement('div');
                temp.innerHTML = html;
                const replacement = temp.querySelector(selector);
                if (replacement) target.replaceWith(replacement);
            }
        }
    }

    /**
     * Animate the cart counter after adding an item
     */
    function animateCartCounter() {
        const cartCounter = document.querySelector('span.header-items-count');
        if (cartCounter) {
            cartCounter.classList.add('cart-animate');
            cartCounter.addEventListener('animationend', function handler() {
                cartCounter.classList.remove('cart-animate');
                cartCounter.removeEventListener('animationend', handler);
            });
        }
    }
});