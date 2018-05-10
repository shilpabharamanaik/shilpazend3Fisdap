<?php

namespace Account\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AccountController extends AbstractActionController
{
    public function __construct()
    {
    }

    public function indexAction()
    {
        return new ViewModel();
    }
}
