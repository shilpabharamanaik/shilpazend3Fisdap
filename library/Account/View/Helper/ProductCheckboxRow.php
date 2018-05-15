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
 * View Helper to display a checkbox row within a form for a product
 */

/**
 * @package Account
 */
class Account_View_Helper_ProductCheckboxRow extends Zend_View_Helper_Abstract
{
    protected $_html;
    
    public function productCheckboxRow($product, $moreAttempts = false)
    {
        $price = number_format($product->getDiscountedPrice(), 2, ".", ",");

        $this->_html = "<tr class='product-".$product->id."-row'>";

        $this->_html .= "<td class='checkbox'>".
                            "<input id='product-".$product->id."'
                            type='checkbox' value='".$product->configuration."'
                            name='products[]' data-price='".$price."'
                            data-config='".$product->configuration."'>".
                        "</td>";

        $this->_html .= "<td class='name'><label for='product-".$product->id."'>".$product->name;
        if ($moreAttempts) {
            $this->_html .= " (buy more attempts)";
        }
        $this->_html .= "</label></td>";

        $this->_html .= "<td class='price'>$$price</td>";

        $this->_html .= "</tr>";
        
        return $this->_html;
    }
}
