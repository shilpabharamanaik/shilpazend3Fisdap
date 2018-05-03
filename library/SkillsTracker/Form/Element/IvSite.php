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
 * Custom Iv Site Prompt
 */

/**
 * Class creating a composite iv site element
 *
 * @package SkillsTracker
 */
class SkillsTracker_Form_Element_IvSite extends Zend_Form_Element_Xhtml
{
    /**
     * @var string the name of the iv site
     */
    protected $_siteName;
    
    /**
     * @var string the side of the iv site 
     */
    protected $_side;
    
    /**
     * @var string the view helper that will render this composite element
     */
    public $helper = "ivSiteElement";
    
    public function init()
    {
        //jquery setup
        if (!$this->_view) {
            $this->_view = $this->getView();
        }
		
		//add js file to do show/hiding
        $this->_view->headScript()->appendFile("/js/library/SkillsTracker/Form/Element/iv-site.js");
    }
    
    /**
     * Set the value of this form element
     *
     * @param mixed the ID of a subject or an array of its values
     * @return SkillsTracker_Form_Element_IvSite the form element
     */
    public function setValue($value)
    {
        if (is_array($value)) {
            $this->_siteName = $value['name'];
            $this->_side = $value['side'];
        } else {
            $ivSite = \Fisdap\EntityUtils::getEntity('IvSite', $value);

            $this->_siteName = $ivSite->name;
            $this->_side = $ivSite->side;
        }
		
		if ($this->_siteName == "other") {
			$this->_side = null;
		}
        
        return $this;
    }
    
    /**
     * returns the value of this iv site
     * @return int the ID of the iv site
     */
    public function getValue()
    {
        $em = \Fisdap\EntityUtils::getEntityManager();
		
		$params = array('name' => $this->_siteName);
		if ($this->_side) {
			$params['side'] = $this->_side;
		}
		
        $ivSite = $em->getRepository('Fisdap\Entity\IvSite')->findOneBy($params);
        
        return $ivSite->id;
    }
}
