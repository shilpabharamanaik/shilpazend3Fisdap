<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2014.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * This file contains a view helper to render an export button
 */

/**
 * @package Fisdap
 *
 * @return string html
 */
class Zend_View_Helper_ExportButtons extends Zend_View_Helper_Abstract 
{
	protected $_html;
	
    public function exportButtons($types, $id = "export-links", $visible=true) {
		$this->view->headScript()->appendFile("/js/library/Fisdap/View/Helper/export-buttons.js");
		$this->view->headLink()->appendStylesheet("/css/library/Fisdap/View/Helper/export-buttons.css");

		if ($visible) {
			$hideAddAndFilter = "style='display:block;'";
		} else {
			$hideAddAndFilter = "style='display:none;'";
		}

		$this->_html = "<div id='$id' class='export-links extra-small gray-button no-pdf' ".$hideAddAndFilter.">";
		
		// add the buttons and associated files
		foreach ($types as $type) {
			// add the javascript for this button type
			$this->view->headScript()->appendFile("/js/library/Fisdap/Utils/create-$type.js");
			
			switch ($type) {
				case "pdf":
					$title = "printer friendly";
					break;
				case "csv":
					$title = "spreadsheet friendly";
					break;
			}
			
			// add the button
			$this->_html .= "<a href='#' class='".$type."Link export-button' title='$title'>".
								"<img src='/images/icons/$type-square-icon.png'>".strtoupper($type).
							"</a>";
		}
		
		$this->_html .= "</div>";
		
		return $this->_html;
    }
}