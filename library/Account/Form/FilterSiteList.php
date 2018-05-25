<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
 *                                                                           *
 *        Copyright (C) 1996-2011.  This is an unpublished work of           *
 *                         Headwaters Software, Inc.                         *
 *                            ALL RIGHTS RESERVED                            *
 *        This program is a trade secret of Headwaters Software, Inc. and    *
 *        it is not to be copied, distributed, reproduced, published, or     *
 *        adapted without prior authorization of Headwaters Software, Inc.   *
 *                                                                           *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * /


/**
 * Form for managing program sites
 */

/**
 * @package    Fisdap
 * @subpackage Account
 */
class Account_Form_FilterSiteList extends Fisdap_Form_Base
{

	/**
	 * @var Fisdap\Entity\ProgramLegacy
	 */
	public $program;

	public static $elementDecorators = array(
			'ViewHelper',
			array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => 'filterInputs')),
			array('Label', array('tag' => 'div', 'class' => 'filterLabels', 'escape' => false)),
			);

	public function __construct($program = null, $options = null)
	{
		$this->program = $program;
		parent::__construct($options);
	}
	/**
	 * init method that adds all the elements to the form
	 */
	public function init()
	{
		parent::init();

		$this->addJsFile("/js/library/Account/Form/filterSites.js");
        $this->addCssFile("/css/library/Account/Form/filterSites.css");
		$this->addJsFile("/js/jquery.fieldtag.js");

		$user = \Fisdap\Entity\User::getLoggedInUser();
		$program = $user->getCurrentRoleData()->program; 
		$this->program = $program;
		
		$title = ($this->_view->action == "add") ? "Search by name, city, address, or contact..." : "Search by name or city...";
		$site_search = new Zend_Form_Element_Text("site_search");
		$site_search->setAttribs(array(
			"class" => "search-box fancy-input",
			"autocomplete" => "off",
			"title" => $title));

		$state = new Fisdap_Form_Element_States('state');
		$state->setLabel('State:')
			->setNullOption(false)
			->setCountry($this->program->country)
			->addErrorMessage('Please choose a state.');

		$this->addElements(array($site_search, $state));

		$this->setElementDecorators(self::$elementDecorators, array('site_search', 'state'), true);

		$this->setDecorators(array(
					'PrepareElements',
					array('ViewScript', array('viewScript' => 'forms/siteFilterForm.phtml')),
					'Form'
					));

		$this->setDefaults(array("state" => $program->state));

		$site_search->removeDecorator('Label');
	}

}
