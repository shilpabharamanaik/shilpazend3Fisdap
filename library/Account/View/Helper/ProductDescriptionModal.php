<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * View Helper to display a modal with info about a given set of products
 */

/**
 * @package Account
 */
class Account_View_Helper_ProductDescriptionModal extends Zend_View_Helper_Abstract
{
    protected $_html;

    // will take an array of products
    public function productDescriptionModal($products)
    {
        $this->view->headScript()->appendFile("/js/library/Account/View/Helper/product-description-modal.js");

        $this->_html = '<div id="productDescriptionsDialog">';

        foreach ($products as $product) {
            $this->_html .= '<h4 class="header">' . $product['name'] . '</h4>' .
                '<div class="section-body">' . $product['description'] . '</div>';
        }

        $this->_html .= '</div>';

        return $this->_html;
    }
}