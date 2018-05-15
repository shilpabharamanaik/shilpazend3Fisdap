<?php
namespace Myfisdap\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class MyfisdapController extends AbstractActionController
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
    public function myfisdapAction()
    {
        //echo "fhf fhgfh"; //die();
        return new ViewModel();
    }
}
