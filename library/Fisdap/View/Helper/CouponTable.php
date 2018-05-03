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
 * This helper takes in date params and outputs a table of all coupons valid
 * in that time frame.
 */

/**
 * @package Fisdap
 */
class Fisdap_View_Helper_CouponTable extends Zend_View_Helper_Abstract 
{
    protected $_html;
    
    public function couponTable($filters)
    {
		$coupons = \Fisdap\EntityUtils::getRepository('Coupon')->getCouponsByDateRange($filters['start'], $filters['end']);
		
		$this->_html = "<div class='clear'></div>
						<div class='withTopMargin extraLong'>
						<h3 class='section-header'>Coupons</h3>
						<div id='coupons-holder'><table id='coupon-table' class='tablesorter coupon-search-table'>";
					
		$this->_html .= "<thead><tr id='head'>
						<th class='code'>Code</th>
						<th class='description'>Description</th>
						<th class='configuration'>Configuration</th>
						<th class='discount'>Discount</th>
						<th class='startdate'>Start Date</th>
						<th class='enddate'>End Date</th>
						<th class='edit'></th>
						</tr></thead><tbody>";

		$professionId = \Fisdap\Entity\ProgramLegacy::getCurrentProgram()->profession->id;
		
		foreach($coupons as $coupon) {
			$this->_html .= "<tr>";
			
			$this->_html .= "<td class='code'>" . $coupon['code'] . "</td>";
			$this->_html .= "<td class='description'>" . $coupon['description'] . "</td>";
			$this->_html .= "<td class='configuration'>" . \Fisdap\Entity\Product::getProductSummary($coupon['configuration'], $professionId, "<br>") . "</td>";
			$this->_html .= "<td class='discount'>" . $coupon['discount_percent'] . "%</td>";
			$this->_html .= "<td class='startdate'>" . $coupon['start_date']->format('m/d/Y') . "</td>";
			$this->_html .= "<td class='enddate'>" . $coupon['end_date']->format('m/d/Y') . "</td>";
			$this->_html .= "<td class='edit'><a href='/admin/coupon/new/couponId/" . $coupon['id'] . "'><img src='\images\icons\edit.png'></a></td>";
			
			$this->_html .= "</tr>";
		}
		$this->_html .= "</tbody></table></div></div>";
		
        return $this->_html;
	}
}