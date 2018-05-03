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
 * This file contains a view helper to render list of sites
 */

/**
 * @package Account
 */
class Account_View_Helper_ListSites extends Zend_View_Helper_Abstract
{
    /**
     * @var string the HTML to be rendered
     */
    protected $html = "";
    
    /**
     * The function to render the html
     *
     * @return string the HTML rendering the site table
     */
    public function listSites($available, $state = null)
    {
		$user = \Fisdap\Entity\User::getLoggedInUser();
		$programId = $user->getProgramId();
		$program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $programId);
		$stateName = \Fisdap_Form_Element_States::getFullName($state, $program->country);
		
		$this->view->headLink()->appendStylesheet("/css/library/Account/View/Helper/list-sites.css");
		$this->view->headScript()->appendFile("/js/library/Account/View/Helper/list-sites.js");
		$this->view->headLink()->appendStylesheet("/css/library/Account/View/Helper/sites-legend.css");
		
		$sites = $this->getSites($available, $state);
		$noActive = $available;
		
		if (count($sites) > 0) {
			$this->showSitesTable($sites, $noActive);
			return $this->_html;
		} else {
				if ($available) {
						$msg = "No one has set up any sites for $stateName yet. Be the first to create a new site.";
				} else {
						$msg = "No one has set up any sites for ".$program->name." yet. Click the \"Add site\" button below to get started.";
				}
			return "<div id='filterMessage' class='info'>$msg</div>";
		}
    }

	protected function getSites($available, $state)
	{
		$em = \Fisdap\EntityUtils::getEntityManager();
		$user = \Fisdap\Entity\User::getLoggedInUser();
		$programId = $user->getProgramId();
		$program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $programId);
		
		if ($available) {
			$rawSites = $em->getRepository('Fisdap\Entity\SiteLegacy')->getAvailableSitesByProgram($programId, $state);
		} else {
			$rawSites = $em->getRepository('Fisdap\Entity\SiteLegacy')->getSitesByProgram($programId);
		}

		$sitePartials = array();
		
		foreach($rawSites as $site) {
			$site['sharedSite'] = $program->getSharedStatus($site['id']);
			if (!$available) {
				$activeSite = $program->isActiveSite($site['id']);
				$site['activeSite'] = $activeSite;
				$sitePartials[] = array('site' => $site);
			} else {
				$sitePartials[] = array('site' => $site);
			}
		}
		
		return $sitePartials;
	}
	
	public function showSitesTable($sites, $noActive) {


		$this->_html .= '<table id="sites-table" class="tablesorter">
						    <thead>
								<tr id="titles">
									<th class="siteType"><span>Type</span></th>
									<th class="siteName"><span>Name</span></th>
									<th class="siteCity"><span>City</span></th>
									<th class="siteState"><span>State</span></th>';
									
		if (!$noActive) {
			$this->_html .= 		'<th class="siteActive"><span>Active</span></th>';
		}

	        $this->_html .= 		'<th class="siteShared"><span>Sharing</span></th>
								</tr>
							</thead>
							<tbody>';
	

		$this->_html .= $this->view->partialLoop('siteCells.phtml', $sites);
				$this->_html .= '</tbody></table>';
	}
}
