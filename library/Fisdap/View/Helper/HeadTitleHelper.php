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
 * This file contains a view helper to render a the title in the <head> section
 * of the layout.
 */

/**
 * @package Fisdap
 *
 * @return string the title of the page
 */
class Zend_View_Helper_HeadTitleHelper extends Zend_View_Helper_Abstract 
{ 
    public function headTitleHelper() {
        $title = "Fisdap";
        
        if ($this->view->pageTitle) {
            $title .= " - " . $this->view->pageTitle;
        }
        
        return $title;
    }
}
