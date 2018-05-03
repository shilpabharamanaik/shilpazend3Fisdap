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
class Account_Form_SiteAdmin extends Fisdap_Form_Base
{

	public $saved;
	public $noSites;
	
	/**
	 * @var Fisdap\Entity\ProgramLegacy
	 */
	public $program;
	
	public $programName = "";
	
	public $isStaff;
	
	/**
         * @var array decorators for hidden elements
         */
	public static $hiddenDecorators = array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'div', 'class' => 'hidden')),
        );
	
	public function __construct($saved = null, $noSites = null, $options = null)
	{
		$this->saved = $saved;
		$this->noSites = $noSites;
		parent::__construct($options);
	}
	

	/**
	 * init method that adds all the elements to the form
	 */
	public function init()
	{
		parent::init();
		
		$user = \Fisdap\Entity\User::getLoggedInUser();
		$programId = $user->getProgramId();
		$program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $programId);
		
		$this->program = $program;
		
		$loggedInUser = \Fisdap\Entity\User::getLoggedInUser();
			
		if($loggedInUser->staff){
			$this->isStaff = true;
		}
		else {
			$this->isStaff = false;
		}
		
		$this->addJsFile("/js/library/Account/Form/siteListSelect.js");
		$this->addJsFile("/js/library/Account/Form/filterSites.js");
		
		$this->addCssFile("/css/library/Account/Form/filterSites.css");
		
		$field = new Zend_Form_Element_Checkbox('field');
		$field->setCheckedValue('field') 
				->setUnCheckedValue('0') ;
		
		$clinical = new Zend_Form_Element_Checkbox('clinical');
		$clinical->setCheckedValue('clinical') 
				->setUnCheckedValue('0') ;
		
		$lab = new Zend_Form_Element_Checkbox('lab');
		$lab->setCheckedValue('lab') 
				->setUnCheckedValue('0') ;
				
		$all = new Zend_Form_Element_Checkbox('all');
		$all->setCheckedValue('all') 
				->setUnCheckedValue('0') ;
		
		$list_type = new Zend_Form_Element_Hidden('list_type');	

		$viewscript = "forms/siteAdminForm.phtml";
		
		$this->setDecorators(array(
			'FormErrors',
			'PrepareElements',
			array('ViewScript', array('viewScript' => $viewscript)),
			'Form'
		));
		
		$this->addElements(array(
			$saved,
			$programName,
			$program,
			$field,
			$clinical,
			$lab,
			$all,
			$list_type
		));
		
		$this->setDefaults(array(
			"field" => false,
			"clinical" => false,
			"lab" => false,
			"all" => true,
			"list_type" => "mySites"
		));
		
		$this->setElementDecorators(self::$hiddenDecorators, array('list_type'));
	}
	
}
