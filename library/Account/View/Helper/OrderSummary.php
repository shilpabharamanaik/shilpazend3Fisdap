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
 * View Helper to display an order summary
 */

/**
 * @package Account
 */
class Account_View_Helper_OrderSummary extends Zend_View_Helper_Abstract
{
    protected $_html;
    
    public function orderSummary($orderId, $mode = "serialNumber")
    {
        $this->view->headLink()->appendStylesheet("/css/library/Account/View/Helper/order-summary.css");
        
        $order = \Fisdap\EntityUtils::getEntity('Order', $orderId);
        
        $this->_html = "<a id='cancel-order' href='/account/orders/cancel-order'>Cancel Order</a>";
        
        $this->_html .= $this->view->orderSummaryTable($order);
        
        //Add a cost summary if the program is paying
        if ($order->order_type->id == 1) {
            $this->_html .= $this->view->partial("orderCostSummary.phtml", array("order" => $order));
        }
        
        return $this->_html;
    }
}
