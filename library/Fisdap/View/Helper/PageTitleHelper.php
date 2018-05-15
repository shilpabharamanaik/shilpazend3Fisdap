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
 * This file contains a view helper to render a page title
 *
 * Example usage:
 * In your controller
 * 		$this->view->pageTitle = "Page Title!!!"
 * 		$this->view->pageTitleLinks = array("This is a link" => "www.example.com");
 */

/**
 * @package Fisdap
 */
class Zend_View_Helper_PageTitleHelper extends Zend_View_Helper_Abstract
{
    /**
     * @var string
     */
    protected $_html= "";
    
    /**
     * @var string
     */
    protected $_title = "";


    /**
     * @param string $title
     * @param null   $additionalContent
     * @param null   $breadCrumb
     *
     * @return string
     */
    public function pageTitleHelper($title = "", $additionalContent = null, $breadCrumb = null)
    {
        if ($this->view->pageTitle) {
            $this->_title = $this->view->pageTitle;
        }
        
        if ($title) {
            $this->_title = $title;
        }

        if ($this->_title) {
            $this->_html = "<h1>";
            if ($breadCrumb) {
                $this->_html .= "<div id='breadcrumb'>$breadCrumb</div>";
            }
            $this->_html .= $this->_title . $additionalContent;
            $this->_html .= "</h1>";

            return $this->_html;
        }
    }
}
