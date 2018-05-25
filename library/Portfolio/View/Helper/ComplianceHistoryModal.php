<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2013.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * This helper will display a modal display history records for a given requirement
 */

/**
 * @package Portfolio
 */
class Portfolio_View_Helper_ComplianceHistoryModal extends Zend_View_Helper_Abstract 
{
	protected $_html;
	
	public function complianceHistoryModal($attachmentId)
	{
		$attachment = \Fisdap\EntityUtils::getEntity("RequirementAttachment", $attachmentId);
		$historySummaries = $attachment->getHistorySummary();
		
		$this->_html = "<h3 class='section-header'>" . $attachment->requirement->name . "</h3>";
		$this->_html .= "<table class='fisdap-table'>";
		$this->_html .= "<thead><tr><td>Date</td><td>Action</td></tr></thead>";
		$this->_html .= "<tbody>";
		foreach($historySummaries as $history) {
			$this->_html .= "<tr><td>" . $history['datetime']->format('M j, Y, H:i') ."</td><td>" . $history['summary'] . "</td></tr>";
		}
		$this->_html .= "</tbody>";
		$this->_html .= "</table>";
		
		return $this->_html;
	}
}