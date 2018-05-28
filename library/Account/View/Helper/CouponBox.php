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
 * This helper will display a coupon box
 */

/**
 * @package Account
 */
class Account_View_Helper_CouponBox extends Zend_View_Helper_Abstract 
{
	/**
	 * @var string the html to be rendered
	 */
	protected $_html;
	
	/**
	 * @param \Fisdap\Entity\Coupon $coupon
	 * @return string the shift list rendered as an html table
	 */
	public function couponBox($order = null, $coupon = null)
    {
		$this->view->headLink()->appendStylesheet("/css/library/Account/View/Helper/coupon-box.css");
        $this->view->headScript()->appendFile("/js/library/Account/View/Helper/coupon-box.js");
		
		if ($order->id) {
			$coupon = $order->coupon;
		}
		
        $this->_html = "<div id='couponBox'>";
        $this->_html .= $this->view->formLabel("coupon", "Coupon Code:");
        $this->_html .= $this->view->formText("coupon");
        $this->_html .= "<div class='extra-small green-buttons'><a href='#' id='apply'>Apply</a></div>";
        $this->_html .= $this->view->formHidden("couponId", $coupon->id);
        $this->_html .= $this->view->formHidden("orderId", $order->id);
        $this->_html .= "<div class='clear'></div>";
        $this->_html .= "<div class='coupon-errors'>" .  $coupon->description . "</div>";
        $this->_html .= "</div>";
        $this->_html .= "<div class='clear'></div>";
		
		
		return $this->_html;
    }
}