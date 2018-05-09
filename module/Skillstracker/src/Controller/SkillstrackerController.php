<?php
namespace Skillstracker\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
class SkillstrackerController extends AbstractActionController
{
	public function __construct()
    {
		//echo "test";//exit;
	}
	/*public function init()
    {
		parent::init();
        $this->loggedInUser =  $this->user;
		
		if ($this->user && $this->user->getRedirectionPage() != "dashboard") {
			$this->redirect($this->user->getRedirectionPage());
		}
    }
	*/
    public function skillstrackerAction()
    {
		//echo "fhf fhgfh"; //die();
		 return new ViewModel();
    }

}
