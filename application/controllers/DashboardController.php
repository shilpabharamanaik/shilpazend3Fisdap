<?php

/**
 * Dashboard
 *
 * @package    Fisdap
 * @subpackage Controllers
 */
class DashboardController extends Fisdap_Controller_Private
{

	public function indexAction()
	{
		$this->redirect('/my-fisdap');
	}
}