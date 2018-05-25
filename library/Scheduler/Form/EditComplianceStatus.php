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
use Fisdap\Data\Requirement\RequirementRepository;

/**
 * @package    Scheduler
 * @subpackage Forms
 */
class Scheduler_Form_EditComplianceStatus extends Fisdap_Form_Base
{
	/**
	 * @var RequirementRepository
	 */
	private $requirementRepository;

	/**
	 * @var array containing form values from the select form
	 */
	public $selectFormValues;
	
	/**
	 * @var array containing the filtered attachments grouped by requirement
	 */
	public $requirementAttachments;
	
	/**
	 * @var array containing the filtered attachments grouped by user
	 */
	public $userAttachments;


	/**
	 * @param RequirementRepository $requirementRepository
	 * @param array                 $selectFormValues
	 */
	public function __construct(RequirementRepository $requirementRepository, $selectFormValues = [])
	{
		$this->requirementRepository = $requirementRepository;
		$this->selectFormValues = $selectFormValues;

		parent::__construct();
	}


	public function init()
	{
		parent::init();
		
		$this->setAttrib("id", "edit-compliance-status");

		// figure out how we're ordering the attachments
		$selectBy = ($this->selectFormValues['selection-by'] == "by-people") ? "People" : "Requirement";
		$sortMethod = ($this->selectFormValues['selection-by'] == "by-people") ? "sortAttachmentsByRequirement" : "sortAttachmentsByPeople";
		
		// set up the data arrays
		foreach($this->selectFormValues['requirementIds'] as $requirementId) {
			$req = \Fisdap\EntityUtils::getEntity("Requirement", $requirementId);
			$reqs[$req->name] = array('attachments'=>array(),
									  'req-type' => $req->getType(),
									  'sectionTitle' => $req->name);
		}
		uksort($reqs, "strnatcasecmp");
		$this->requirementAttachments = $reqs;

		$attachments = $this->requirementRepository->getRequirementAttachmentsByUserContexts($this->selectFormValues['userContextIds'], $this->selectFormValues['requirementIds']);
		uasort($attachments, array("self", $sortMethod));

		// get info about each attachment and add the appropriate form elements for each
		$userContextsWithAttachments = [];

		foreach ($attachments as $attachment) {
			//Calculate expired and due dates
			$attachment['expired'] = $attachment['expiration_date'] ? $attachment['expiration_date'] <= new \DateTime() : false;
			$attachment['in_progress'] = $attachment['due_date'] ? $attachment['due_date'] > new \DateTime() : true;

			// add the slider switch showing the compliance status for this attachment
			$compliant = new Zend_Form_Element_Checkbox("completed_" . $attachment['id']);
			$compliant->setLabel("Compliant")
				->setValue($attachment['completed'])
				->setDecorators(self::$checkboxDecorators)
				->setAttrib("data-attachmentid", $attachment['id']);
			if ($attachment['in_progress']) {
				$compliant->setAttrib("class", "to-do");
			}
			if ($attachment['expired']) {
				$compliant->setAttrib("class", "expired");
				$compliant->setValue(0);
			}
			$this->addElement($compliant);

			// add a flag to track whether or not this requirement is being renewed
			$renewed = new Zend_Form_Element_Hidden("renewed_" . $attachment['id']);
			$renewed->setDecorators(array("ViewHelper"))
				->setValue(0);
			$this->addElement($renewed);

			// if this is the kind of requirement that expires, add a date picker for the expiration date
			if ($attachment['expires']) {
				$expirationDate = new Zend_Form_Element_Text("expirationDate_" . $attachment['id']);

				// hide the expiration date picker on load if the attachment is compliant
				$expDateDecorator = ($attachment['completed']) ? self::$basicElementDecorators : self::$basicHiddenElementDecorators;

				$expirationDate->setLabel("exp:")
					->setDecorators($expDateDecorator)
					->setAttrib("class", "selectDate expirationDate");
				if ($attachment['expiration_date'] instanceof \DateTime) {
					$expirationDate->setValue($attachment['expiration_date']->format("m/d/Y"));
				}
				$this->addElement($expirationDate);
			}

			// some some info for the labelling of things
			$userCertification = $attachment['user_context_certification'] ? $attachment['user_context_certification'] : "Instructor";
			$userContextDescription = $attachment['first_name'] . " " . $attachment['last_name'] . ", " . $userCertification;
			$reqDescription = $attachment['req_name'];

			// Label and store the list of attachment IDs depending on what mode we're in
			if ($selectBy == "People") {
				//Set a title for each compliance toggle
				$attachment['compliance_title'] = $reqDescription;

				$userSortKey = $attachment['last_name'] . $attachment['first_name'] . $userCertification . ":" . $attachment['userContextId'];
				$this->userAttachments[$userSortKey]['attachments'][] = $attachment;
				$this->userAttachments[$userSortKey]['sectionTitle'] = $userContextDescription;

				$userContextsWithAttachments[] = $attachment['userContextId'];

			} else {
				//Set a title for each compliance toggle
				$attachment['compliance_title'] = $userContextDescription;

				$this->requirementAttachments[$reqDescription]['attachments'][] = $attachment;
				$this->requirementAttachments[$reqDescription]['sectionTitle'] = $reqDescription;
			}
		}

		// if we're selecting by people, we still want to return a section for every user, even if they have no attachments
		if ($selectBy == "People") {
			foreach ($this->selectFormValues['userContextIds'] as $userContextId) {
				if (!in_array($userContextId, $userContextsWithAttachments)) {
					$role = \Fisdap\EntityUtils::getEntity("UserContext", $userContextId);
					if ($role) {
						$userCertification = $role->getCertification();
						$userContextDescription = $role->user->first_name . " " . $role->user->last_name . ", " . $userCertification;
						$userSortKey = $role->user->last_name . $role->user->first_name . $userCertification . ":" . $userContextId;
						$this->userAttachments[$userSortKey]['attachments'] = array();
						$this->userAttachments[$userSortKey]['sectionTitle'] = $userContextDescription;
					}
				}
			}

			// now sort the users into the proper order
			uksort($this->userAttachments, "strnatcasecmp");
		}
		
		$save = new Fisdap_Form_Element_SaveButton("saveButton");
		$save->setDecorators(self::$buttonDecorators);
		$this->addElement($save);
		
		// Set the decorators for the form
		$this->setDecorators(array(
			'FormErrors',
			'PrepareElements',
			array('ViewScript', array('viewScript' => "forms/edit-compliance-status.phtml")),
			'Form'
		));
		
	}
	
