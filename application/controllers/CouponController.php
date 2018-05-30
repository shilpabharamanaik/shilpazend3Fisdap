<?php

/**
 * the main controller for the coupon controller.
 *
 * @package    reports
 * @subpackage Controllers
 */
class CouponController extends Fisdap_Controller_Base
{
    public function init()
    {
        parent::init();
    }
    
    public function indexAction()
    {
        if (\Fisdap\Entity\User::getLoggedInUser() == null) {
            $this->_redirect('/');
        }
        if (!\Fisdap\Entity\User::getLoggedInUser()->isStaff()) {
            $this->_redirect('/');
        }
        
        $this->view->pageTitle = "Coupon Report";
        $this->view->headScript()->appendFile("/js/reports/coupon/coupon.js");
        $this->view->headLink()->appendStylesheet("/css/reports/coupon/coupon.css");
        $this->view->headScript()->appendFile("/js/tableSorter/jquery.tablesorter.min.js");
        $this->view->headScript()->appendFile("/js/library/Fisdap/Utils/create-pdf.js");
    }
    
    public function getCouponsFromSearchAction()
    {
        
        //check for POST data
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->view->isPost = true;
            $post = $request->getPost();

            $filters = array();
            
            if (isset($post['startDate'])) {
                $filters['startDate'] = $post['startDate'];
            }
            if (isset($post['endDate'])) {
                $filters['endDate'] = $post['endDate'];
            }
            
            $start = new DateTime($filters['startDate']);
            $end = new DateTime($filters['endDate']);
            
            if ($post != null) {
                $coupons = \Fisdap\EntityUtils::getRepository('Coupon')->getCouponsByDateRange($start, $end);
            } else {
                //grab ALL of the coupons!
                $coupons = \Fisdap\EntityUtils::getRepository('Coupon')->findAll();
            }
        } else {
            $this->view->isPost = false;
        }

        $returnText = "";
        

        if ($coupons) {
            $returnText .= $this->getCouponsTable($coupons);
        } else {
            $returnText .= "<div class='clear'></div><div class='grid_12 island withTopMargin'>
							    <h3 class='section-header'>Programs</h3><div class='error'>No programs
							    were found</div></div>";
        }
            
        $this->_helper->json(array("table" => $returnText));
    }
    
    public function getCouponsTable($coupons)
    {
        $returnText = "<div class='clear'></div>
					<div class='island withTopMargin extraLong'>
					<h3 class='section-header'>Coupons</h3>
					<div id='coupons-holder'><table id='coupon-table' class='tablesorter coupon-search-table'>";
                    
        $returnText .= "<thead><tr id='head'>
						<th class='id'>Id</th>
						<th class='code'>Code</th>
						<th class='description'>Description</th>
						<th class='configuration'>Configuration</th>
						<th class='discount'>Discount</th>
						<th class='startdate'>Start Date</th>
						<th class='enddate'>End Date</th>
						</tr></thead><tbody>";
                        
        foreach ($coupons as $coupon) {
            $returnText .= "<tr>";
            
            $returnText .= "<td class='id'>" . $coupon->id . "</td>";
            $returnText .= "<td class='code'>" . $coupon->code . "</td>";
            $returnText .= "<td class='description'>" . $coupon->description . "</td>";
            $returnText .= "<td class='configuration'>" . $coupon->configuration . "</td>";
            $returnText .= "<td class='discount'>" . $coupon->discount_percent . "</td>";
            $returnText .= "<td class='startdate'>" . $coupon->start_date->format('Y-m-d') . "</td>";
            $returnText .= "<td class='enddate'>" . $coupon->end_date->format('Y-m-d') . "</td>";
            
            $returnText .= "</tr>";
        }
        $returnText .= "</tbody></table></div></div>";
        
        return $returnText;
    }
}
