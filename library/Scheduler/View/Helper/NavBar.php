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
 * This helper will display the nav bar across the various scheudler/compliance pages
 * @author Hammer :)
 */

/**
 * @package Scheduler
 */
class Scheduler_View_Helper_NavBar extends Zend_View_Helper_Abstract
{
    /*
     * Array of button visiblity
     * (all values except for request_count_html are Boolean)
     */
    protected $buttons;

    /*
     * String the name of the current page
     */
    protected $page;

    /**
     * This will build the HTML for the nav bar that appears on all Scheduler/Compliance pages.
     * This will handle which links should be visible depending on permissions, account type, current app location
     * @param String $page the name of the current page: 'calendar', 'portfolio', 'manage', 'edit', 'compliance_settings',
     * 'shift_requests', 'scheduler_settings', 'recurring_emails'
     *
     * @return string the menu rendered as html
     */
    public function navBar($page = null)
    {
        $this->page = $page;

        // add our css/js
        $this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/navbar-menu.js");
        $this->view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/navbar-menu.css");

        // handle permssions
        $user = \Fisdap\Entity\User::getLoggedInUser();
        $is_student = ($user->getCurrentRoleName() == 'instructor') ? false : true;
        $instructor = $user->getCurrentRoleData();
        $settings = ($is_student) ? false : $instructor->hasPermission("Edit Program Settings");
        $edit_compliance = ($is_student) ? false : $instructor->hasPermission("Edit Compliance Status");
        $scheduler = ($is_student) ? false : $instructor->hasPermission("View Schedules");
        $lab = ($is_student) ? false : $instructor->hasPermission("Edit Lab Schedules");
        $field = ($is_student) ? false : $instructor->hasPermission("Edit Field Schedules");
        $clinical = ($is_student) ? false : $instructor->hasPermission("Edit Clinic Schedules");

        // now for button visibility
        $this->buttons['scheduler_settings'] = ($settings && $page != "scheduler_settings");
        $this->buttons['manage_requests'] = (($lab || $clinical || $field) && ($page != "shift_requests"));
        $this->buttons['compliance_settings'] = ($settings && $edit_compliance && $page != "compliance_settings");
        $this->buttons['compliance_status'] = ($edit_compliance && $page != "edit");
        $this->buttons['portfolio'] = ($page != "portfolio");
        $this->buttons['manage'] = ($settings && $edit_compliance && $page != "manage");
        $this->buttons['scheduler'] = ($scheduler || $page == "calendar");
        $this->buttons['compliance'] = ($this->buttons['compliance_settings'] ||
            $this->buttons['compliance_status'] ||
            $this->buttons['portfolio'] ||
            $this->buttons['manage']);
        $this->buttons['on_calendar'] = ($page == "calendar");
        $this->buttons['emails'] = ($page != "recurring_emails");
        $this->buttons['subscriptions'] = ($page != "subscriptions");
        $this->buttons['request_count_html'] = $this->getRequestCountHTML($user, $is_student);

        // render the student or instructor view
        return ($is_student) ? $this->getStudentHTML() : $this->getInstructorHTML();
    } // end navBar()


