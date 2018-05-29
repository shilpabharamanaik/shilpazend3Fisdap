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
 * View Helper to display an accordion of accreditation info for a given group of sites
 */

/**
 * @package Fisdap
 */
class Fisdap_View_Helper_AccreditationInfoAccordion extends Zend_View_Helper_Abstract 
{
	protected $_html;
	
	public $view;
	
	public function __construct($view = null)
	{
		if ($view) {
			$this->view = $view;
		}
	}
	
	public function accreditationInfoAccordion($config, $options = array())
	{
		// if a config is set, use those values for selected fieldsthe site ids
		if (!empty($config)) {
			$options['selected_sites'] = $config['sites_filters'];
		}
		
		$site_ids = \Fisdap\EntityUtils::getRepository('SiteLegacy')->parseSelectedSites($options['selected_sites']);
		$sites = (count($site_ids) > 0) ? \Fisdap\EntityUtils::getRepository("SiteLegacy")->findById($site_ids) : array();
		@usort($sites, array('self', 'sortSitesByTypeName'));

		// JS / CSS for the view helper
		$this->view->headLink()->appendStylesheet('/css/accordion.css');
		$this->view->headScript()->appendFile("/js/accordion.js");
		$this->view->headLink()->appendStylesheet('/css/library/Fisdap/View/Helper/accreditation-info-accordion.css');
        $this->view->headScript()->appendFile("/js/library/Fisdap/View/Helper/accreditation-info-accordion.js");
		$this->view->headLink()->appendStylesheet("/css/library/Account/Form/site-sub-forms/accreditation.css");
		$this->view->headScript()->appendFile("/js/library/Account/Form/site-sub-forms/accreditation.js");
		$this->view->headScript()->appendFile("/js/jquery.maskedinput-1.3.js");
		
		$this->_html = "<div class='accordionContainer accreditation-accordion'>";
		// loop through all the sites		
		foreach ($sites as $site_id => $site) {
			$accred_info = $site->getAccreditationInfoByProgram(\Fisdap\Entity\User::getLoggedInUser()->getProgramId());
//var_dump($accred_info);
			$incomplete = (empty($accred_info) || !$accred_info->isComplete()) ? true : false;
			if ($incomplete) {
				$status_class = "warning";
				$accred_desc = "incomplete accreditation information";
			} else {
				$status_class = "complete";
				$accred_desc = $accred_info->cao . ", " . $accred_info->phone;
			}
			$this->_html .=
				"<div class='accordionHeader $status_class'>
					<div class='arrowImg'><img src='/images/accordion_arrow_right.png'></div>
					<div class='headerTitle'>
						<div class='".$site->type." site-name'>".$site->name."</div>
						<div class='accred-info'>$accred_desc</div>
					</div>
				</div>
				
				<div class='accordionContent'>
					<div class='edit-btn-wrapper small'>
						<a href='#' class='edit-accred-info-btn extra-small' data-siteid='" . $site->id . "'>Edit</a>
					</div>";
			$this->_html .= $this->view->partial('accreditation-info.phtml', 'default', array('accred_info' => $accred_info, 'site' => $site));
			$this->_html .=
				"</div>
				<div class='clear'></div>";
		}
		$this->_html .= "</div>";
		
		// add the edit modal div
		$this->_html .= "<div id='edit-modal-container'>
							<h3 class='section-header'>Site - Accreditation Info</h3>
							<div id='accreditation_submit_messages'></div>
							<div id='accreditationinfo'>
							</div>
							<div class='modal-buttons'>
								<div class='small green-buttons'>
									<a id='save-accred-info-button' href='#' data-siteid=''>Save</a>
								</div>
								<div class='small gray-button'>
									<a id='cancel-accred-info-button' href='#'>Cancel</a>
								</div>
							</div>
						</div>";
		return $this->_html;
	}
	
	public static function sortSitesByTypeName($a, $b){
		if($a->type == $b->type){
			return ($a->name < $b->name ? -1 : 1);
		}

		return ($a->type < $b->type ? -1 : 1);
	}
	
	public function accreditationInfoAccordionSummary($options = array(), $config = array())
    {
		return array();
	}
	
	public function accreditationInfoAccordionValidate($options = array(), $config = array())
    {
		return array();
	}
}
