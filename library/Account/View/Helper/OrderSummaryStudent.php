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
 * View Helper to display an order summary for a student
 */

/**
 * @package Account
 */
class Account_View_Helper_OrderSummaryStudent extends Zend_View_Helper_Abstract
{
    protected $_html;

    // will take a product code
    public function orderSummaryStudent($accountDetails, $couponId = null)
    {
        $this->view->headLink()->appendStylesheet("/css/library/Account/View/Helper/order-summary-student.css");
        $this->view->headScript()->appendFile("/js/library/Account/View/Helper/order-summary-student.js");

        $coupon = \Fisdap\EntityUtils::getEntity("Coupon", $couponId);

        $this->_html = "<table class='summary-table'><tr>";

        // affiliation
        $this->_html .= '<td class="affiliationBox">'.
						    '<h3 class="section-header no-border">Affiliation</h3>' .
                            $accountDetails['cert'] .
						    ' Account at: <br />' .
                            $accountDetails['programName'] . '<br/>' .
                            $accountDetails['groupName'] . '<br/>' .
                            $accountDetails['graduationDate'] .
						'</td>';

        // products
        $this->_html .= '<td class="productsBox">'.
						    '<h3 class="section-header no-border">Products</h3>'.
						    '<a id="productDescriptionsLink" href="#">Product descriptions</a>'.
						    '<ul>';
        foreach ($accountDetails['products'] as $product) {
            $this->_html .= '<li>' . $product['name'] . '</li>';
        }
        $this->_html .= '</ul></td>';

        // cost/coupon
        $this->_html .= '<td class="costBox">
						<h3 class="section-header no-border">Cost</h3>
						<div>$<span id="cost">' . $accountDetails['cost'] . '</span></div>';
        $this->_html .= '<div id="couponWrapper">';
        $this->_html .= $this->view->couponBox(null, $coupon);
        $this->_html .= '</div></td>';

        $this->_html .= "</tr></table>";

        $this->_html .= $this->view->productDescriptionModal($accountDetails['products']);

        return $this->_html;
    }

}