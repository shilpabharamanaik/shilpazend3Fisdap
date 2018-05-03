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
 * Form for searching for a Fisdap user
 */

/**
 * @package Fisdap
 * @subpackage Admin
 */
class Fisdap_Form_UserSearch extends Zend_Form
{
	/**
	 * @var array decorators for the form
	 */
	public static $formDecorators = array(
			'FormErrors',
			'FormElements',
			array('Form', array('class' => 'grid_8')),
			);

	/**
	 * @var array decorators for individual elements
	 */
	public static $elementDecorators = array(
			'ErrorHighlight',
			'ViewHelper',
			array('Label', array('class' => 'form-label dark-gray')),
			array('HtmlTag', array('tag'=>'div', 'class'=>'form-prompt')),
			);

	/**
	 * @var array decorators for button elements
	 */
	public static $buttonDecorators = array(
			'ViewHelper',
			array('HtmlTag', array('tag'=>'div', 'class'=>'floating-button-container')),
			);

	/**
	 * Create and add all the elements to the form
	 */
	public function init()
	{
		$this->setAttrib('id', 'userSearch');

		//add path to custom decorators
		$this->addElementPrefixPath('Fisdap_Form_Decorator', 'Fisdap/Form/Decorator/', 'decorator');

		//jquery setup
		if (!$this->_view) {
			$this->_view = $this->getView();
		}

		$this->_view->jQuery()->addOnLoad("$('#searchString').fieldtag()");
		$this->_view->headScript()->appendFile("/js/jquery.fieldtag.js");

		//create search box
		$search = new Zend_Form_Element_Text('searchString');
		$search->setLabel("Who are you looking for?")
			->setRequired(true)
			->setAttrib('title', 'Type name, username, or email, etc')
			->setAttrib('size', '50')
			->addFilter('StringTrim')
			->addFilter('PregReplace', array('match' => '/\s+/', 'replace' => ' '))
			->addFilter('Alnum', array('allowwhitespace' => true))
			->setDecorators(self::$elementDecorators);
		$this->addElement($search);

		//create buttons
		$search_button = new Fisdap_Form_Element_SaveButton('searchButton');
		$search_button->setLabel("Search");
		$this->addElement($search_button);

		//Set decorators here
		$this->setDecorators(self::$formDecorators);
		$this->setElementDecorators(self::$elementDecorators, array('searchButton', 'searchString'), false);
		$this->setElementDecorators(self::$buttonDecorators, array('searchButton'), true);
	}

	/**
	 * Function to process form input
	 *
	 * @param array the POSTed information from the form
	 * @return mixed if the form is valid, return array of user information, if invalid, return the form w/errors
	 */
	public function process($post)
	{
		if ($this->isValid($post)) {
			$values = $this->getValues();
			$users = \Fisdap\EntityUtils::getRepository('User')->searchUsers($values['searchString']);
			return $users;
		}

		return $this;
	}
}
