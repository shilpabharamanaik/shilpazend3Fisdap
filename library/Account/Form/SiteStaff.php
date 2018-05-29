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
 * @author     khanson
 * @package    Account
 * @subpackage Forms
 */
class Account_Form_SiteStaff extends Fisdap_Form_Base
{
	/**
	 * @var \Fisdap\Entity\SiteLegacy the site that these staff members belong to
	 */
	public $site;

	/**
	 * @var \Fisdap\Entity\User this logged in user
	 */
    public $user;

	/**
	 * @var \Fisdap\Entity\ProgramLegacy the logged in user's program
	 */
    public $program;

	/**
	 * @var bool does this user have admin permissions for this site?
	 */
    public $site_admin;

	/**
	 * @var Account_Form_Modal_SiteStaffMemberModal the modal to add/edit a site staff member
	 */
	public $staff_member_modal;

	/**
	 * @var array the \Fisdap\Entity\SiteStaffMember entities associated with this site for this program/network
	 */
    public $staff_members;

	/**
	 * Set the properties we know about
	 *
	 * @param \Fisdap\Entity\SiteLegacy $site the current site
	 */
	public function __construct(\Fisdap\Entity\SiteLegacy $site)
	{
		$this->site = $site;
		$this->user = \Fisdap\Entity\User::getLoggedInUser();
		$this->program = $this->user->getProgram();
		$this->site_admin = (!$this->program->sharesSite($this->site->id) || $this->program->isAdmin($this->site->id));
		
		$this->staff_member_modal = new Account_Form_Modal_SiteStaffMemberModal($site);
		
		parent::__construct();
	}

	/**
	 * initialize the form
	 *
	 * @throws Zend_Form_Exception
	 */
	public function init()
	{
		parent::init();
		
		$this->addJsFile("/js/library/Account/Form/site-sub-forms/site-staff.js");
		$this->addCssFile("/css/library/Account/Form/site-sub-forms/site-staff.css");
		
		$search = new Zend_Form_Element_Text("search_staff_members");
		$search->setAttribs(array("class" => "fancy-input search-accordion hide-when-no-accordion", "title" => "Type a contact name or title to search..."));
		$this->addElement($search);

        // get all the programs whose staff members we care about
        $program_ids = $this->program->getNetworkPrograms($this->site);
        $program_ids[] = $this->program->id;
		
		$staff_members = \Fisdap\EntityUtils::getRepository("SiteStaffMember")->getStaffMembersBySiteAndProgram($this->site, $program_ids);
		$this->staff_members = $staff_members;
		
		// Set the decorators for the form
		$this->setDecorators(array(
			'FormErrors',
			'PrepareElements',
			array('ViewScript', array('viewScript' => 'forms/site-sub-forms/site-staff.phtml')),
			'Form'
		));
		
	}

	/**
	 * This "form" is never submitted, so there is no processing to do. The form is just a handy container for the
	 * search input and the site staff member accordion.
	 *
	 * @param $data
	 * @return bool
	 */
	public function process($data)
	{
		return true;
	} // end process()
}
