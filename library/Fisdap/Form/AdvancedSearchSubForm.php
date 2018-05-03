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
 * Sub form for advanced searching
 */

/**
 * @package    Fisdap
 * @subpackage Admin
 */
class Fisdap_Form_AdvancedSearchSubForm extends Zend_Form_SubForm
{
    public $buttonDecorators = array(
        'ViewHelper',
    );
    
    public function init()
    {
        //jquery setup
        if (!$this->_view) {
            $this->_view = $this->getView();
        }
        $this->_view->jQuery()->addJavascriptFile("/js/jquery.savestate.js");
	
        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "advancedSearchSubForm.phtml")),
		    array('HtmlTag', array('tag'=>'div', 'class'=>'form-prompt')),
		));
        
        //user type
		$user_type = new Zend_Form_Element_Radio('userType');
		$user_type->setMultiOptions(array(
			'student' => 'a student',
			'instructor' => 'an instructor',
		));
	
		//certification levels
		$certification = new Fisdap_Form_Element_CertificationLevel('certification');
		$certification->setRequired(true);
		
		//graduation date
		$graduation_date = new Fisdap_Form_Element_GraduationDate('graduationDate');
		
		//hidden toggle
		$hidden_toggle = new Zend_Form_Element_Hidden('toggle');
		$hidden_toggle->setValue(true)
					  ->setDecorators(array("ViewHelper"));
		
		$search_button = new Fisdap_Form_Element_SaveButton('searchButton');
		$search_button->setLabel('Search')
					  ->setDecorators($this->buttonDecorators);
		
		$cancel_button = new Fisdap_Form_Element_CancelButton('cancelButton');
		$cancel_button->setDecorators($this->buttonDecorators);
		
		$this->addElement($user_type)
			 ->addElement($certification)
			 ->addElement($graduation_date)
			 ->addElement($hidden_toggle)
			 ->addElement($search_button)
			 ->addElement($cancel_button);
			 
		//set default values
		$this->setDefaults(array(
			'userType' => 'student',
			'certification' => array('emt-b', 'emt-i', 'paramedic'),
		));
    }
}