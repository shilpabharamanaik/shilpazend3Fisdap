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
 * View Helper to display a summary row within a table for a product
 */

/**
 * @package Account
 */
class Account_View_Helper_ProductSummaryRow extends Zend_View_Helper_Abstract
{
    protected $_html;

    public function productSummaryRow($productInfo, $showPrice)
    {
        $product = $productInfo['product'];
        $include = $productInfo['upgradeable'];
        $moreAttempts = $productInfo['attempts'];

        $icon_src = ($include) ? "/images/icons/check.svg" : "/images/icons/x-circle.svg";
        $icon_class = ($include) ? "include" : "exclude";

        $price = number_format($product->getDiscountedPrice(), 2, ".", ",");
        $discountedPrice = (!$include && $showPrice) ? "discounted" : "";
        $includeRow = ($include) ? "include" : "";

        $this->_html = "<tr class='product-row $includeRow' data-price='$price' data-config='".$product->configuration."'>";
        $this->_html .= "<td class='icon' rowspan='2'><img class='icon $icon_class' src='$icon_src'></td>";
        $this->_html .= "<td class='name'><label for='product-" . $product->id . "'>" . $product->name . "</label></td>";
        $this->_html .= "<td class='price $discountedPrice'>";
        if ($showPrice) {
            $this->_html .= "$$price";
        }
        $this->_html .= "</td>";
        $this->_html .= "</tr>";

        if ($moreAttempts) {
            $this->_html .= "<tr><td class='form-desc' colspan='2'>You will be adding 2 additional attempts for each exam.</td></tr>";
        } else if (!$include) {
            $this->_html .= "<tr><td class='form-desc' colspan='2'>Looks like you already have this product, so we don't need to add it to your account.</td></tr>";
        } else {
            $this->_html .= "<tr><td></td></tr>";
        }

        return $this->_html;
    }
}