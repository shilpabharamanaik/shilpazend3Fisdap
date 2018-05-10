<?php
namespace Skillstracker\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
class SkillstrackerController extends AbstractActionController
{
	public function __construct()
    {
	}

    public function indexAction()
    {
		 return new ViewModel();
    }

}
