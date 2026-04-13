<?php

namespace TMProductConfigurator\Product;

class TMPC_ModelSelection {

    public static function init() {

        // Add ex-VAT price to model selection dropdown options
        add_filter('wapf/html/option_attributes', [__CLASS__, 'add_ex_vat_price'], 10, 4);

        // add to field-container class list
        add_filter( 'wapf/html/field_container_classes', [__CLASS__, 'add_field_container_classes'], 10, 2 );

    }

    /**
     * Use WAPF hooks to add ex-vat cost to model option HTML
     * Allows us to serve ex-VAT costs to Woocommerce and allow
     * it to handle taxation for us
     * 
     * @param object $attributes
     * @param object $field
     * @param object $product
     * @param object $option
     * @return void
     */
    public static function add_ex_vat_price($attributes, $field, $product, $option) {

        // Only amend attributes where element has a price
        if(!empty($attributes['data-wapf-price'])) {

            // Add the ex-VAT price to HTML
            $attributes['data-ex-vat'] = floatval($option['pricing_amount']);

        }

        return $attributes;

    }

     /**
     * Add custom CSS classes to WAPF field containers based on their label.
     *
     * Converts the field label into a slug format (`obj-field-label`) and appends it
     * to the container's class list. For example, a label "Top Colour" becomes "obj-top-colour".
     *
     * @param array        $classes Array of existing CSS classes applied to the field container.
     * @param object|array $field   Field object (or array) containing at least the `label` property.
     *
     * @return array Modified array of classes including the label-based slug.
     */
    public static function add_field_container_classes( $classes, $field ) {
	
        // Use null coalescing to avoid errors
        $label = $field->label ?? '';
        
        // convert field label to 'obj-field-label' format
        $parts = array_filter( explode(' ', strtolower($label)) );
        $label_slug = 'obj-' . implode('-', $parts);
        
        // add to main class list and return 
        $classes[] = $label_slug;
        return $classes;
        
    }

}