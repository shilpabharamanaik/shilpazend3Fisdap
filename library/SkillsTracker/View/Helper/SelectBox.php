<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
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
 * This file contains a view helper to render a blood pressure prompt
 */

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_SelectBox extends Zend_View_Helper_Abstract
{
    /**
     * @var string the HTML to be rendered
     */
    protected $_html = "";
    
    /**
     * The function to render the html
     *
     * @return string the HTML rendering the age date element
     */
    public function selectBox($program = null, $certificationLevel = null)
    {
        $this->view->headScript()->appendFile("/js/library/SkillsTracker/View/Helper/select-box.js");
        $this->view->headLink()->appendStylesheet("/css/library/SkillsTracker/View/Helper/select-box.css");
        
        $this->_html = "<div class='selectBox'>
            <div id='addIcon'>+</div> 
           <span class='selected' value='Open New Skill'></span>
           <span class='selectArrow'>&#9660</span>
                        
           <div class='selectOptions' >
              <span id='open' class='selectOption' value='Open New Skill'>Open New Skill</span>
              <span class='selectOption' value='Option 1'>Option 1</span>
              <span class='selectOption' value='Option 2'>Option 2</span>
              <span class='selectOption' value='Option 3'>Option 3</span>
           </div>
        </div>";
        
        return $this->_html;
    }
}
