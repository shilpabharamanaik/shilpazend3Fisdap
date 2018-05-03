<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2013.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * This produces a modal form for assigning students to a shift
 */

/**
 * @package    Scheduler
 * @subpackage Forms
 */
class Scheduler_Form_PickSitesModal extends Fisdap_Form_BaseJQuery
{
	/**
	 * @var \Fisdap\Entity\User
	 */
	public $user;
	
	/**
	 * @var array
	 */
	public $sites;
	
	/**
	 * @var array
	 */
	public $selected_sites;
	
	/**
	 * @var string
	 */
	public $req_name;
	
	/**
	 *
	 * @param $options mixed additional Zend_Form options
	 */
	public function __construct($site_ids = null, $req_name = null)
	{
		$this->user = \Fisdap\Entity\User::getLoggedInUser();
		$this->sites = \Fisdap\EntityUtils::getRepository('SiteLegacy')->getSitesByProgram($this->user->getProgramId(), null, null, null, true);
		$this->selected_sites = explode(',', $site_ids);
		$this->req_name = $req_name;
		
		parent::__construct();
	}
	
	public function init()
	{
		parent::init();
		$this->addJsFile("/js/library/Scheduler/Form/pick-sites-modal.js");
		$this->addCssFile("/css/library/Scheduler/Form/pick-sites-modal.css");
			
		//Set the decorators for the form
		$this->setDecorators(array(
			'FormErrors',
			'PrepareElements',
			array('ViewScript', array('viewScript' => "pickSitesModal.phtml")),
		));
	}
	
	/**
	 * Process the form
	 *
	 * @param int $event_id
	 * @param array $students
	 * 
	 * @return boolean
	 */
	public function process($event_id, $students)
	{
		return true;
	}
}
