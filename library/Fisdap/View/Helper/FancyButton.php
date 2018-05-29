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
 * This file contains a view helper to render a stylized button
 */

/**
 * @package Fisdap
 */
class Fisdap_View_Helper_FancyButton extends Zend_View_Helper_Abstract 
{
	/**
	 * @var string
	 */
	protected $_html= "";
	
    public function fancyButton($id, $text, $url, $color = 'gray-button', $size = "medium") {
        $this->view->jQuery()->addOnLoad("$('#$id').button().parent().addClass('$color $size');");
        
        $this->_html = "<div style='display:inline;'><a href='$url' id='$id'>$text</a></div>";
        return $this->_html;
    } 
}