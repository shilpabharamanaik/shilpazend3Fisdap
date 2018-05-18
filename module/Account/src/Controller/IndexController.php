<?php
namespace Account\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class Account_IndexController extends AbstractActionController
{

   public function __construct()
    {
    }

    public function indexAction()
    {
        return new ViewModel();
    }

}

