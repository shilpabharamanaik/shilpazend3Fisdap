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
 * This file contains a view helper to render a stylized information box
 */

/**
 * @package Fisdap
 */
class Fisdap_View_Helper_ErrorContainer extends Zend_View_Helper_Abstract
{
    /**
     * @var string
     */
    protected $_html= "";
    
    public function errorContainer($errors, $title = "", $additionalContent = "")
    {
        if (count($errors) == 0) {
            return "";
        }
        
        $this->_html = "<div class='error'>";
        
        if ($title) {
            $this->_html .= "<h4>$title</h4>";
        }
        
        if ($additionalContent) {
            $this->_html .= "<p>$additionalContent</p>";
        }
        
        $this->_html .= "<ul>";
        foreach ($errors as $error) {
            $this->_html .= "<li>" . $error . "</li>";
        }
        $this->_html .= "</ul>";

        $this->_html .= "</div>";
        
        return $this->_html;
    }
}
