<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2014.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * View Helper to display a total cost
 */

/**
 * @package Fisdap
 */
class Fisdap_View_Helper_CostContainer extends Zend_View_Helper_Abstract
{
    protected $html;

    public $view;

    public function __construct($view = null)
    {
        if ($view) {
            $this->view = $view;
        }
    }

    public function costContainer($order)
    {
        // get some other info for the view, too
        if ($order) {
            $discounts = $order->getAssociatedDiscounts();
            $totalCost = $order->getSubtotal();
            $discountedCost = $order->getTotalCost();
            if ($totalCost == $discountedCost) {
                $discountedCost = null;
            }
        } else {
            $discounts = array();
        }

        $this->view->headLink()->appendStylesheet('/css/library/Fisdap/View/Helper/cost-container.css');

        $discounted = ($discountedCost) ? "discounted" : "";
        $this->html = '<div class="discounts">';
        foreach ($discounts as $discount) {
            $this->html .= "<div>$discount</div>";
        }
        $this->html .= '</div>
        <div id="cost-container" class="section-header no-border">
                        <h3 class="section-header no-border">Total cost</h3>

                        <div class="green total-cost-container ' . $discounted . '">$<span class="total-cost">' . $totalCost . '</span></div>
                        <div class="green discount-cost-container ' . $discounted . '">$<span class="discount-cost">' . $discountedCost . '</span></div>
                    </div>';

        return $this->html;
    }
}
