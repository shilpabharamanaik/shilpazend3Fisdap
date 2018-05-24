<?php

/**
 * the main controller for the coupon controller.
 *
 * @package    reports
 * @subpackage Controllers
 */
class Admin_CouponController extends Fisdap_Controller_Staff
{
	public function init()
    {
        parent::init();	
    }
	
	public function indexAction()
	{
		$this->view->pageTitle = "Coupon Admin";
		//$this->view->headScript()->appendFile("/js/reports/coupon/coupon.js");
		//$this->view->headLink()->appendStylesheet("/css/reports/coupon/coupon.css");		
		$this->view->headScript()->appendFile("/js/tableSorter/jquery.tablesorter.min.js");
	}
	
	public function newAction()
	{
		if (\Fisdap\Entity\User::getLoggedInUser() == null)
		{
			$this->_redirect('/');
		}
		if (!\Fisdap\Entity\User::getLoggedInUser()->isStaff())
		{
			$this->_redirect('/');
		}
		
		$this->view->pageTitle = "Create new coupon";
		$this->view->form = new Fisdap_Form_Coupon($this->_getParam("couponId"));
		
		$request = $this->getRequest();
	    
	    if ($request->isPost())
	    {
		    if ($this->view->form->process($request->getPost()) == true)
		    {
			$this->flashMessenger->addMessage("Your coupon was successfully saved.");
			$this->_redirect("/admin/coupon");
		    }
		    
	    }
	}
	
	public function getCouponTableAction()
	{
		$this->_helper->json($this->view->couponTable($this->_getAllParams()));		
	}
}