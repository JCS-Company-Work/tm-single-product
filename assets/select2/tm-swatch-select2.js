jQuery(document).ready(function($) {

    // Function to initialize Select2 with thumbnails
    function initSwatchSelect() {
        const $select = $('#swatch-dropdown');

        if ($select.length && !$select.hasClass('select2-hidden-accessible')) {
            $select.select2({
                width: '100%',
                dropdownParent: $('body'),
                dropdownPosition: 'below',
                templateResult: function(option) {
                    if (!option.id) return option.text; // Default text for placeholder

                    const thumb = $(option.element).data('thumb');
                    if (thumb) {
                        return $(
                            '<span><img class="select2-swatch" src="' + thumb + '" />' + option.text + '</span>'
                        );
                    }
                    return option.text;
                },
                templateSelection: function(option) {
                    return option.text;
                }
            });
        }
    }

    // Initialize on page load
    initSwatchSelect();

    // Re-initialize after WooCommerce updates fragments
    $(document.body).on('updated_wc_div updated_cart_totals updated_checkout', function() {
        initSwatchSelect();
    });

});