<?php

class Account_SitesAjaxController extends Fisdap_Controller_Private
{

    public function init()
    {
		parent::init();
		// redirect to login if the user is not logged in yet
		if (!$this->user) {return;}
		$this->view->user = $this->user;
		$this->view->program = $this->user->getCurrentProgram();
    }
	
	public function formatFormErrors($process_result)
	{
		$form_elements_with_errors = array();
		$html_res = "<div class='error'>";
		
		foreach($process_result as $element_id => $msg){
			$form_elements_with_errors[] = $element_id;
			$html_res .= "<p>" . $msg[0] . "</p>";
		}
		
		$html_res .= "</div>";
		
		return array("elements" => $form_elements_with_errors, "html" => $html_res);
	}
	
	/*
	* --------------------------------------------------------------------------------------------------------------------------
	* Bases/Departments functions
	*   toggleBaseAction()
	* 	savePreceptorAction()
	* 	generateBaseModalAction()
	* --------------------------------------------------------------------------------------------------------------------------
	*/
	
	// toggles the active state of a program/base association
	public function toggleBaseAction()
	{
		$base_id = $this->_getParam("base");
		$base = \Fisdap\EntityUtils::getEntity("BaseLegacy", $base_id);
		$site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $this->_getParam("site"));
		$new_association = $this->_getParam("new_association");
		$program = $this->view->program;
		$return_val = true;
		
		if($new_association){
			// in this case, we're creating a new association between an existing base and the current program
			// happens in sharing networks
			$program->addBase($base, true);
			$return_val = true;
		}
		else {
			if($base){
				$program_base_assoc = $base->getBaseAssocationByProgram($program->id);
				$program_base_assoc->active = (!$program_base_assoc->active);
				$program_base_assoc->save();
			}
			else {
				// if we didn't get the entity, that means we have a new default clinical department to add
				$new_base = new \Fisdap\Entity\BaseLegacy;
				$new_base->name = $base_id;
				$new_base->site = $site;

                // default the address to the site's address
                $new_base->address = $site->address;
                $new_base->city = $site->city;
                $new_base->state = $site->state;
                $new_base->zip = $site->zipcode;

				$new_base->save();
				$program->addBase($new_base, true);
				$return_val = $new_base->id;
			}
		}
		
