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
 * This helper will display a student search box
 */

/**
 * @package Mobile
 */
class Mobile_View_Helper_MobileStudentPicker extends Zend_View_Helper_Abstract
{
    /**
     * @var string the html to be rendered
     */
    protected $_html;
    
    /**
     * @param array $runs array of arrays containing each run to be
     * rendered in a view for a mobile device
     *
     * @return string the run list rendered as an html table
     */
    public function mobileStudentPicker($searchString = null)
    {
        $this->view->headScript()->appendFile("/js/library/Mobile/View/Helper/mobile-student-picker.js");
        
        $this->_html = "<div id='mobile-student-picker'>";
        $this->_html .= "<h2 class='page-title'>Find Student</h2>";
        $this->_html .= "<div class='island' style='margin-bottom:.5em;'>";
        $this->_html .= $this->view->formLabel("searchString", "Name:");
        $this->_html .= $this->view->formText("searchString", "");
        $this->_html .= $this->view->formSubmit("find-student-btn", "Go!");
        $this->_html .= "<img id='throbber' style='margin-left: .5em;' src='/images/throbber_small.gif'>";
        $this->_html .= "<div id='student-search-results'></div>";
        $this->_html .= "</div>";
        $this->_html .= "</div>";
        
        return $this->_html;
    }
}
