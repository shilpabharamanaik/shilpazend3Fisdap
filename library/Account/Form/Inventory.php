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
 * Form for filtering inventory
 */

/**
 * @package    Account
 */
class Account_Form_Inventory extends SkillsTracker_Form_Modal
{
        
    /**
     * @var array contain the products that are displayed
     */
    public $products;
    
    /**
     * @var array contain the certification levels that are displayed
     */
    public $certLevels;
    
    
    /**
     * @var array the decorators for the form
     */
    protected static $_formDecorators = array(
        'FormErrors',
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/inventoryForm.phtml")),
        array('Form', array('class' => 'reset-password-form')),
    );
    
    /**
     * @var array decorators for products
     */
    public static $gridDecorators = array(
        'ViewHelper',
        array('Label', array('class' => 'grid_1')),
        array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'email')),
    );
    
    /**
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($options = null)
    {
        $this->products = \Fisdap\EntityUtils::getRepository("Product")->getProducts();
        $this->certLevels = \Fisdap\Entity\CertificationLevel::getFormOptions(false, false, "description", null, true);
        parent::__construct($options);
    }
    
    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();

        $this->setDecorators(self::$_formDecorators);
        $this->addJsFile("/js/library/Account/Form/inventory.js");
        $this->addCssFile("/css/library/Account/Form/inventory.css");
        
        // create a checkbox for each product
        foreach ($this->products as $product) {
            $productElement = new Zend_Form_Element_Checkbox("products_" . $product->configuration);
            $productElement->setLabel($product->name)
                           ->setValue(1);
            $this->addElement($productElement);
        }
        

        $dateBeginDefault = new \DateTime('-2 week');
        $dateBeginDefault = $dateBeginDefault->format('m/d/Y');
    
        $dateEndDefault = new \DateTime('+1 day');
        $dateEndDefault = $dateEndDefault->format('m/d/Y');
        
        $program = \Fisdap\Entity\ProgramLegacy::getCurrentProgram();
        
        // create an ordered between
        $dateBegin = new ZendX_JQuery_Form_Element_DatePicker('dateBegin');
        $dateBegin->setValue($dateBeginDefault)
                  ->setAttrib("class", "selectDate");
        $this->addElement($dateBegin);
        
        $dateEnd = new ZendX_JQuery_Form_Element_DatePicker('dateEnd');
        $dateEnd->setValue($dateEndDefault)
                ->setAttrib("class", "selectDate");
        $this->addElement($dateEnd);

        // add years/group select boxes
        //$groups = new Fisdap_Form_Element_Groups('group');
        $classSectionRepository = \Fisdap\EntityUtils::getRepository('ClassSectionLegacy');
        $groups_options = $classSectionRepository->getFormOptions($program->id);
        $groups_options['0'] = "Any group";
        
        $groups = $this->createChosen('group', "Student groups", "277px", "All student groups...", $groups_options, false);
        $this->addElement($groups);
        
        // all for graduation years
        $grad = new Fisdap_Form_Element_GraduationDate('grad');
        $this->addElement($grad);
        $sn_years = $program->get_possible_graduations_years_from_sn(false);
        $years = $program->get_possible_graduation_years(false);
        $merged_years = array();
        foreach ($sn_years as $year) {
            $merged_years[$year] = $year;
        }
        foreach ($years as $year) {
            $merged_years[$year] = $year;
        }
        ksort($merged_years);
        $first_year = (int)array_shift(array_slice($merged_years, 0, 1));
        $last_year = (int)end($merged_years);
        $years_inclusive = array();
        $years_inclusive[0] = "Year";
        for ($i = $first_year; $i < $last_year+1; $i++) {
            $years_inclusive[$i] = $i;
        }
        $grad->setAttrib('yearAttribs', array('formOptions' => $years_inclusive));
        
        foreach ($this->certLevels as $level) {
            // LAME - zend struggles with having a dash in the name, replace it with an underscore
            $elementName = preg_replace('/[^a-z0-9]/i', '_', $level['name']);
            $levelElement = new Zend_Form_Element_Checkbox("levels_" . $elementName);
            $levelElement->setLabel($level['displayName'])
                         ->setValue(1);
            $this->addElement($levelElement);
        }
        
        // create the field for a specific activation code
        $code = new Zend_Form_Element_Textarea("code");
        $this->addElement($code);
        
        // create available/activated/distributed checkboxes
        $available = new Zend_Form_Element_Checkbox("available");
        $available->setLabel("Available")
              ->setValue(1);
        $this->addElement($available);
        
        $activated = new Zend_Form_Element_Checkbox("activated");
        $activated->setLabel("Activated");
        $this->addElement($activated);

        $distributed = new Zend_Form_Element_Checkbox("distributed");
        $distributed->setLabel("Distributed");
        $this->addElement($distributed);
        
        $this->setElementDecorators(self::$elementDecorators);
        $this->setElementDecorators(self::$formJQueryElements, array('dateBegin'), true);
        $this->setElementDecorators(self::$formJQueryElements, array('dateEnd'), true);
    }
    
    private function createChosen($element_name, $label, $width, $placeholder_text, $options, $multi = "multiple")
    {
        $chosen = new Zend_Form_Element_Select($element_name);
        $chosen->setMultiOptions($options)
             ->setLabel($label)
             ->setAttribs(array("class" => "chzn-select update-people-description-on-change",
                                           "data-placeholder" => $placeholder_text,
                                           "style" => "width:" . $width,
                                           "multiple" => $multi,
                                           "tabindex" => count($options)));
        return $chosen;
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

            return true;
        } else {
            return false;
        }
    }
}