		$this->_helper->json($return_val);
	}
	
	// generate the base modal for editing/adding a base/department
	public function generateBaseModalAction()
	{
		$base_id = $this->_getParam('base_id');
        $site_id = $this->_getParam('site_id');
        $site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $site_id);
        $newDefault = false;

        // if we didn't get an actual id, that means we have a new default clinical department to add
        if ($base_id && !is_numeric($base_id)) {
            $base_name = array_shift(explode("_", "$base_id"));
            $new_base = new \Fisdap\Entity\BaseLegacy;
            $new_base->name = $base_name;
            $new_base->site = $site;

            // default the address to the site's address
            $new_base->address = $site->address;
            $new_base->city = $site->city;
            $new_base->state = $site->state;
            $new_base->zip = $site->zipcode;

            $new_base->save();

            // if we're creating the default clinical department on modal launch, it is not active, by definition
            // because it would have been created when it first activated
            $this->view->program->addBase($new_base, false);

            $base_id = $new_base->id;
            $newDefault = true;
        }

		$form = new Account_Form_Modal_BaseModal($site, $base_id);
		$this->_helper->json(array("modalHTML" => $form->__toString(), "baseId" => $base_id, "newDefault" => $newDefault));
	}
	
	// process the base modal form (will return errors or a new accordion is data is valid)
	public function saveBaseAction()
	{
		$site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $this->_getParam("site_id"));
		$form_data = $this->_getParam("form_data");
		$base_modal = new Account_Form_Modal_BaseModal($site, $this->_getParam("base_id"));
		
		$process_result = $base_modal->process($form_data);
		
		if($process_result['success'] === true){
			$success = "true";
			$base_form = new Account_Form_Bases($site);
			$html_res = $this->view->partial("forms/site-sub-forms/base-accordion.phtml", array("form_element" => $base_form));
		}
		else {
			$errors = $this->formatFormErrors($process_result);
			$success = "false";
			$html_res = $errors['html'];
		}
		
		$this->_helper->json(array("success" => $success, "result" => $html_res, "form_elements_with_errors" => $errors['elements'],
								   "new_base_id" => $process_result['new_base_id']));
		
	} // end saveBaseAction()
	
	/*
	* --------------------------------------------------------------------------------------------------------------------------
	* Merge Bases Modal functions
	* 	generateMergeBaseFormAction()
	* 	mergeBasesAction()
	* --------------------------------------------------------------------------------------------------------------------------
	*/
	
	// generate the modal
	public function generateMergeBaseFormAction()
	{
		$options = $this->_getParam('bases');
		$site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $this->_getParam("site_id"));

		foreach ($options as $id => $text) {
		    $base = \Fisdap\EntityUtils::getEntity("BaseLegacy", $id);
		    if ($base->getAddressString() != '') {
				$options[$id] .= ": ".$base->getAddressString();
		    } else {
				$options[$id] .= ": no address entered";
		    }
		}
		
		$form = new Account_Form_Modal_MergeBasesModal($options, $site->id);
		$this->_helper->json($form->__toString());
		
	} // end generateMergeBaseFormAction()
	
	
	// process the modal
	public function mergeBasesAction()
	{
		$formValues = $this->_getAllParams();
		$form = new Account_Form_Modal_MergeBasesModal($formValues);
		$process_result = $form->process($formValues);
		$success = false;
		
		if(is_numeric($process_result)){
			$success = "true";
			$site_id = $this->_getParam("site_id");
			$site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $site_id);
			$base_form = new Account_Form_Bases($site);
			$html_res = $this->view->partial("forms/site-sub-forms/base-accordion.phtml", array("form_element" => $base_form));
		}
		else {
			$success = "false";
		}
		
		$this->_helper->json(array("success" => $success, "html_res" => $html_res, "process_res" => $process_result));
		
	} // end mergeBasesAction()
	
	
	/*
	* --------------------------------------------------------------------------------------------------------------------------
	* Preceptors functions
	*   togglePreceptorAction()
	* 	savePreceptorAction()
	* 	generatePreceptorModalAction()
	* --------------------------------------------------------------------------------------------------------------------------
	*/
	
	// toggles the active state of a program/preceptor association
	public function togglePreceptorAction()
	{
		$preceptor = \Fisdap\EntityUtils::getEntity("PreceptorLegacy", $this->_getParam("preceptor"));
		$site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $this->_getParam("site"));
		$return = false;
		
		if($preceptor){
			$assoc = $preceptor->getAssociationByProgram($this->view->program->id);
			$assoc->active = (!$assoc->active);
			$assoc->save();
			$return = true;
		}
		
		$this->_helper->json($return);
		
	} // end togglePreceptorAction()
	
	// generate the preceptor modal for editing/adding a preceptor
	public function generatePreceptorModalAction()
	{
		$preceptor_id = $this->_getParam('preceptor_id');
		$site_id = $this->_getParam('site_id');
		$site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $site_id);
		$form = new Account_Form_Modal_PreceptorModal($site, $preceptor_id);
		$this->_helper->json($form->__toString());
		
	} // end generatePreceptorModalAction()
	
	// process the preceptor modal form (will return errors or a new accordion is data is valid)
	public function savePreceptorAction()
	{
		$site_id = $this->_getParam("site_id");
		$site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $site_id);
		$preceptor_id = $this->_getParam("preceptor_id");
		$form_data = $this->_getParam("form_data");
		$base_modal = new Account_Form_Modal_PreceptorModal($site, $preceptor_id);
		
		$process_result = $base_modal->process($form_data);
		
		if($process_result['success'] === true){
			$success = "true";
			$preceptor_form = new Account_Form_Preceptors($site);
			$html_res = $this->view->partial("forms/site-sub-forms/preceptor-accordion.phtml", array("form_element" => $preceptor_form));
		}
		else {
			$errors = $this->formatFormErrors($process_result);
			$success = "false";
			$html_res = $errors['html'];
		}
		
		$this->_helper->json(array("success" => $success, "result" => $html_res, "form_elements_with_errors" => $errors['elements'],
								    "new_preceptor_id" => $process_result['new_preceptor_id']));
		
	} // end savePreceptorAction()
	
	
	
	/*
	* --------------------------------------------------------------------------------------------------------------------------
	* Merge Preceptors Modal functions
	* 	generateMergePreceptorFormAction()
	* 	mergePreceptorsAction()
	* --------------------------------------------------------------------------------------------------------------------------
	*/
	
	// generate the modal
	public function generateMergePreceptorFormAction()
	{
		$options = $this->_getParam('preceptors');
		$form = new Account_Form_Modal_MergePreceptorsModal($options, $this->_getParam("site_id"));
		$this->_helper->json($form->__toString());
	} 
	
	// process the modal
	public function mergePreceptorsAction()
	{
		$data = $this->_getAllParams();
		$form = new Account_Form_Modal_MergePreceptorsModal($options, $this->_getParam("site_id"));
		$process_result = $form->process($data);
		$success = false;
		
		if(is_numeric($process_result)){
			$success = "true";
			$site_id = $this->_getParam("site_id");
			$site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $site_id);
			$preceptor_form = new Account_Form_Preceptors($site);
			$html_res = $this->view->partial("forms/site-sub-forms/preceptor-accordion.phtml", array("form_element" => $preceptor_form));
		}
		else {
			$success = "false";
		}
		
		$this->_helper->json(array("success" => $success, "html_res" => $html_res, "process_res" => $process_result));
		
	} // end mergePreceptorsAction()


    /*
	* --------------------------------------------------------------------------------------------------------------------------
	* Additional staff form functions
	* 	generateStaffMemberModalAction()
	* --------------------------------------------------------------------------------------------------------------------------
	*/

	/**
	 * generate the site staff modal for editing/adding a base/department
	 */
    public function generateStaffMemberModalAction()
    {
        $site_id = $this->_getParam('site_id');
        $site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $site_id);

        $staff_member_id = $this->_getParam('staff_member_id');

        $form = new Account_Form_Modal_SiteStaffMemberModal($site, $staff_member_id);
        $this->_helper->json($form->__toString());
    }

	/**
	 * process the site staff member modal form (will return errors or a new accordion if data is valid)
	 */
    public function saveStaffMemberAction()
    {
		$formValues = $this->_getAllParams();
        $site_id = $formValues["site_id"];
        $site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $site_id);

        $staff_member_id = $formValues["staff_member_id"];

        $staff_member_modal = new Account_Form_Modal_SiteStaffMemberModal($site, $staff_member_id);

        $process_result = $staff_member_modal->process($formValues);

        if($process_result['success'] === true){
            $success = "true";
			$info = array();
			$info["staff_member"] = \Fisdap\EntityUtils::getEntity("SiteStaffMember", $process_result['new_staff_member_id']);
			$info["country"] = $this->view->program->country;
			$info["site_admin"] = (!$this->view->program->sharesSite($this->site->id) || $this->view->program->isAdmin($this->site->id));
            $html_res = $this->view->partial("forms/site-sub-forms/site-staff-accordion-row.phtml", $info);
        }
        else {
            $errors = $this->formatFormErrors($process_result);
            $success = "false";
            $html_res = $errors['html'];
        }

        $this->_helper->json(array("success" => $success, "result" => $html_res, "form_elements_with_errors" => $errors['elements'],
            "new_staff_member_id" => $process_result['new_staff_member_id']));

    } // end saveStaffMemberAction()

	/**
	 * process the delete staff member modal
	 */
	public function deleteStaffMemberAction()
	{
		$staff_member_id = $this->_getParam("staff_member_id");
		$staff_member = \Fisdap\EntityUtils::getEntity("SiteStaffMember", $staff_member_id);
		$staff_member->delete();

		$this->_helper->json(true);

	} // end deleteStaffMemberAction()

	
	/*
	* --------------------------------------------------------------------------------------------------------------------------
	* Accreditation form functions
	* 	saveAccreditationAction()
	* --------------------------------------------------------------------------------------------------------------------------
	*/
	
	/**
	 * process the accreditation form (will return errors or a new accordion if data is valid)
	 */
	public function saveAccreditationAction()
	{
		$site_id = $this->_getParam("site_id");
		$site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $site_id);
		$form_data = $this->_getParam("form_data");
		
		$form = new Account_Form_Accreditation($site);
		
		$process_result = $form->process($form_data);
		
		if($process_result['success'] === true){
			$success = "true";
			$html_res = "<div class='success'>Your accreditation info has been saved.</div>";
		}
		else {
			$errors = $this->formatFormErrors($process_result);
			$success = "false";
			$html_res = $errors['html'];
		}
		
		$this->_helper->json(array("success" => $success, "result" => $html_res, "form_elements_with_errors" => $errors['elements']));
		
	} // end saveAccreditationAction()
	
} // end Class