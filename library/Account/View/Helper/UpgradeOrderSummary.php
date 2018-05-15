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
 * View Helper to display a basic upgrade order summary
 */

/**
 * @package Account
 */
class Account_View_Helper_UpgradeOrderSummary extends Zend_View_Helper_Abstract
{
    protected $html;

    public function upgradeOrderSummary($products, $programId = null)
    {
        $this->html = "<table class='upgrade-order-summary'>";

        foreach ($products as $product) {
            $price = number_format($product->getDiscountedPrice($programId), 2, ".", ",");

            $this->html .= "<tr>";
            $this->html .= "<td>".$product->name."</td>";
            $this->html .= "<td>$$price</td>";
            $this->html .= "</tr>";
        }

        $this->html .= "</table>";

        return $this->html;
    }
}
