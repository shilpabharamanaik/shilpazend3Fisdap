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
 * This file contains a view helper to a quick links box
 */

/**
 * @package Fisdap
 */
class Fisdap_View_Helper_QuickLinksHelper extends Zend_View_Helper_Abstract
{
    /**
     * @var string the html to be returned
     */
    protected $_html;
    
    /**
     * @param array $links an array of links
     * @return string html to render a quick links box
     */
    public function quickLinksHelper($links = array())
    {
        if (empty($links)) {
            return null;
        }
        
        $this->_html = "<div id='quick-links'><h4>Quick Links</h4><ul>";
        
        foreach ($links as $title => $url) {
            $this->_html .= "<li style='padding: 2px;'><a href='$url'>$title</a></li>";
        }
        
        $this->_html .= "</ul></div>";
        
        return $this->_html;
    }
}
