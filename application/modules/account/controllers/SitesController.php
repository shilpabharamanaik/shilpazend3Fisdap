<?php

class Account_SitesController extends Fisdap_Controller_Private
{

    public function init()
    {
		parent::init();
		
		// redirect to login if the user is not logged in yet
		if (!$this->user) {
		    return;
		}
		
		$this->view->user = $this->user;
		$this->view->program = $this->user->getCurrentProgram();
    }

	public function hasPermission()
	{
		$user = \Fisdap\Entity\User::getLoggedInUser();
		$instructor = $user->getCurrentRoleData();
		$is_instructor = ($user->getCurrentRoleName() == "instructor");
		return ($is_instructor) ?  $instructor->hasPermission("Edit Program Settings") : false;
	}

    public function indexAction()
    {
		$loggedInUser = \Fisdap\Entity\User::getLoggedInUser();
		$this->view->pageTitle = "Manage Sites";
		$this->view->action = "index";
		
		// do a few permissions checks. Is this an instructor who can edit sites?
		if(!$this->hasPermission()){
			$this->displayError("You do not have permission to view this page.");
			return;
		}
		
		$instructor = \Fisdap\Entity\User::getLoggedInUser()->getCurrentRoleData();
		$this->view->program = $instructor->program;
		
		$this->view->filterForm = new Account_Form_FilterSiteList();
		$this->view->isStaff = $loggedInUser->isStaff();
		$this->view->headScript()->appendFile("/js/tableSorter/jquery.tablesorter.min.js");
    }
	
	public function editAction()
	{
		// handle permissions: is the current logged in user an instructor w/ permission?
		if(!$this->hasPermission()){
			$this->displayError("You do not have permission to view this page.");
			return;
		}
		
		// parameters: do we have a site ID?
		$site_id = $this->_getParam("siteId");
		$site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $site_id);
		
		// page title
		$this->view->pageTitle = "Edit Site";	
		$this->view->pageTitleLinkURL = "/account/sites/";
		$this->view->pageTitleLinkText = "<< Back to ".$this->view->program->name."'s sites";
		
		if(!$site){
			$this->view->pageTitle = "We're sorry.";
			$this->displayError("We couldn't find the site you're looking for.");
			return;
		}
		
		$this->view->site = $site;
		$this->view->site_id = $site->id;
		$this->view->site_name = $site->name;
		$this->view->opened_tab = $this->_getParam("tab");
		$this->view->has_site = $this->view->program->hasSite($site);
		
		if (!$this->view->has_site) {
			$this->view->pageTitle = "We're sorry.";
		}
		
		// site info card
		$this->view->headLink()->appendStylesheet("/css/library/Account/View/Helper/site-info.css");
		$this->view->isActive = $this->view->program->isActiveSite($site->id);
		$this->view->siteInfoForm = new Account_Form_SiteInfo($site->id);
		
		// tabbed forms
		$base_department = ($site->type == "clinical") ? "Departments" : "Bases";
		$unformatted_base_depart = strtolower($base_department);
		$valid_tabs = array($unformatted_base_depart, "preceptors", "additionalstaff", "accreditationinfo", "sharingnetwork", "siterequirements");
		
		$this->view->forms = array($base_department => new Account_Form_Bases($site),
								    "Preceptors" => new Account_Form_Preceptors($site),
                                    "Additional Staff" => new Account_Form_SiteStaff($site),
								    "Accreditation Info" => new Account_Form_Accreditation($site),
								    "Sharing Network" => new Account_Form_Sharing($site),
								    "Site Requirements" => new Account_Form_Requirements($site),
								   );
		
		if(!in_array($this->view->opened_tab, $valid_tabs)){
			$this->view->opened_tab = $unformatted_base_depart;
		}

