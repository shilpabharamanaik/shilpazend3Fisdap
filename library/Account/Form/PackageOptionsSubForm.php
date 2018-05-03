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
 * SubForm for choosing package options
 */

/**
 * @package    Account
 */
class Account_Form_PackageOptionsSubForm extends Zend_Form_SubForm
{
    /**
     * @var array
     */
    protected static $elementDecorators = array(
        'ViewHelper',
        array('HtmlTag', array('tag' => 'div', 'style' => 'float:left;')),
    );
    
    /**
     * @var array
     */
    protected static $subformDecorators = array(
        'FormElements',
        array('HtmlTag', array('tag' => 'div', 'class' => 'package-options')),
    );
    
    /**
     * @var \Fisdap\Entity\ProductPackage
     */
    public $package;
    
    public function __construct($packageId = null, $options = null)
    {
        $this->package = \Fisdap\EntityUtils::getEntity('ProductPackage', $packageId);
        
        parent::__construct($options);
    }
    
    public function init()
    {
        $limited = new Zend_Form_Element_Radio('limited');
        $limited->setDecorators(array('ViewHelper'))
                ->setMultiOptions(array('0' => 'unlimited', 1 => 'limited'))
                ->setAttrib("class", "limited")
                ->setValue(0);
                
        if ($this->package->limitable) {
            $this->addElement($limited);
        }
        
        $certifications = new Zend_Form_Element_Radio('certification');
        $certifications->setDecorators(array('ViewHelper'))
                ->setAttrib("class", "certification");
        foreach ($this->package->certifications as $certConfig) {
            $certifications->addMultiOption($certConfig->certification->id, $certConfig->certification->description);
            $certifications->setValue($certConfig->certification->id);
        }
        
        if ($this->package->has_certifications) {
            $this->addElement($certifications);        
        }
        
        $this->setDecorators(self::$subformDecorators);
        $this->setElementDecorators(self::$elementDecorators);
    }
}