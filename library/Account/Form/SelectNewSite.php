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
class Account_Form_SelectNewSite extends Fisdap_Form_Base
{
	
	/**
	 * @var Fisdap\Entity\ProgramLegacy
	 */
	public $program;
	
	public static $elementDecorators = array(
		'ViewHelper',
		array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => 'grid_3 filterInputs')),
		array('Label', array('tag' => 'div', 'class' => 'grid_2 filterLabels', 'escape' => false)),
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


		$user = \Fisdap\Entity\User::getLoggedInUser();
		$programId = $user->getProgramId();
		$program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $programId);
		
		$this->program = $program;
		
		$state = new Fisdap_Form_Element_States('state');
		$state->setLabel('State:')
                          ->setCountry($this->program->country)
			  ->addErrorMessage('Please choose a state.');

			  
		$type = new Zend_Form_Element_Select('type');
		$type->setLabel('Site Type:')
			  ->setMultiOptions(array(
									  "none" => "All",
									  "field" => "Field",
									  "lab" => "Lab",
									  "clinical" => "Clinical"));

		$viewscript = "forms/newSiteFilterForm.phtml";
		
		
		
		$this->addElements(array(
			$state,
			$type
		));
		
		$this->setElementDecorators(self::$elementDecorators, array('type', 'state'), true);

		$this->setDecorators(array(
			'PrepareElements',
			array('ViewScript', array('viewScript' => $viewscript)),
			'Form'
		));
		
		$this->setDefaults(array(
			"state" => $program->state,
		));

	}
	
	public function process($data){
	}

		
}
