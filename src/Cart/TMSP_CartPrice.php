<?php

namespace TMSingleProduct\Cart;

/**
 * Class TMSP_CartPrice
 *
 * Ensures custom ex-VAT unit prices are passed into WooCommerce cart items.
 * Once the unit price is set on the cart product object, WooCommerce's own
 * tax system and totals logic handle all calculations automatically.
 *
 * @package TMSingleProduct
 */
class TMSP_CartPrice
{
    /**
     * Initialize cart price adjustments.
     *
     * Hooks into WooCommerce filters to inject our custom unit price.
     */
    public static function init()
    {
        // Inject ex-VAT unit price into cart item data.
        add_filter('woocommerce_add_cart_item', [__CLASS__, 'tm_set_cart_item_price'], 20, 1);
    }

    /**
     * Set custom unit price for cart items.
     *
     * - If the cart item contains a 'grand_total' value (our ex-VAT total),
     *   we convert it into a per-unit price and assign it to the product object.
     * - WooCommerce then uses this price to calculate all line totals,
     *   cart subtotals, and the final order total with correct VAT handling.
     *
     * @param array $cart_item The cart item data.
     * @return array Modified cart item data with custom price set.
     */
    public static function tm_set_cart_item_price($cart_item)
    {
        if (!empty($cart_item['grand_total']) && !has_term('swatch', 'product_cat', $cart_item['product_id'])) {
            $qty = max(1, (int)$cart_item['quantity']);

            // Extract numeric value from 'grand_total' (strip HTML, symbols).
            $unit_price = floatval(preg_replace('/[^\d.]/', '', strip_tags($cart_item['grand_total']))) / $qty;

            // Apply custom ex-VAT price to the cart product object.
            $cart_item['data']->set_price($unit_price);

            // Store for debugging/reference.
            $cart_item['unit_price'] = $unit_price;
        }

        return $cart_item;
    }
}