<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * This produces a modal form for editing shifts
 */

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class SkillsTracker_Form_Modal extends Fisdap_Form_BaseJQuery
{
	/**
	 * @var array decorators for individual elements
	 */
    public static $elementDecorators = array(
        'ViewHelper',
		array(array('break' => 'HtmlTag'), array('tag' => 'br', 'openOnly' => true, 'placement' => 'PREPEND')),
        array('LabelDescription', array('escape' => false)),
        array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt')),
    );
	
	/**
	 * @var array decorators for checkbox elements
	 */
	public static $checkboxDecorators = array(
		'ViewHelper',
		array('Label', array('placement' => 'APPEND')),
	);
	
	/**
	 * @var array decorators for buttons
	 */
	public static $buttonDecorators = array(
		'ViewHelper',
	);
	
	/**
	 * @var array decorators for jQuery form elements
	 */
	public static $formJQueryElements = array(
        'ErrorHighlight',
        array('UiWidgetElement', array('tag' => '')), // it necessary to include for jquery elements
		array(array('break' => 'HtmlTag'), array('tag' => 'br', 'openOnly' => true, 'placement' => 'PREPEND')),
        array('LabelDescription', array('class' => '', 'escape' => false)),
        array('HtmlTag', array('tag'=>'div', 'class'=>'form-prompt')),
	);
	
	/**
	 * @var array decorators for hidden elements
	 */
	public static $hiddenElementDecorators = array(
		'ViewHelper',
	);
    
    public function init()
    {
        $this->addPrefixPath('Fisdap_Form_Decorator', 'Fisdap/Form/Decorator/', 'decorator');
    }
}