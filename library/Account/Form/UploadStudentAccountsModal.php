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
 * Form for processing an activation code
 */

/**
 * @package    Account
 */
class Account_Form_UploadStudentAccountsModal extends Fisdap_Form_Base
{
	
	/**
	 * @var array the decorators for the form
	 */
	protected static $_formDecorators = array(
        'FormErrors',
		'PrepareElements',
		array('ViewScript', array('viewScript' => "forms/uploadStudentAccountsModal.phtml")),
		array('Form'),
	);
	
	/**
	 * @var \Fisdap\Entity\Order
	 */
	public $order;
	
	/**
	 * @var file the csv template
	 */
	public $template;
	
	/**
	 * @param $options mixed additional Zend_Form options
	 */
	public function __construct($orderId = null, $options = null)
	{
		$this->order = \Fisdap\EntityUtils::getEntity('Order', $orderId);
		parent::__construct($options);
	}
	
	/**
	 * init method that adds all the elements to the form
	 */
	public function init()
	{
		parent::init();

		$this->addJsFile("/js/library/Account/Form/excel-file-upload.js");
		$this->addCssFile("/css/library/Account/Form/excel-file-upload.css");
		
		$this->setDecorators(self::$_formDecorators);
		
		$this->setAttrib("id", "uploaderForm");
		$this->setAttrib('enctype', 'multipart/form-data');
		

		

		
		//var_dump($this->template);
		
        $uploader = new Zend_Form_Element_File("file");
        $uploader->setRequired(true)
			 ->addValidator('Extension', false, 'csv');
        $this->addElement($uploader);
		
		$override = new Zend_Form_Element_Checkbox("override");
		$override->setLabel("Override and fill account details with data from this file.");
        $this->addElement($override);

        $test = new \Fisdap_Form_Element_SaveButton("uploadSave");
        $test->setLabel("Upload");
        $this->addElement($test);
		
	}
	
	public function process()
	{
		
	}
}