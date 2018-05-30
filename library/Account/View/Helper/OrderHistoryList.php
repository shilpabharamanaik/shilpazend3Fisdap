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
 * View Helper to display an order history list
 */

/**
 * @package Account
 */
class Account_View_Helper_OrderHistoryList extends Zend_View_Helper_Abstract
{
    protected $_html;
    
    public function orderHistoryList($programId, $filters = array())
    {
        $this->view->headLink()->appendStylesheet("/css/library/Account/View/Helper/order-history-list.css");
        $this->view->headLink()->appendStylesheet("/css/fisdap_dialogs.css");
        $this->view->headScript()->appendFile("/js/library/Account/View/Helper/order-history-list.js");
        
        $orders = \Fisdap\EntityUtils::getRepository("Order")->getOrders($programId, $filters);
        foreach ($orders as $order) {
            $orderPartials[] = array('order' => $order);
        }
        
        $this->_html = "<table class='fisdap-table' id='order-history-list'>";
        $this->_html .= "<thead>";
        $this->_html .= "<tr><th>Date</th><th>Quantity</th><th>Cost</th><th>Ordered By</th><th>Order #</th></tr>";
        $this->_html .= "</thead>";
        $this->_html .= "<tbody>";
        
        $this->_html .= $this->view->partialLoop("orderHistoryCell.phtml", $orderPartials);
        
        $this->_html .= "</tbody>";
        $this->_html .= "</table>";
        
        return $this->_html;
    }
}
