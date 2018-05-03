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
 * This helper takes in a shift and provides a link to google maps for it,
 * or a warning message if one cannot be determined.
 */

/**
 * @package SkillsTracker
 */
class Fisdap_View_Helper_MapLinkHelper extends Zend_View_Helper_Abstract 
{
	/**
	 * @var string the html to be rendered
	 */
	protected $_html;
	
	/**
	 * @param array $items Array of items to display in the list.
	 * 
	 * @param integer $columnCount Number of columns to display.
	 *
	 * @return string the shift list rendered as an html table
	 */
	public function mapLinkHelper($shift)
	{
		$html = "";
		
		if($shift->site->hasValidMapAddress($shift)){
			$html .= '<a class="small-link" target="_blank" href="http://maps.google.com/maps?q=' . $shift->site->getMapAddress($shift) . '">Map it</a> <img src="/images/icons/new_window_link.gif" />';
		}else{
			if ($shift->type != 'field') {
				$site_name = $shift->site->name;
				$site_type = "site";
			} else {
				$site_name = $shift->site->name . " (" . $shift->base->name . ")";
				$site_type = "base";
			}
			$msg = "We don't currently have enough information to show you a map for $site_name. ";

			$this_user =  \Fisdap\Entity\User::getLoggedInUser();
			if ($this_user->isInstructor()) {
				if ($this_user->hasPermission("Edit Program Settings")) {
					$admin_link = Util_GetLegacyTopNavLinks::getLink(Util_GetLegacyTopNavLinks::ADMIN, $this->view->serverUrl());
					$msg .= "To update the address, please go to the <a href='$admin_link'>Account</a> page in Fisdap or call 651.690.9241 for help.";
				} else {
					$msg .= "Please ask your Fisdap coordinator or program director to update the address in Fisdap.";
				}
			} else {
				$msg .= "Please contact your instructor to have them correctly configure the address for this $site_type.";
			}
			$html .= '<a class="small-link" id="bad-map-link" href="#">Map it</a> <img src="/images/icons/new_window_link.gif" />';
			$html .= '<div id="alert-modal"></div>';
			$html .= '<script>
				$("#bad-map-link").click(function(e){
					$("#alert-modal").dialog({
						autoOpen: false, 
						width: 400, 
						height: 225,
						resizable: false,
						modal: true
					});
					
					$("#alert-modal").html("'.$msg.'");
					
					$("#alert-modal").dialog("open");

					return false;
				});
			</script>';
		}

		return $html;
	}
}
