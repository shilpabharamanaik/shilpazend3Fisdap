<?php

/**
 * the main controller for Workshop Registration.
 *
 * @package    Fisdap
 * @subpackage Controllers
 */

class EventsController extends Fisdap_Controller_Base
{
    public function init()
    {
        parent::init();
    }
    
    public function indexAction()
    {
        $this->view->pageTitle = "Workshop Registration";
        $this->view->form = new Fisdap_Form_Events();
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            if ($attendeeId = $this->view->form->process($request->getPost())) {
                $this->flashMessenger->addMessage("Thanks for registering! Please check your email for your confirmation.");
                $this->_redirect("/events/success/?attendeeId=$attendeeId");
            }
        }
    }
    
    public function updateAction()
    {
        if (\Fisdap\Entity\User::getLoggedInUser() == null) {
            $this->_redirect('/');
        }
        if (!\Fisdap\Entity\User::getLoggedInUser()->isStaff()) {
            $this->_redirect('/');
        }
        
        $this->view->pageTitle = "Edit Form for Workshop Events.";
        $this->view->form = new Fisdap_Form_WorkshopEdit($this->_getParam("workshopId"));
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            if ($this->view->form->process($request->getPost()) == true) {
                $this->flashMessenger->addMessage("Your workshop events have been successfully updated.");
                $this->_redirect("/events/show-workshops/");
            }
        }
    }
    
    public function deleteAction()
    {
        if (\Fisdap\Entity\User::getLoggedInUser() == null) {
            $this->_redirect('/');
        }
        if (!\Fisdap\Entity\User::getLoggedInUser()->isStaff()) {
            $this->_redirect('/');
        }
        
        $workshop = \Fisdap\EntityUtils::getEntity("Workshop", $this->_getParam("workshopId"));
        $workshop->delete();
        $this->flashMessenger->addMessage("This workshop event was successfully deleted.");
        $this->_redirect("/events/show-workshops/");
    }
    
    public function deleteAttendeeAction()
    {
        if (\Fisdap\Entity\User::getLoggedInUser() == null) {
            $this->_redirect('/');
        }
        if (!\Fisdap\Entity\User::getLoggedInUser()->isStaff()) {
            $this->_redirect('/');
        }
        $attendee = \Fisdap\EntityUtils::getEntity("Attendee", $this->_getParam("attendeeId"));
        $attendee->delete();
        $this->flashMessenger->addMessage("This attendee was successfully deleted.");
        $this->_redirect("/events/workshop-report/");
    }
    
    public function showWorkshopsAction()
    {
        if (\Fisdap\Entity\User::getLoggedInUser() == null) {
            $this->_redirect('/');
        }
        if (!\Fisdap\Entity\User::getLoggedInUser()->isStaff()) {
            $this->_redirect('/');
        }
        
        $this->view->headScript()->appendFile("/js/library/Fisdap/Form/show-workshops.js");
        $this->view->headLink()->appendStylesheet("/css/library/Reports/show-workshops.css");
        $this->view->pageTitle = "Display table for Workshop Events.";
    }
    
    public function workshopReportAction()
    {
        if (\Fisdap\Entity\User::getLoggedInUser() == null) {
            $this->_redirect('/');
        }
        if (!\Fisdap\Entity\User::getLoggedInUser()->isStaff()) {
            $this->_redirect('/');
        }
        
        $this->view->headScript()->appendFile("/js/tableSorter/jquery.tablesorter.min.js");
        $this->view->headScript()->appendFile("/js/library/Fisdap/Form/workshop-report.js");
        $this->view->headLink()->appendStylesheet("/css/library/Fisdap/Form/workshop-report.css");
        $this->view->form = $form = new Fisdap_Form_PickWorkshop();
        $this->view->pageTitle = "Report for Workshop Events.";
    }
    
    public function getAttendeesFromSearchAction()
    {
        
        //check for POST data
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->view->isPost = true;
            $post = $request->getPost();
            
            if ($post != null) {
                $attendees = \Fisdap\EntityUtils::getRepository("Attendee")->findByWorkshop($post['workshops']);
            } else {
                //Grab all existing attendees
                $attendees = \Fisdap\EntityUtils::getRepository("Attendee")->findAll();
            }
        } else {
            $this->view->isPost = false;
        }

        $returnText = "";
        

        if ($attendees) {
            $returnText .= $this->getAttendeeTable($attendees);
        } else {
            $returnText .= "<div class='clear'></div><div class='grid_12 island withTopMargin'>
						    <h3 class='section-header'>Attendees Table</h3><div class='error'>No attendees
						    were found</div></div>";
        }
        
        $this->_helper->json(array("table" => $returnText, "outlook" => $this->view->attendeeEmailsOutlook,
                                   "other" => $this->view->attendeeEmailsOther));
    }
    
    public function getAttendeeTable($attendees)
    {
        $emails = array();
        
        $returnText = "<div class='clear'></div>
				    <div class='island withTopMargin extraLong'>
				    <h3 class='section-header'>" . " Attendees</h3>
				    <div id='table-holder'><table id='attendee-table' class='tablesorter attendee-search-table'>";
                    
        $returnText .= "<thead><tr id='head'>
					    <th class='id'>ID</th>
					    <th class='name'>Name</th>
					    <th class='cert'>Cert. Level</th>
					    <th class='email'>Email</th>
					    <th class='phone'>Phone</th>
						<th class='username'>Username</th>
					    <th class='cert-taught'>Cert. Level Taught</th>
					    <th class='cert-num'>Cert Num</th>
					    <th class='city'>City</th>
					    <th class='state'>State</th>
					    <th class='organization'>Organization</th>
					    <th class='zip'>Zipcode</th>
					    <th class='address1'>Address Line 1:</th>
					    <th class='address2'>Address Line 2:</th>
					    <th class='workshop'>Workshop</th>
						<th class='emailed'>Emailed</th>
						<th class='action'>Action</th>
					    </tr></thead><tbody>";
                        
        foreach ($attendees as $attendee) {
            $emails[] = $attendee->email;
            
            $returnText .= "<tr>";
            
            $returnText .= "<td class='id'>" . $attendee->id . "</td>";
            $returnText .= "<td class='name'>" . $attendee->first_name . " " . $attendee->last_name . "</td>";
            $returnText .= "<td class='cert'>" . \Fisdap\EntityUtils::getEntity("CertificationLevel", $attendee->cert_lvl)->description . "</td>";
            $returnText .= "<td class='email'>" . "<a href=\"mailto:" . $attendee->email . "\">" . $attendee->email . "</a></td>";
            $returnText .= "<td class='phone'>" . $attendee->phone . "</td>";
            $returnText .= "<td class='username'>" . $attendee->user_name . "</td>";
            $returnText .= "<td class='cert-taught'>" . \Fisdap\EntityUtils::getEntity("CertificationLevel", $attendee->cert_lvl_taught)->description . "</td>";
            $returnText .= "<td class='cert-num'>" . $attendee->cert_num . "</td>";
            $returnText .= "<td class='city'>" . $attendee->city . "</td>";
            $returnText .= "<td class='state'>" . $attendee->state . "</td>";
            $returnText .= "<td class='organization'>" . $attendee->organization . "</td>";
            $returnText .= "<td class='zip'>" . $attendee->zipcode . "</td>";
            $returnText .= "<td class='address1'>" . $attendee->address1 . "</td>";
            $returnText .= "<td class='address2'>" . $attendee->address2 . "</td>";
            $returnText .= "<td class='workshop'>" . $attendee->workshop->location . "</td>";
            if ($attendee->emailed) {
                $buttonText = "Emailed";
            } else {
                $buttonText = "Not Emailed";
            }
            
            $returnText .= "<td class='emailed green-buttons extra-small'><a href='#' class='toggleEmailedButton' data-attendeeid='{$attendee->id}'>" . $buttonText . "</a></td>";
            $returnText .= "<td class='action'><a class='deleteAttendee' href='/events/delete-attendee/attendeeId/" . $attendee->id . "'>Delete</a></td>";
            
            $returnText .= "</tr>";
        }
        
        $returnText .= "</tbody></table></div></div>";
        //$this->view->attendeeEmailsOutlook = implode(";", $emails);
        $this->view->attendeeEmailsOther = implode(",", $emails);
        
        return $returnText;
    }
    
    public function changeEmailedStatusAction()
    {
        $attendeeId = $this->_getParam("attendeeid");
        $emailed = $this->_getParam("emailed");
        $attendee = \Fisdap\EntityUtils::getEntity('Attendee', $attendeeId);

        $attendee->emailed = $emailed;
        
        //Save the changes and flush
        $attendee->save();
        $this->_helper->json($attendee->emailed);
    }
    
    public function successAction()
    {
        $this->view->pageTitle = "Workshop Registration";
        $this->view->attendee = \Fisdap\EntityUtils::getEntity("Attendee", $this->_getParam("attendeeId"));
    }
}
