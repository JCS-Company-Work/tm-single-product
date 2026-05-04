document.addEventListener('DOMContentLoaded', () => {
    
    const ajaxurl = '/wp-admin/admin-ajax.php';

    document.querySelectorAll('.single_add_to_cart_button').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();

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
                console.warn('Invalid or missing product ID');
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
                handleAjaxResponse(data, button);
            } catch (error) {
                console.error('AJAX cart error:', error);
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
    function handleAjaxResponse(data, button) {
      const container = button.closest('.playground-add-to-cart');
      if (!container) return;

      // Ensure message container exists
      let messageDiv = container.nextElementSibling;
      if (!messageDiv || !messageDiv.classList.contains('ajax-add-to-cart-message')) {
          messageDiv = document.createElement('div');
          messageDiv.className = 'ajax-add-to-cart-message';
          messageDiv.style.marginTop = '10px';
          messageDiv.style.minHeight = '1.5em'; // Reserve space to prevent layout shift
          messageDiv.style.transition = 'opacity 0.5s ease';
          messageDiv.style.opacity = '0';       // Start hidden
          messageDiv.style.visibility = 'hidden';
          container.insertAdjacentElement('afterend', messageDiv);
      }


      // Reset opacity in case it's mid-transition
      messageDiv.style.opacity = '1';
      messageDiv.style.display = 'block';

      if (data.success && data.data?.fragments) {
          updateFragments(data.data.fragments);
          animateCartCounter();
          messageDiv.style.color = 'green';
          messageDiv.textContent = data.data.message || 'Product added to cart!';
      } else {
          messageDiv.style.color = 'red';
          messageDiv.textContent = data.data?.message || 'Error adding product to cart.';
      }

      // Show message
      messageDiv.style.opacity = '1';
      messageDiv.style.visibility = 'visible';

      // Hide after 2 seconds
      setTimeout(() => {
          messageDiv.style.opacity = '0';
          messageDiv.style.visibility = 'hidden';
      }, 2000);

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