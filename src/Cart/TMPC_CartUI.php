<?php

namespace TMProductConfigurator\Cart;

class TMPC_CartUI
{
    public static function init()
    
    {

        add_filter('woocommerce_add_to_cart_fragments', [__CLASS__, 'tm_update_cart_fragments'], 10, 3);
        
    }

    /**
     * Update mini-cart fragments
     */
    public static function tm_update_cart_fragments($fragments) {
        ob_start(); ?>
        <ul id="site-header-cart" class="site-header-cart">
            <li class="cart-toggle" style="position:relative;">
                <a class="cart-contents" href="<?php echo esc_url(wc_get_cart_url()); ?>">
                    <i class="fa-light fa-basket-shopping-simple"></i>
                    <span class="mobilehide"> ITEMS </span>
                    <span class="header-items-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
                </a>
                <div class="widget_shopping_cart_content">
                    <?php woocommerce_mini_cart(); ?>
                </div>
            </li>
        </ul>
        <?php
        $fragments['#site-header-cart'] = ob_get_clean();

        ob_start(); ?>
        <span class="header-items-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
        <?php
        $fragments['.header-items-count'] = ob_get_clean();

        return $fragments;
    }

}
