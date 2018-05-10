<?php

namespace Reports\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ReportsController extends AbstractActionController
{
    public function __construct()
    {
    }

    public function splashAction()
    {
        return new ViewModel();
    }
}
