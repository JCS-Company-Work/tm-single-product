<?php

namespace TMSingleProduct\Init;

if (!defined('ABSPATH')) exit;

class TMSP_Init {

    /**
     * Initialize all sub-classes
     */
    public static function init() {
        
        if(class_exists('TMSingleProduct\Admin\TMSP_Admin')) {
            \TMSingleProduct\Admin\TMSP_Admin::init();
        }
        
        if(class_exists('TMSingleProduct\Admin\TMSP_Options')) {
            \TMSingleProduct\Admin\TMSP_Options::init();
        }

        if (class_exists('TMSingleProduct\Overrides\TMSP_LayoutOverrides')) {
            \TMSingleProduct\Overrides\TMSP_LayoutOverrides::init();
        }

        if (class_exists('TMSingleProduct\Email\TMSP_Email')) {
            \TMSingleProduct\Email\TMSP_Email::init();
        }

        if (class_exists('TMSingleProduct\Gallery\TMSP_Gallery')) {
            \TMSingleProduct\Gallery\TMSP_Gallery::init();
        }
        
        if (class_exists('TMSingleProduct\Gallery\TMSP_GalleryAssets')) {
            \TMSingleProduct\Gallery\TMSP_GalleryAssets::init();
        }
        
        if (class_exists('TMSingleProduct\Cart\TMSP_CartData')) {
            \TMSingleProduct\Cart\TMSP_CartData::init();
        }

        if (class_exists('TMSingleProduct\Cart\TMSP_CartImage')) {
            \TMSingleProduct\Cart\TMSP_CartImage::init();
        }

        if (class_exists('TMSingleProduct\Cart\TMSP_CartPrice')) {
            \TMSingleProduct\Cart\TMSP_CartPrice::init();
        }

        if (class_exists('TMSingleProduct\Cart\TMSP_CartUI')) {
            \TMSingleProduct\Cart\TMSP_CartUI::init();
        }
        
        if (class_exists('TMSingleProduct\Product\TMSP_ProductSummary')) {
            \TMSingleProduct\Product\TMSP_ProductSummary::init();
        }
    }
}