<?php

/**
 *  Creation interface for Fisdap Coupons
 */

class Fisdap_Form_Coupon extends Fisdap_Form_Base
{
    /**
     * @var \Fisdap\Entity\Coupon
     */
    public $coupon;
    
    public function __construct($couponId = null, $spec = null)
    {
        $this->coupon = \Fisdap\EntityUtils::getEntity("Coupon", $couponId);
        parent::__construct($spec);
    }
    
    /**
     * init method that adds all the elements to the form
     * */
    public function init()
    {
        parent::init();
        
        $this->addJsFile("/js/library/Fisdap/Form/coupon.js");
        
        //code
        $code = new Zend_Form_Element_Text('code');
        $code->setLabel('Code:')
              ->setRequired(true)
              ->addFilter('StripTags')
              ->addFilter('HtmlEntities')
              //->addErrorMessage("Please enter a code for this coupon.")
              ->setDecorators(self::$gridElementDecorators);
        
        //Add validator to make sure the coupon code is unique
        $codeValidator = new \Zend_Validate_Db_NoRecordExists(array('table' => 'fisdap2_coupons', 'field' => 'code', 'adapter' => \Zend_Registry::get('db')));
        if ($this->coupon->code) {
            $codeValidator->setExclude("code != '" . $this->coupon->code . "'");
        }
        $codeValidator->setMessage('That code already exists. Please choose another.');
        $code->addValidator($codeValidator);
        
        //description
        $description = new Zend_Form_Element_Text('description');
        $description->setLabel('Description:')
                  ->setRequired(true)
                  ->addFilter('StripTags')
                  ->addFilter('HtmlEntities')
                  ->addErrorMessage("Please enter a description for this coupon.")
                  ->setDecorators(self::$gridElementDecorators);
                  
        //configuration
        $product_options = \Fisdap\EntityUtils::getRepository("Product")->findAll();
        $config_array = array();
        
        foreach ($product_options as $product) {
            $config_array[$product->configuration] = $product->name;
        }
        
        $configuration = new Zend_Form_Element_MultiCheckbox('configuration');
        $configuration->setRequired(true)
             ->setLabel('Please check all products to be included:')
                     ->addErrorMessage("Please check at least one product to include.")
                     ->setMultiOptions($config_array)
                     ->setDecorators(self::$gridElementDecorators);
        
                  
        //discount
        $discount = new Zend_Form_Element_Text('discount');
        $discount->setLabel('Discount Percentage:')
                  ->setRequired(true)
                  ->addFilter('StripTags')
                  ->addFilter('HtmlEntities')
                  ->addErrorMessage("Please enter a discount percentage for this coupon.")
                  ->setDecorators(self::$gridElementDecorators);
                  
        //start date
        $start = new Zend_Form_Element_Text('start');
        $start->setLabel('Start Date:')
                  ->setRequired(true)
                  ->addErrorMessage("Please enter a start date for this coupon.")
                  ->setDecorators(self::$gridElementDecorators);
                  
        //end date
        $end = new Zend_Form_Element_Text('end');
        $end->setLabel('End Date:')
                  ->setRequired(true)
                  ->addErrorMessage("Please enter an ending date for this coupon.")
                  ->setDecorators(self::$gridElementDecorators);
                  
        //submit
        $submit = new Fisdap_Form_Element_SaveButton('submit');
        $submit->setDecorators(self::$buttonDecorators);
        
        //hidden element to store the ID
        $couponId = new Zend_Form_Element_Hidden('couponId');
        $couponId->setDecorators(self::$hiddenElementDecorators);
        
        $this->addElements(array($code, $description, $configuration, $discount, $start, $end, $submit, $couponId));
        
        $this->setDecorators(array(
            'FormErrors',
            'FormElements',
            'Form'
        ));
        
        if ($this->coupon->id) {
            $this->setDefaults(array(
                "couponId" => $this->coupon->id,
                "start" => $this->coupon->start_date->format("m/d/Y"),
                "end" => $this->coupon->end_date->format("m/d/Y"),
                "code" => $this->coupon->code,
                "description" => $this->coupon->description,
                "discount" => $this->coupon->discount_percent,
            ));
            
            $configDefaults = array();
            foreach (array_keys($config_array) as $bit_value) {
                if ($bit_value & $this->coupon->configuration) {
                    $configDefaults[] = $bit_value;
                }
            }
            $this->setDefault("configuration", $configDefaults);
        }
    }
    
    public function process($post)
    {
        if ($this->isValid($post)) {
            $values = $this->getValues();
        
            //Create new coupon entity
            if ($values['couponId']) {
                $coupon = \Fisdap\EntityUtils::getEntity('Coupon', $values['couponId']);
            } else {
                $coupon = \Fisdap\EntityUtils::getEntity('Coupon');
            }
            
            $coupon->code = $values['code'];
            $coupon->description = $values['description'];
            $coupon->configuration = array_sum($values['configuration']);
            $coupon->discount_percent = $values['discount'];
            $coupon->start_date = new DateTime($values['start']);
            $coupon->end_date = new DateTime($values['end']);
            
            //Save the changes and flush
            $coupon->save();
            
            return $coupon->id;
        }
        
        return false;
    }
}
