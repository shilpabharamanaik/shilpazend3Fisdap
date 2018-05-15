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
 * View Helper to display an order summary table
 */

/**
 * @package Account
 */
class Account_View_Helper_OrderSummaryTable extends Zend_View_Helper_Abstract
{
    protected $_html;

    public function orderSummaryTable($orderId, $active = true)
    {
        $this->view->headLink()->appendStylesheet("/css/library/Account/View/Helper/order-summary-table.css");
        $this->view->headScript()->appendFile("/js/library/Account/View/Helper/order-summary-table.js");

        $order = \Fisdap\EntityUtils::getEntity('Order', $orderId);

        $orderPartials = array();
        foreach ($order->order_configurations as $config) {
            $orderPartials[] = array('orderConfig' => $config, 'active' => $active);
        }

        $tableClass = ($active) ? "" : "static";
        $this->_html = "<table class='order-summary $tableClass'>";
        $this->_html .= "<tbody>";

        $this->_html .= $this->view->partialLoop("orderSummaryCell.phtml", $orderPartials);

        $this->_html .= "</tbody>";
        $this->_html .= "</table>";

        return $this->_html;
    }
}
