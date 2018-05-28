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
 * This helper will display a modal showing which sites a user is cleared for 
 */

/**
 * @package Portfolio
 */
class Portfolio_View_Helper_ClearedSitesModal extends Zend_View_Helper_Abstract 
{
	
	/**
	 * @var string the html to be rendered
	 */
	protected $_html;
	
	// will create an empty modal
	public function clearedSitesModal()
	{
		$this->view->headLink()->appendStylesheet("/css/library/Portfolio/View/Helper/clearedSitesModal.css");

		$this->_html =  "<div id='sites-modal'>";
		$this->_html .= 	"<div id='sites-modal-content'></div>";
		$this->_html .= "</div>";
		
		return $this->_html;
	}
	
	// generates the content for the modal
	public function generateClearedSites($sites, $userContextId)
	{
		$user_context = \Fisdap\EntityUtils::getEntity("UserContext", $userContextId);
		
		$returnContent  = "<div id='type-filter'>";
		$returnContent .= "	<input type='radio' id='all' name='site_type' checked='checked'><label for='all'>All</label>";
		$returnContent .= "	<input type='radio' id='clinical' name='site_type'><label for='clinical'>Clinical</label>";
		$returnContent .= "	<input type='radio' id='field' name='site_type'><label for='field'>Field</label>";
		$returnContent .= "	<input type='radio' id='lab' name='site_type'><label for='lab'>Lab</label>";
		$returnContent .= "</div>";
		$returnContent .= "<h3 class='section-header'>".$user_context->user->first_name." is compliant to go to:</h3>";
		
		// make the table
		$returnContent .= "<div id='tableWrapper'>";
		$returnContent .= 	"<table class='sites-table'>";
		$returnContent .= 		"<tbody>";
	
		foreach($sites as $site) {
			$returnContent .= 		"<tr>";
			$returnContent .= 			"<td class='site-icon'><img src='/images/icons/".$site->type."SiteIconColor.png'></td>";
			$returnContent .= 			"<td class='".$site->type."'>".$site->name."</td>";
			$returnContent .= 			"<td class='address'>".$site->getSiteAddress()."</td>";
			$returnContent .= 		"</tr>";
		}
		
		$returnContent .= 		"</tbody>";
		$returnContent .= 	"</table>";
		$returnContent .= "</div>";
		
		$returnContent .= "<div id='table-legend'>";
		$returnContent .= "	<div class='legend-entry'><img src='/images/icons/clinicalSiteIconColor.png'> Clinical Site</div>";
		$returnContent .= "	<div class='legend-entry'><img src='/images/icons/fieldSiteIconColor.png'> Field Site</div>";
		$returnContent .= "	<div class='legend-entry'><img src='/images/icons/labSiteIconColor.png'> Lab Site</div>";
		$returnContent .= "</div>";

		$returnContent .= "<div class='small gray-button'>";
		$returnContent .= "<button id='sitesCloseButton'>Ok</button>";
		$returnContent .= "</div>";
		
		return $returnContent;
	}

}
