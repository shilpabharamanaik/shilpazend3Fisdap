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
 * This file contains a view helper to render a help bubble
 */

/**
 * @package Fisdap
 *
 * @return string html
 */
class Zend_View_Helper_HelpBubble extends Zend_View_Helper_Abstract
{
    protected $_html;

    public function helpBubble($id, $title, $contents, $width = 450, $linkText = "")
    {
        $this->view->headScript()->appendFile("/js/jquery.cluetip.js");
        $this->view->headLink()->appendStylesheet("/css/jquery.cluetip.css");

        $this->view->jQuery()->addOnLoad(new Zend_Json_Expr("$('#$id').cluetip({activation: 'click',
			local:true, 
			cursor: 'pointer',
			width: $width,
			cluetipClass: 'jtip',
			sticky: true,
			closePosition: 'title',
			closeText: '<img width=\"25\" height=\"25\" src=\"/images/icons/delete.png\" alt=\"close\" />'});"));

        $this->_html = "<a id='$id' rel='#$id-popup' href='#$id-popup' title='$title'><img class='question-mark' src='/images/icons/question_mark.svg'>$linkText</a>";
        $this->_html .= "<div id='$id-popup' style='display: none;'>$contents</div>";

        return $this->_html;
    }
}
