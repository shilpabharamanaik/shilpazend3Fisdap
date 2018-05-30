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
 * Custom Respirations Prompt
 */

/**
 * Class creating a composite respirations element
 *
 * @package SkillsTracker
 */
class SkillsTracker_Form_Element_Respirations extends Zend_Form_Element_Xhtml
{
    /**
     * @var string the resp rate
     */
    protected $_rate;
    
    /**
     * @var \Fisdap\Entity\VitalRespQuality
     */
    protected $_quality;
    
    /**
     * @var string the view helper that will render this composite element
     */
    public $helper = "respirationsElement";
    
    /**
     * Set the value of this form element
     *
     * @param array the resp quality and rate
     * @return SkillsTracker_Form_Element_Respirations the form element
     */
    public function setValue($value)
    {
        $this->_rate = $value['rate'];
        $this->_quality = \Fisdap\Entity\VitalRespQuality::id_or_entity_helper($value['quality'], 'VitalRespQuality');
        
        return $this;
    }
    
    /**
     * returns the value of this respirations prompt
     * @return array the pulse rate and quality
     */
    public function getValue()
    {
        return array('rate' => $this->_rate, 'quality' => $this->_quality->id);
    }
}
