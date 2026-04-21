<?php

namespace TMProductConfigurator\Init;

if (!defined('ABSPATH')) exit;

class TMPC_Init {

    /**
     * Initialize all sub-classes
     */
    public static function init() {

        // Ensure classes exist before calling init
        if(class_exists('TMProductConfigurator\Ajax\TMPC_AJAX')) {
            \TMProductConfigurator\Ajax\TMPC_AJAX::init();
        }
        
        if(class_exists('TMProductConfigurator\Admin\TMPC_Admin')) {
            \TMProductConfigurator\Admin\TMPC_Admin::init();
        }

        if (class_exists('TMProductConfigurator\Assets\TMPC_Assets')) {
            \TMProductConfigurator\Assets\TMPC_Assets::init();
        }
        
        if (class_exists('TMProductConfigurator\ColourOptions\TMPC_ColourOptions')) {
            \TMProductConfigurator\ColourOptions\TMPC_ColourOptions::init();
        }
        
        if (class_exists('TMProductConfigurator\Images\TMPC_Images')) {
            \TMProductConfigurator\Images\TMPC_Images::init();
        }
        
        if (class_exists('TMProductConfigurator\Cart\TMPC_CartData')) {
            \TMProductConfigurator\Cart\TMPC_CartData::init();
        }

        if (class_exists('TMProductConfigurator\Cart\TMPC_CartImage')) {
            \TMProductConfigurator\Cart\TMPC_CartImage::init();
        }

        if (class_exists('TMProductConfigurator\Cart\TMPC_CartPrice')) {
            \TMProductConfigurator\Cart\TMPC_CartPrice::init();
        }

        if (class_exists('TMProductConfigurator\Cart\TMPC_CartUI')) {
            \TMProductConfigurator\Cart\TMPC_CartUI::init();
        }
        
        if (class_exists('TMProductConfigurator\Product\TMPC_CurrentStatus')) {
            \TMProductConfigurator\Product\TMPC_CurrentStatus::init();
        }
        
        if (class_exists('TMProductConfigurator\Product\TMPC_ModelSelection')) {
            \TMProductConfigurator\Product\TMPC_ModelSelection::init();
        }
    }
}