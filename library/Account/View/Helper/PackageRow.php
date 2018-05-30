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
 * View Helper to display an input row within a form for a package package
 */

/**
 * @package Account
 */
class Account_View_Helper_PackageRow extends Zend_View_Helper_Abstract
{
    protected $_html;

    public function packageRow($package, $inputType = "radio")
    {
        $price = number_format($package->getDiscountedPrice(), 2, ".", ",");
        $savings = number_format(($package->getFullPrice() - $package->getDiscountedPrice()), 2, ".", ",");

        $this->_html = "<tr class='package-" . $package->id . "-row'>";
        $this->_html .= "<td class='checkbox' rowspan='2'>" .
            "<input id='package-" . $package->id . "'
            type='$inputType' value='" . $package->configuration . "'
            name='packages[]' data-price='" . $price . "' data-config='" . $package->configuration . "'>" .
            "</td>";
        $this->_html .= "<td class='name'><label for='package-" . $package->id . "'>" . $package->name . "</label></td>";
        $this->_html .= "<td class='price'>$$price</td>";
        $this->_html .= "</tr>";

        // add the product description
        $this->_html .= "<tr class='bottom-row package-" . $package->id . "-row'>";
        $this->_html .= "<td class='form-desc'>" . $package->getProductDescription(true) . "</td>";
        $this->_html .= "<td class='price green'>Save $$savings</td>";
        $this->_html .= "</tr>";

        return $this->_html;
    }
}