    /*
     * Will render the HTML for an instructor
     * (uses the global buttons array to determine visibility)
     *
     * @return String the rendered HTML
     */
    public function getInstructorHTML()
    {
        $html = '<ul id="compliance-nav-bar" class="instructor-compliance-nav-bar">';

        // scheduler drop down
        if ($this->buttons['scheduler']) {
            $html .= "<li class='compliance-nav-bar-menu-item' data-dropDownId='scheduler-navbar-options' id='scheduler-navbar-options-li'>";
            $html .= "Scheduler";
            $html .= '<ul id="scheduler-navbar-options" class="navbar-options">';
            $html .= ($this->buttons['on_calendar']) ? "" : "<li><a class='navbar-sub-option' href='/scheduler'>Calendar</a></li>";
            $html .= ($this->buttons['scheduler_settings']) ? "<li><a class='navbar-sub-option' href='/scheduler/settings'>Settings</a></li>" : "";
            $html .= ($this->buttons['manage_requests']) ? "<li><a class='navbar-sub-option' href='/scheduler/requests'><div class='requests-txt'>Shift requests</div>" . $this->buttons['request_count_html'] . "<div class='clear'></div></a></li>" : "";
            $html .= ($this->buttons['emails']) ? '<li><a class="navbar-sub-option" href="/scheduler/emails">Recurring emails</a></li>' : '';
            $html .= ($this->buttons['subscriptions']) ? '<li><a class="navbar-sub-option" href="/scheduler/settings/subscriptions">Subscriptions</a></li>' : '';
            $html .= "</ul>";
            $html .= "</li>";
        }

        // compliance drop down
        if ($this->buttons['compliance']) {
            $html .= "<li class='compliance-nav-bar-menu-item' data-dropDownId='compliance-navbar-options' id='compliance-navbar-options-li'>";
            $html .= "Compliance";
            $html .= '<ul id="compliance-navbar-options" class="navbar-options">';
            $html .= ($this->buttons['compliance_settings']) ? "<li><a class='navbar-sub-option' href='/scheduler/compliance/settings'>Settings</a></li>" : "";
            $html .= ($this->buttons['manage']) ? "<li><a class='navbar-sub-option' href='/scheduler/compliance/manage'>Requirements</a></li>" : "";
            $html .= ($this->buttons['compliance_status']) ? "<li><a class='navbar-sub-option' href='/scheduler/compliance/edit-status'>Edit status</a></li>" : "";
            $html .= ($this->buttons['portfolio']) ? "<li><a class='navbar-sub-option' href='/portfolio'>Student portfolios</a></li>" : "";
            $html .= '</ul>';
            $html .= "</li>";
        }

        // PDF/Email link
        if ($this->buttons['on_calendar']) {
            $html .= '<div class="dashed-right-border"></div>';
            $html .= '<li class="export-pdf-nav-bar-menu-item" id="pdf-link">PDF/Email</li>';
            $html .= '<li class="subscribe-nav-bar-menu-item" id="sub-link">Subscribe</li>';
        }

        $html .= "</ul>";

        return $html;
    } // end getInstructorHTML()


    /*
     * Will render the HTML for a student
     * @return String the rendered HTML
     */
    public function getStudentHTML()
    {
        $html = '<ul id="compliance-nav-bar" class="student-compliance-nav-bar" data-currentPage="' . $this->page . '">';

        if ($this->page != "shift_requests") {
            $html .= "<li><a class='single-navbar-option' href='/scheduler/requests'><div class='requests-txt'>Shift requests</div>";
            $html .= $this->buttons['request_count_html'];
            $html .= "<div class='clear'></div></a></li>";
        } else {
            $html .= "<li class='single-navbar-option active'><div class='requests-txt'>Shift requests</div>";
            $html .= $this->buttons['request_count_html'];
            $html .= "<div class='clear'></div></li>";
        }

        if ($this->page != "portfolio") {
            $html .= "<li><a class='single-navbar-option' href='/portfolio/index/compliance'>Compliance</a></li>";
        } else {
            $html .= "<li class='single-navbar-option active' href='/portfolio/index/compliance'>Compliance</li>";
        }

        if ($this->buttons['on_calendar']) {
            $html .= "<li class='single-navbar-option active'>Calendar</li>";
        } else {
            $html .= "<li><a class='single-navbar-option' href='/scheduler'>Calendar</a></li>";
        }

        // PDF/Email link
        if ($this->buttons['on_calendar']) {
            $html .= '<div class="dashed-right-border"></div>';
            $html .= '<li class="export-pdf-nav-bar-menu-item" id="pdf-link">PDF/Email</li>';
            $html .= '<li class="subscribe-nav-bar-menu-item" id="sub-link">Subscribe</li>';
        }

        $html .= "</ul>";

        return $html;
    } // end getStudentHTML()


    /*
     * Will get the HTML for displaying the total count of pending shift requests.
     * @param /Fisdap/Entity/User $user the current logged in user
     * @param Boolean $is_student true if the current logged in user is a student
     *
     * @return String the rendered HTML
     */
    public function getRequestCountHTML($user, $is_student)
    {
        // get the total number of pending shift requests
        $shift_request_repo = \Fisdap\EntityUtils::getRepository("ShiftRequest");
        $repo_param = ($is_student) ? $user->getCurrentUserContext() : $user->getProgramId();
        $requests = ($is_student) ? $shift_request_repo->getPendingRequestCountByOwner($repo_param) : $shift_request_repo->getPendingRequestCountByProgram($repo_param);

        return ($requests > 0) ? "<span class='request-count'>" . $requests . "</span>" : "";
    } // end getRequestCountHTML()
} // end Scheduler_View_Helper_NavBar
