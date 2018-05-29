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
 * Custom Subject Prompt
 */

/**
 * Class creating a composite subject element
 *
 * @package SkillsTracker
 */
class SkillsTracker_Form_Element_Subject extends Zend_Form_Element_Xhtml
{
    /**
     * @var string the name of the subject
     */
    protected $_subjectName;
    
    /**
     * @var sgring the type of subject 
     */
    protected $_type;
    
    /**
     * @var string the view helper that will render this composite element
     */
    public $helper = "subjectElement";
    
    public function init()
    {
        //jquery setup
        if (!$this->_view) {
            $this->_view = $this->getView();
        }
		
		//add js file to do cool input masking
        $this->_view->headScript()->appendFile("/js/library/SkillsTracker/Form/Element/subject.js");
    }
    
    /**
     * Set the value of this form element
     *
     * @param mixed the ID of a subject or an array of its values
     * @return SkillsTracker_Form_Element_Subject the form element
     */
    public function setValue($value)
    {
        if (is_array($value)) {
            $this->_subjectName = $value['name'];
            $this->_type = $value['type'];
        } else {
            $subject = \Fisdap\EntityUtils::getEntity('Subject', $value);
        
            $this->_subjectName = $subject->name;
            $this->_type = $subject->type;
        }
        
        return $this;
    }
    
    /**
     * returns the value of this subject
     * @return int the ID of the subject
     */
    public function getValue()
    {
        $em = \Fisdap\EntityUtils::getEntityManager();
        $subject = $em->getRepository('Fisdap\Entity\Subject')->findOneBy(array('name' => $this->_subjectName, 'type' => $this->_type));
        
        return $subject->id;
    }
}
