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
 * Form for choosing a package
 */

/**
 * @package    Account
 */
class Account_Form_Packages extends Fisdap_Form_Base
{
    /**
     * @var array the decorators for the form
     */
    protected static $_formDecorators = array(
        'FormErrors',
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/packagesForm.phtml")),
        array('Form'),
    );

    /**
     * @var \Fisdap\Entity\Order
     */
    public $order;

    /**
     * @var array of package entities
     */
    public $packages;

    /**
     * @var \Fisdap\Entity\CertificationLevel
     */
    public $certification;

    public function __construct($orderId = null, $certificationId = null, $options = null)
    {
        $this->order = \Fisdap\EntityUtils::getEntity('Order', $orderId);
        $this->certification = \Fisdap\EntityUtils::getEntity('CertificationLevel', $certificationId);
        $this->packages = \Fisdap\EntityUtils::getRepository('ProductPackage')->findByCertification($certificationId);

        parent::__construct($options);
    }

    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();

        $this->setAttrib("id", "packagesForm");

        $this->setDecorators(self::$_formDecorators);
        $this->addCssFile("/css/library/Account/Form/packages.css");
        $this->addJsFile("/js/library/Account/Form/packages.js");

        //BE AWARE: I switched the name and id attribute of the package radio buttons
        //so that they pretend to be part of the same radio group, look for more goofiness in the isValid() method
        foreach ($this->packages as $package) {
            $element = new Zend_Form_Element_Radio("packages_" . $package->id, array('escape' => false));
            $element->setDecorators(self::$strippedDecorators)
                ->setAttribs(array('name' => 'packages', 'id' => 'packages'))
                ->addMultiOption($package->id, "<span class='package-title'>{$package->name}</span><div class='package-desc'>{$package->description}</div>");
            $this->addElement($element);
            //$subform = new Account_Form_PackageOptionsSubForm($package->id);
            //$this->addSubForm($subform, "packageOptions_" . $package->id);
        }

        $packageId = new Zend_Form_Element_Hidden('packageId');
        $packageId->setDecorators(array("ViewHelper"));
        $this->addElement($packageId);
    }

    /**
     * JANK ALERT: Because Zend does not allow me to make the package options
     * part of the same radio set, we have some hacking to fake it by inserting the value
     * that zend expects in the POST array based on the name of the radio (packages)
     *
     * We're also setting the hidden packageId variable here
     *
     * @param array $values
     * @return boolean
     */
    public function isValid($values)
    {
        if ($values['packages']) {
            $values['packages_' . $values['packages']] = $values['packages'];
            $values['packageId'] = $values['packages'];
        }

        return parent::isValid($values);
    }

    /**
     * Process the submitted POST values and do whatever you need to do
     *
     * @param array $post the POSTed values from the user
     * @return mixed either the values or the form w/errors
     */
    public function process($post)
    {
        if ($this->isValid($post)) {
            $formValues = $this->getValues();

            $session = new \Zend_Session_Namespace("OrdersController");
            $session->packagesFormValues = $formValues;

            return true;
        }
        return $this;
    }
}
