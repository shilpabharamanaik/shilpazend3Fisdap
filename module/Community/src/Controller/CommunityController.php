<?php

namespace Community\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class CommunityController extends AbstractActionController
{
    public function __construct()
    {
    }

    public function indexAction()
    {
        return new ViewModel();
    }
}
