<?php
namespace MyFisdap\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class MyFisdapController extends AbstractActionController
{
	public function __construct()
    {
		echo "test";//exit;
	}
  /*  public function init()
    {
		parent::init();
        $this->loggedInUser =  $this->user;
		
		if ($this->user && $this->user->getRedirectionPage() != "dashboard") {
			$this->redirect($this->user->getRedirectionPage());
		}
    }
	*/
    /**
     * Index action now loads all widgets and displays them in initial page load
     * (instead of doing a bunch of AJAX requests)
     */
    public function myfisdapAction()
    {
		echo "here";
		//
		
		die();
		//$this->view->pageTitle = "MyFisdap Dashboard";
    }

}
