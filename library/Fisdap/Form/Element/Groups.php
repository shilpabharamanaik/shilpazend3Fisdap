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
 * Custom Groups
 */

/**
 * Class creating a composite graduation date element
 *
 * @package Fisdap
 */
class Fisdap_Form_Element_Groups extends Zend_Form_Element_Xhtml
{
    /**
     * @var integer the ID of the selected group
     */
    protected $_id;
    
    /**
     * @var integer the year of this group
     */
    protected $_year;
    
    /**
     * @var string the view helper that will render this composite element
     */
    public $helper = "groupsElement";
    
    public function init()
    {
        //jquery setup
        if (!$this->_view) {
            $this->_view = $this->getView();
        }
        
        //add js file to populate the base list
        $this->_view->headScript()->appendFile("/js/library/Fisdap/Form/Element/groups.js");
        $this->_view->headScript()->appendFile("/css/library/Fisdap/Form/Element/groups.css");
    }
    
    /**
     * Set the date of this form element
     *
     * @param mixed DateTime | array the month/year values to set
     * @return Fisdap_Form_Element_GraduationDate the form element
     */
    public function setValue($value)
    {
        //$this->addJsFile("/js/library/Fisdap/Form/Element/groups.js");

        $this->_id = $value['id'];
        $this->_year = $value['year'];
        return $this;
    }
    
    /**
     * returns the value of this form element
     *
     * @return array
     */
    public function getValue()
    {
        return array('id' => $this->_id, 'year' => $this->_year);
    }
}
