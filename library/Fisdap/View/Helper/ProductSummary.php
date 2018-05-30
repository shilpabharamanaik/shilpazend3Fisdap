<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2014.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * View Helper to display a pretty list of products
 */

/**
 * @package Fisdap
 */
class Fisdap_View_Helper_ProductSummary extends Zend_View_Helper_Abstract
{
    protected $_html;

    public $view;

    public function __construct($view = null)
    {
        if ($view) {
            $this->view = $view;
        }
    }

    public function productSummary($products, $student = false)
    {
        $this->_html = "<div class='products-container'>";

        foreach ($products as $product) {
            // don't show preceptor training or pilot testing to students
            if ($student && ($product->id == 9 || $product->id == 12)) {
                continue;
            }

            $this->_html .= "<div class='product'>";
            $this->_html .= "<img class='product-shield' src='/images/product-icons/product-icon-" . $product->configuration . ".svg'>";
            $this->_html .= "<div class='shield-label'>" . $product->name . "</div>";
            $this->_html .= "</div>";
        }

        $this->_html .= "</div>";

        return $this->_html;
    }
}