	private function addAdditionalElements($post)
	{
		foreach($post as $name => $value) {
			if (strpos($name, "renewed") !== false && $value == 1) {
				$pieces = preg_split("/_/", $name);
				$this->addElement('checkbox', 'completed_' . $pieces[1] . "_new");
				$this->addElement('text', 'dueDate_' . $pieces[1] . "_new");
				$this->addElement('text', 'expirationDate_' . $pieces[1] . "_new");
			}
		}
	}

	/**
	 * Sort attachment arrays by name, for use in the "by requirement" mode
	 * @param $a
	 * @param $b
	 * @return int
	 */
	public static function sortAttachmentsByPeople($a, $b) {
		$stringA = $a["last_name"].$a["first_name"].$a["user_context_certification"];
		$stringB = $b["last_name"].$b["first_name"].$b["user_context_certification"];
		return strcasecmp($stringA ,$stringB);
	}

	/**
	 * Sort attachment arrays by requirement name, for use in the "by people" mode
	 * @param $a
	 * @param $b
	 * @return int
	 */
	public static function sortAttachmentsByRequirement($a, $b) {
		$stringA = $a["req_name"];
		$stringB = $b["req_name"];
		return strcasecmp($stringA ,$stringB);
	}

	public function process($post)
	{
		$attachments = array();
		$updateCompliance = array();
		
		$this->addAdditionalElements($post);
		$valid = $this->isValid($post);
		$values = $this->getValues();
		
		foreach($values as $name => $value) {
			$pieces = preg_split("/_/", $name);
			
			//Split the name of the element, if there are two pieces, store them in an array to parse later.
			if (count($pieces) == 2) {
				$attachments[$pieces[1]][$pieces[0]] = $value;
			} else if (count($pieces) == 3) {
				$attachments[$pieces[1]][$pieces[0]] = $value;
			}
		}

		foreach($attachments as $id => $data) {
			$attachment = \Fisdap\EntityUtils::getEntity("RequirementAttachment", $id);
			
			//Archive the attachment and get a new one
			if ($data["renewed"] == 1) {
				$attachment = $attachment->archive(true);
				
				// Only set a due date for new attachments
				$attachment->due_date = $data["dueDate"];
			}
			
			//Store compliance before changes so we can compare later after we've made edits
			$isCompliantBefore = $attachment->isCompliant();
			
			$attachment->completed = $data["completed"];
			
			if ($attachment->completed) {
				$attachment->expiration_date = $data["expirationDate"];
			} else {
				$attachment->expiration_date = null;
			}
			
			//Now check to see if compliance for this attachment changed
			if (($isCompliantBefore != $attachment->isCompliant() || !$attachment->id) && !in_array($attachment->user_context->id, $updateCompliance)) {
				$updateCompliance[] = $attachment->user_context->id;
			}
			
			$attachment->save(false);
		}
		
		//Now flush all of the changes
		$attachment->save(true);
		
		\Fisdap\EntityUtils::getRepository("Requirement")->updateCompliance($updateCompliance);
		
		return true;
	}
	
}
