<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2014.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * /


/**
 * Custom ZendForm
 * @package Reports
 * @author khanson
 */
class Reports_Form_ProfessionsForm extends Fisdap_Form_Base
{

    /**
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($config = null, $options = null)
    {
        parent::__construct($options);
    }

    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();

        // Now create the individual elements
        // create the skills chosen
        $prof_options = \Fisdap\EntityUtils::getRepository('Profession')->getFormOptions("prof.name");
        $profession = new Zend_Form_Element_Select("profession");
        $profession->setMultiOptions($prof_options)
            ->setAttribs(array("class" => "chzn-select",
                "data-placeholder" => "Choose a profession...",
                "style" => "width:390px",
                "multiple" => false));
        $profession->setRegisterInArrayValidator(false);
        $profession->setRequired(true);
        $profession->addErrorMessage("Please select a profession.");

        // Add elements
        $this->addElements(array(
            $profession,
        ));

        //Set the decorators for the form
        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => 'forms/professions-form.phtml')),
        ));
        
        // set element decorators
        $this->setElementDecorators(self::$basicElementDecorators, array('profession'));
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
            $values = $this->getValues();
        }
        return false;
    }
    
    /**
     * Return an array containing the summary of what's on this report
     *
     */
    public function getReportSummary($config)
    {
        $summary = array();
        
        // get profession info
        $profession = \Fisdap\EntityUtils::getEntity('Profession', $config['profession']);
        $summary["Profession"] = $profession->name;
        
        return $summary;
    }
}
