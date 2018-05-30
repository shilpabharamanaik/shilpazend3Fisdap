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
 * Form for searching Fisdap student accounts by email
 */

/**
 * @package    Account
 */
class Account_Form_SearchStudyTools extends Fisdap_Form_Base
{
    
    /**
     * @var array contain the multiple user account options
     */
    public $radioOptions;
    
    /**
     * @var sting the email address
     */
    public $email;
    
    /**
     * @param int $studentId the id of the student to edit
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($radioOptions = null, $email = null, $options = null)
    {
        $this->radioOptions = $radioOptions;
        $this->email = $email;
        
        parent::__construct($options);
    }
    
    /**
     * @var array the decorators for the form
     */
    protected static $_formDecorators = array(
        'FormErrors',
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/searchStudyToolsForm.phtml")),
        array('Form', array('class' => 'study-tools-form')),
    );
    
    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();
        
        $this->addJsFile("/js/library/Account/Form/search-study-tools.js");
        $this->addCssFile("/css/library/Account/Form/search-study-tools.css");

        $this->setDecorators(self::$_formDecorators);
        
        $this->setAction('/account/new/order-individual-products');
        
        $users =  new Zend_Form_Element_Radio("users");
        
        foreach ($this->radioOptions as $option) {
            $optionValue = $option['id'];
            // use the < character to seperate lines, JQuery will handle the actual separation
            $optionLabel = $option['firstName'] . " " . $option['lastName'] . "<" . $option['certLevel'] . "<" . $option['program'] . "<";
            $productString = "";
            $totalProducts = count($option['products']);
            
            for ($i = 0; $i<$totalProducts; $i++) {
                $productString .= $option['products'][$i];
                if ($i == $totalProducts - 2) {
                    $productString .= " and ";
                } elseif ($i != $totalProducts-1) {
                    $productString .= ", ";
                }
            }
            
            $optionLabel .= $productString;
            $users->addMultiOptions(array(
                    $optionValue => $optionLabel
            ));
        }
        
        $lastOption = (count($this->radioOptions) > 1) ? "None of these are me" : "This isn't me";
        $users->addMultiOptions(array("0" => $lastOption));
        $this->addElement($users);
        
        $hiddenEmail = new Zend_Form_Element_Hidden("hiddenEmail");
        $hiddenEmail->setValue($this->email);
        $this->addElement($hiddenEmail);
        
        $continue = new \Fisdap_Form_Element_SaveButton("save");
        $continue->setLabel("Continue");
        $this->addElement($continue);
        
        $this->setElementDecorators(self::$elementDecorators);
        $this->setElementDecorators(self::$hiddenElementDecorators, array('hiddenEmail'), true);
    }
    
    public function process()
    {
        return true;
    }
}
