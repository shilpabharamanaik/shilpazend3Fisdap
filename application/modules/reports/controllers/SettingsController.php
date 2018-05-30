<?php

/**
 *
 * @package    Reports
 * @subpackage Controllers
 */
class Reports_SettingsController extends Fisdap_Controller_Private
{
    public function init()
    {
        parent::init();
        
        // redirect to login if the user is not logged in yet
        if (!$this->user) {
            return;
        }
    }
    
    public function indexAction()
    {
        //Check permissions
        if (!$this->user->isInstructor()) {
            $this->displayError("You don't have permission to view this page.");
            return;
        } elseif (!$this->user->hasPermission("Edit Program Settings")) {
            $this->displayPermissionError("Edit Program Settings");
            return;
        }
        
        $program = \Fisdap\Entity\User::getLoggedInUser()->getCurrentProgram();
        
        $this->view->pageTitle = "Reports Settings";
        $this->view->program = $program;
        $this->view->profession = $program->profession->name;
        
        // get the reports for this user
        $reports = \Fisdap\EntityUtils::getRepository('Report')->getAvailableReportsByProfession($program->profession->id);
        $visible_reports = array();
        foreach ($reports as $report) {
            $reportClass = 'Fisdap_Reports_' . $report->class;
            if (class_exists($reportClass) && $reportClass::hasPermission($this->userContext)) {
                $visible_reports[] = $report;
            }
        }
        $this->view->reports = $visible_reports;
        
        // stuff we need for plugins
        $this->view->headLink()->appendStylesheet("/css/jquery.sliderCheckbox.css");
        $this->view->headScript()->appendFile("/js/jquery.sliderCheckbox.js");
        $this->view->headScript()->appendFile("/js/jquery.fieldtag.js");
    }
    
    public function toggleReportAction()
    {
        $report_id = $this->_getParam('report_id');
        $active  = $this->_getParam('active');
        $program = \Fisdap\Entity\User::getLoggedInUser()->getCurrentProgram();
        $program->toggleReport($report_id, $active);
        
        $this->_helper->json($program->id);
    }

    public function manageGoalsetsAction()
    {
        //Check permissions
        if (!$this->user->isInstructor()) {
            $this->displayError("You don't have permission to view this page.");
            return;
        } elseif (!$this->user->hasPermission("Edit Program Settings")) {
            $this->displayPermissionError("Edit Program Settings");
            return;
        }
        $this->view->pageTitle = "Manage Goalsets";
    }
}
