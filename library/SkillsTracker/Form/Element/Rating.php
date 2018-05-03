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
 * Custom Rating Prompt
 */

/**
 * Class creating a composite rating element
 *
 * @package SkillsTracker
 */
class SkillsTracker_Form_Element_Rating extends Zend_Form_Element_Xhtml
{
    /**
     * @var integer rating
     */
    protected $_rating;
    
    /**
     * @var boolean disabled
     */
    protected $_disabled;

    /**
     * @var string the view helper that will render this composite element
     */
    public $helper = "ratingElement";
    
    /**
     * Set the value of this form element
     *
     * @param mixed either the array of rating and disabled, or just the rating
     * @return SkillsTracker_Form_Element_Rating the form element
     */
    public function setValue($value)
    {
        if (is_array($value)) {
            $this->_rating = $value['rating'];
            $this->_disabled = $value['disabled'];
        } else {
            $this->_rating = $value;
            if ($this->_rating == -1) {
                $this->_disabled = true;
            } else {
                $this->_disabled = false;
            }
        }
        
        return $this;
    }
    
    /**
     * returns the value of this object
     * @return either an integer or null
     */
    public function getValue()
    {
        if ($this->_disabled) {
            return -1;            
        } else {
            return $this->_rating;
        }
    
    }
}
