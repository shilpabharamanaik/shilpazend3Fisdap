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
 * View Helper to display a categorized upgrade summary
 */

/**
 * @package Account
 */
class Account_View_Helper_UpgradeSummary extends Zend_View_Helper_Abstract
{
    protected $_html;

    public function upgradeSummary($addedProducts, $showPrice = false)
    {
        $this->_html = "<h4 class='section-header no-border'>Your account will be upgraded to include:</h4>";

        $this->_html .= $this->view->partial(
            "upgradeProductSummary.phtml",
            array("products" => $addedProducts,
                "showPrice" => $showPrice));

        return $this->_html;
    }
}