		$this->view->headScript()->appendFile("/js/jquery.fieldtag.js");
		$this->view->headLink()->appendStylesheet("/css/jquery.sliderCheckbox.css");
		$this->view->headScript()->appendFile("/js/jquery.sliderCheckbox.js");
		$this->view->headScript()->appendFile("/js/jquery.busyRobot.js");
		$this->view->headLink()->appendStylesheet("/css/jquery.busyRobot.css");
		$this->view->headScript()->appendFile("/js/jquery.maskedinput-1.3.js");
        $this->view->headLink()->appendStylesheet("/css/jquery.flippy.css");
		$this->view->headScript()->appendFile("/js/jquery.flippy.js");
	}
	
	public function addAction()
    {
		// do a few permissions checks. Is this an instructor who can edit sites?
		if(!$this->hasPermission()){
			$this->displayError("You do not have permission to view this page.");
			return;
		}
		
		$this->view->pageTitle = "Add Site";
		$this->view->pageTitleLinkURL = "/account/sites/";
		$this->view->pageTitleLinkText = "<< Back to ".$this->view->program->name."'s sites";
		$this->view->action = "add";
		$this->view->filterForm = new Account_Form_FilterSiteList();

		$this->view->headScript()->appendFile("/js/tableSorter/jquery.tablesorter.min.js");

    }

	public function addExistingSiteAction()
	{
		$site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $this->_getParam("site_id"));
		$this->_helper->json($this->view->program->addSite($site));
    }
	
	public function createAction()
    {
		// do a few permissions checks. Is this an instructor who can edit sites?
		if(!$this->hasPermission()){
			$this->displayError("You do not have permission to view this page.");
			return;
		}
		
		$this->view->pageTitle = "Create New Site";
		$this->view->siteForm = new Account_Form_SiteInfo();
		$this->view->headScript()->appendFile("/js/jquery.maskedinput-1.3.js");
		
    }
	
	public function saveSiteAction()
    {
		$formValues = $this->_getAllParams();
		$site_id = $this->_getParam("site_id");
		
		$form = new Account_Form_SiteInfo($site_id);
		$this->_helper->json($form->process($formValues));
    }
	
	public function generateSharingPermissionsFormAction()
	{
		$form = new Account_Form_SharingPermissionsModal($this->_getParam("programId"), $this->_getParam("siteId"));
		$this->_helper->json($form->__toString());
		
	}
	
	public function setSharingPermissionsAction()
	{
		$formValues = $this->_getAllParams();
		$form = new Account_Form_SharingPermissionsModal();
		$this->_helper->json($form->process($formValues));
		
	}
	
	public function generateRemoveSharingFormAction()
	{
		$form = new Account_Form_RemoveSharingModal($this->_getParam("programId"), $this->_getParam("siteId"));
		$this->_helper->json($form->__toString());
		
	}
	
	public function removeFromSharingAction()
	{	
		$formValues = $this->_getAllParams();
		$form = new Account_Form_RemoveSharingModal();
		$this->_helper->json($form->process($formValues));
		
	}
	
	public function sendSharingRequestAction()
	{
		$site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $this->_getParam("siteId"));
		$site->sendSharingRequest($this->view->program->id);
		$contacts = $site->getSharingContactInfo();
		if ($site->hasNetwork()) {
		    $action = "join the";
		    $contact_info = ":<ul id='contact-list'>";
		    foreach ($contacts as $contact) {
		        $contact_info .= "<li>".$contact['name']." at ".$contact['email']."</li>";
		    }
		    $contact_info .= "</ul>";
		} else {
		    $action = "set up a";
		    $contact_info = " Fisdap at support@fisdap.net.";
		}
		
		$message = "You have requested to $action sharing network at ".$site->name.".<br>".
			   "If you do not receive a response within two business days, please contact$contact_info";
		$this->_helper->json($message);
		
	}
	
	public function filterSitesAction()
	{
		$state = $this->_getParam('state', 0);
		
		if (!$state) {
			$state = null;
		}
		
		$results = $this->view->listSites(true, $state);
		if (!$results) {
			$state = \Fisdap_Form_Element_States::getFullName($state, $this->view->program->country);
			$message = "<div id='filterMessage'>Be the first to add a site for " . $state . ".</div>";
			$this->_helper->json($message);
		} else {
			$this->_helper->json($results);
		}
	}

	public function saveNewSiteAction(){
		$formValues = $this->_getAllParams();
		$form = new Account_Form_NewSiteModal();
		$this->_helper->json($form->process($formValues));
	}

	public function toggleActiveAction()
	{
		$site_id = $this->_getParam("site");
		$active = $this->_getParam("active");
		$site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $site_id);
		
		$program = $this->view->program;
		
		$this->_helper->json($program->toggleSite($site, $active));
	}
	
	public function getSimpleSiteInfoAction() {
		$site_id = $this->_getParam("site_id");
		$site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $site_id);
		$this->_helper->json($this->view->partial('site-info.phtml', array("site" => $site)));
	}
	
	public function getSiteInfoFormAction() {
		$site_id = $this->_getParam("site_id");
		$form = new Account_Form_SiteInfo($site->id);
		$this->_helper->json($form->__toString());
	}
	
	public function updateSiteRequirementsAction() {
		$site_id = $this->_getParam("site_id");
		$site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $site_id);

		$formValues = $this->_getAllParams();
		$form = new Account_Form_Requirements($site);
		$this->_helper->json($form->process($formValues));
	}

}
