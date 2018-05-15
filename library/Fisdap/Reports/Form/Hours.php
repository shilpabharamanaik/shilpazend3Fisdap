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
 * Hour Type Display settings form for Hours Report
 *
 * It's three checkboxes displayed as sliders, pretty simple
 * @author jmortenson
 */
class Fisdap_Reports_Form_Hours extends Fisdap_Form_Base
{
    public $scheduledLabel = 'Scheduled hours';
    public $lockedLabel = 'Locked hours';
    public $auditedLabel = 'Audited hours';
    
    /**
    * @param $options mixed additional Zend_Form options
    */
    public function __construct($filters = null, $options = null)
    {
        parent::__construct($options);
    }
        
    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();
        $this->addJsFile("/js/jquery.sliderCheckbox.js");
        $this->addCssFile("/css/jquery.sliderCheckbox.css");
        $this->addCssFile("/css/library/Fisdap/Reports/Form/hours.css");
       
        $this->addJsFile("/js/library/Fisdap/Reports/Form/hours.js");
                
        // Zend Form decorators that work with the sliders and display in the format we want (Buttons on the left, label right)
        $sliderDecorators = array(
        'ErrorHighlight',
        'ViewHelper',
        array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => 'grid_1 hours-report-slider')),
        array('LabelDescription', array('tag' => 'div', 'class' => 'grid_3', 'placement' => 'APPEND')),  //'escape' => false)),
        array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt')),
    );
        
        $hoursScheduled = new Zend_Form_Element_Checkbox('hours_scheduled');
        $hoursScheduled->setLabel($this->scheduledLabel);
        $hoursScheduled->setValue(1);
        $hoursScheduled->setRequired(false);
        $hoursScheduled->setDecorators($sliderDecorators);
        
        $hoursLocked = new Zend_Form_Element_Checkbox('hours_locked');
        $hoursLocked->setLabel($this->lockedLabel);
        $hoursLocked->setValue(1);
        $hoursLocked->setRequired(false);
        $hoursLocked->setDecorators($sliderDecorators);
        
        $hoursAudited = new Zend_Form_Element_Checkbox('hours_audited');
        $hoursAudited->setLabel($this->auditedLabel);
        $hoursAudited->setValue(1);
        $hoursAudited->setRequired(false);
        $hoursAudited->setDecorators($sliderDecorators);
        
        // add elements to the form
        $this->addElements(array($hoursScheduled, $hoursLocked, $hoursAudited));
        
        //Set the decorators for the form
        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => 'forms/hours.phtml')),
        ));
    }
    
    
    /**
     * Override isValid to do custom validation that checks multiple fields
     */
    public function isValid($post)
    {
        // make sure at least one hours type is selected
        if (!$post['hours_scheduled'] && !$post['hours_locked'] && !$post['hours_audited']) {
            $this->addError("Please choose at least one type of hours to display (scheduled, locked or audited)");
            
            return false;
        } else {
            // we're good, check other validation stuff
            return parent::isValid($post);
        }
    }

    
    /**
     * Return user-legible set of fields/values for display in a Fisdap Report Summary
     */
    public function getReportSummary($config = array())
    {
        $showing = array();
        if ($config['hours_scheduled']) {
            $showing[] = $this->scheduledLabel;
        }
        if ($config['hours_locked']) {
            $showing[] = $this->lockedLabel;
        }
        if ($config['hours_audited']) {
            $showing[] = $this->auditedLabel;
        }
        
        return array('Showing hour type(s)' => implode(', ', $showing));
    }
}
