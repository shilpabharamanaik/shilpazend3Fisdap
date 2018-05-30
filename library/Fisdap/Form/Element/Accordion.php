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
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * Custom Zend_Form_Element_Select for displaying jquery accordions.
 *
 * The elements passed in should follow a grouped array structure- at the top
 * level, indexes should be the names of the accordion headers.  Under that,
 * there should be arrays with the database ID as the index, and the name for
 * that option as the value.  Example:
 *
 * array(
 * 	'Airway' => array(25221 => 'Some Airway', 25222 => 'Airway Management'),
 *  'Medical' => array(25225 => 'Some Medical', 25226 => 'Another Med')
 * )
 *
 * To set a value, just pass in an array of IDs that should be selected.  They
 * should map to the IDs in the above array.
 */

/**
 * @package Fisdap
 */
class Fisdap_Form_Element_Accordion extends Zend_Form_Element
{
    public $elements = array();
    public $spec = "";
    
    public function __construct($spec, $elements=array(), $options = null)
    {
        $this->spec = $spec;
        $this->elements = $elements;
        // Default its value to an empty array, just to make it the correct type in case
        // it doesn't get altered.
        $this->setValue(array());
        parent::__construct($spec, $options);
    }
    
    public function init()
    {
        //jquery setup
        if (!$this->_view) {
            $this->_view = $this->getView();
        }
        
        $this->_view->headScript()->appendFile("/js/library/Fisdap/Form/Element/accordion.js");
        $this->_view->headLink()->appendStylesheet("/css/library/Fisdap/Form/Element/accordion.css");
    }
    
    public function __toString()
    {
        $html = "<div class='accordion_container' id='{$this->spec}_container' data-element_name='{$this->spec}'>";
        
        $pos = 0;
        
        $vals = $this->getValue();
        
        foreach ($this->elements as $elementGroup => $elements) {
            $html .= "<div class='accordion_section'>";
            $html .= "	<div class='accordion_header' data-group_id='{$pos}'>";
            $html .= "		<div class='imgWrapper'><img src='/images/icons/minus_Gray.png'>" . $elementGroup . "</div>";
            $html .= "	</div>";
            $html .= "	<div class='accordion_options' data-group_id='{$pos}'>";
            foreach ($elements as $element) {
                $selectedClassStr = "";
                if (in_array($element->id, $vals)) {
                    $selectedClassStr = "accordion_option_selected";
                }
                $html .= "		<div class='accordion_option {$selectedClassStr}' id='{$this->spec}_element_{$element->id}' data-element_id='{$element->id}'>{$element->name}</div>";
            }
            
            $html .= "	</div>";
            $html .= "</div>";
            
            $pos++;
        }
        
        $html .= "
			<div class='accordion_input_container' data-element_name='{$this->spec}'>
				<input type='hidden' name='{$this->spec}' id='{$this->spec}_input' value='" . implode(',', $vals) . "'/>
			</div>
		";
        
        $html .= "</div>";
        
        $html .= "<div class='accordion_display' data-element_name='{$this->spec}'></div>";
        
        return $html;
    }
}
