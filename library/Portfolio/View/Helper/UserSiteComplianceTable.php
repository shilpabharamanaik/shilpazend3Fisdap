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
 * View Helper to display a table of requirement attachments for a give user
 */

/**
 * @package Portfolio
 */
class Portfolio_View_Helper_UserSiteComplianceTable extends Zend_View_Helper_Abstract 
{
	protected $_html;
	
	public function userSiteComplianceTable($user_context, $site, $attachments, $filterBy, $shared = false)
	{
		$date_format = "M j, Y";
		$req_associations = \Fisdap\EntityUtils::getRepository("Requirement")->getRequirementAssociations($user_context->program->id);
		
		$this->_html = "";
		
		if (count($attachments) < 1) {
			switch ($filterBy) {
				case "all-reqs":
					$status = " has no assigned requirements.";
					break;
				case "pending":
					$status = " has no requirements in progress.";
					break;
				case "compliant-only":
					$status = " is not compliant for any assigned requirements.";
					break;
				case "non-compliant-only":
					$status = " has no non-compliant requirements.";
					break;
			}
			$this->_html .= "<div class='none-found'>".$user_context->user->getName() . $status."</div>";
		} else {
			$this->_html .= "<div id='table-container'><table class='attachment-row'>";

			// loop through all the attachments		
			foreach ($attachments as $attachment) {
				$status_class = $status = $attachment->getStatus();
				$associations = $req_associations[$attachment->requirement->id];
				$is_program = $associations['program'] ? "<img src='/images/icons/program-requirement.png'>" : "";
				$is_site = $associations['site'][$site->type][$site->id]['local'] ? "<img src='/images/icons/site-requirement.png'>" : "";
				$is_shared = ($shared && $associations['site'][$site->type][$site->id]['global']) ? "<img src='/images/icons/shared-requirement.png'>" : "";
				switch ($status) {
					case "in progress":
						$date = "due: ".$attachment->due_date->format($date_format);
						$status_class = "pending";
						break;
					case "compliant":
						$date = $attachment->expiration_date ? "exp: ".$attachment->getExpirationDate($date_format) : "";
						break;
					case "non-compliant":
						$date = $attachment->completed ? "expired ".$attachment->getExpirationDate($date_format) : "past due ".$attachment->due_date->format($date_format);
						break;
				}
				$this->_html .=	"<tr class='$status_class'>
							<td class='req_col'>".
								$attachment->requirement->name.
							"</td>
							<td class='status_col'>$status</td>
							<td class='expiration_col'>$date</td>
							<td class='icon_col'>$is_program</td>
							<td class='icon_col'>$is_site</td>
							<td class='icon_col'>$is_shared</td>
						</tr>";
			}

			$this->_html .= "</table></div>
					<div id='table-legend'>
						<div class='legend-entry'><img src='/images/icons/program-requirement.png'> Program requirement</div>
						<div class='legend-entry'><img src='/images/icons/site-requirement.png'> Site requirement</div>
						<div class='legend-entry'><img src='/images/icons/shared-requirement.png'> Shared site requirement</div>
					</div>";
		}

		return $this->_html;
	}
}
