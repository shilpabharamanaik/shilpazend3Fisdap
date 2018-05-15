<?php

/* * **************************************************************************
 *
 *         Copyright (C) 1996-2011.  This is an unpublished work of
 *                          Headwaters Software, Inc.
 *                             ALL RIGHTS RESERVED
 *         This program is a trade secret of Headwaters Software, Inc.
 *         and it is not to be copied, distributed, reproduced, published,
 *         or adapted without prior authorization
 *         of Headwaters Software, Inc.
 *
 * ************************************************************************** */
use Fisdap\Entity\User;

/**
 * Guided Tour view helper!
 * Use this helper whenever you want to include a guided tour on a page.
 * This helper is smart enough to know when it should be displayed (will handle user role/active/etc.)
 * Just send in a tour guide ID.
 *
 * @todo Eventually, it'd be nice to make this helper smart enough to figure out which tour to include on its own (based on $tour->url and current user role)
 *
 * @author Hammer :)
 */
class Fisdap_View_Helper_GuidedTourHelper extends Zend_View_Helper_Abstract
{
    public $tour;

    /**
     * @var User
     */
    public $current_user;

    public $has_history = false;


    public function guidedTourHelper($tour_id)
    {
        $html = "";

        $this->current_user = \Fisdap\Entity\User::getLoggedInUser();
        $this->tour = \Fisdap\EntityUtils::getEntity("GuidedTour", $tour_id);
        $can_render_tour = $this->canRenderTour();

        if ($can_render_tour) {

            // get the current view
            $view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
            $view->headLink()->appendStylesheet("/css/jquery.guidedTour.css");
            $view->headScript()->appendFile("/js/jquery.guidedTour.js");

            $this->setHasHistory();

            // get the HTML to be rendered
            $html .= "<div id='guided_tour_wrapper' data-tourid='" . $this->tour->id . "'>";
            $html .= $this->renderWelcomeModal();
            $html .= $this->renderBottomNavigation();
            $html .= $this->renderSteps();
            $html .= $this->renderTourGuideRobot();
            $html .= "</div>";
        }

        return $html;
    } // end guidedTourHelper()


    /*
     * Renders the HTML for the welcome modal
     * Also adds in parameters for the js to know if should auto open or not
     *
     * @return String $html to be rendered
     */
    public function renderWelcomeModal()
    {
        $session = Zend_Registry::get('session');
        $auto_open_modal = true;
        $starting_step_id = 0;

        // do we have session progress on this tour already? Need to tell jQuery...
        if (isset($session->guided_tour_progress[$this->tour->id])) {
            $auto_open_modal = false;
            $starting_step_id = $session->guided_tour_progress[$this->tour->id];
        } elseif ($this->has_history) {
            $auto_open_modal = false;
        }

        $html = '<div class="guided_tour_welcome_modal" id="guided_tour_' . $this->tour->id . '_welcome_modal" data-autoOpen="' . $auto_open_modal . '" data-startingstepid="' . $starting_step_id . '">';
        $html .= '<h3 class="guided_tour_welcome_header">Take a tour of ' . $this->tour->name . '</h3>';
        $html .= '<div class="guided_tour_welcome_msg">';
        $html .= ($this->tour->welcome_msg) ? $this->tour->welcome_msg : "Looks like you haven't taken our tour yet.";
        $html .= '</div>';
        $html .= '<div class="guided_tour_end_msg">';
        $html .= ($this->tour->end_msg) ? $this->tour->end_msg : "Thanks for taking the tour!";
        $html .= '</div>';
        $html .= $this->getModalButtons();
        $html .= '</div>';

        return $html;
    } // end renderWelcomeModal()


    /*
     * Renders the HTML for the bottom navigation
     * This is pretty simple since the plugin will do most of the work
     *
     * @return String $html to be rendered
    */
    public function renderBottomNavigation()
    {
        $html = '<div id="tour_' . $this->tour->id . '_directions" class="tour_directions">';

        $html .= '<div class="guided_tour_navigation">';
        $html .= '<span class="guided_tour_arrow guided_tour_prev"></span>';
        $html .= '<div class="guided_tour_current_step">1</div>';
        $html .= '<span class="guided_tour_arrow guided_tour_next"></span>';
        $html .= '<div class="clear"></div>';
        $html .= '<div class="guided_tour_dots_wrapper"></div>';
        $html .= '</div>';

        $html .= '<div class="guided_tour_step_text"></div>';
        $html .= "<div class='small green-buttons'><button id='completed_tour_" . $this->tour->id . "'>I'm done</button></div>";
        $html .= "<img class='tour_complete_throbber' src='/images/throbber_small.gif' id='tour_" . $this->tour->id . "_complete_throbber'>";
        $html .= "<img class='tour_checkmark' src='/images/white_checkmark.png' id='tour_" . $this->tour->id . "_checkmark'>";
        $html .= "<img class='close_guided_tour' src='/images/icons/delete.png' id='tour_" . $this->tour->id . "_close'>";

        $html .= '</div>';

        return $html;
    } // end renderBottomNavigation()


    /*
     * Renders the HTML for the hidden UL that contains the steps
     * This UL is what will call the plugin in JQuery (eventauly)
     *
     * @return String $html to be rendered
    */
    public function renderSteps()
    {
        $user_has_history_attribute = ($this->has_history) ? "1" : "0";
        $html = '<ul id="guided_tour_' . $this->tour->id . '" data-roleType=' . $this->tour->role->id . ' user_has_history="' . $user_has_history_attribute . '">';

        foreach ($this->tour->steps as $step) {
            $html .= '<li ' . $this->getStepDataAttributes($step) . '>';
            $html .= '<span class="focus_element_selector" id="guided_tour_step_' . $step->id . '_focus_element_selector">';
            $html .= $step->focus_element;
            $html .= '</span>';
            $html .= '<span class="step_text" id="guided_tour_step_' . $step->id . '_text">';
            $html .= $step->step_text;
            $html .= '</span>';
            $html .= '</li>';
        }

        $html .= "</ul>";

        return $html;
    } // end renderSteps()


    /*
     * Renders the HTML for the small robot that is in the bottom right hand corner when a tour is not in progress
     * This is visible if:
     * 		A user chooses 'later' or 'I'm good'
     * 		After they've completed the tour
     * 		They've come to a page and already have a history record
     * Clicking this element will re-open/start the tour (jQuery plugin handles this, of course)
     *
     * @return String $html to be rendered
    */
    public function renderTourGuideRobot()
    {
        $robot_class = ($this->has_history) ? "inactive_tour_robot" : "active_tour_robot";
        $html = "<div id='guided_tour_" . $this->tour->id . "_robot_tooltip' class='guided_tour_robot_tooltip'><div class='guided_tour_robot_tooltip_arrow_left'></div>Click me to open a guided tour for this page!</div>";
        $html .= "<div id='guided_tour_" . $this->tour->id . "_corner_robot' class='guided_tour_robot " . $robot_class . "'>";
        $html .= '<img id="tour_' . $this->tour->id . '_robot" src="/images/tour-guide-robot.svg">';
        $html .= "</div>";

        return $html;
    } // end renderTourGuideRobot()


    /*
     * Sets $this->has_history to true if the current user has a record for $this->tour
     * 	(they have already gone through it or chosen to have it not pop up again)
     * Sets $this->has_history to false if the current user DOES NOT have a record for $this->tour
     * 	(either they chose 'later' or this is their first time using this interface)
    */
    public function setHasHistory()
    {
        $this->has_history = $this->tour->userHasCompleted($this->current_user->getCurrentUserContext()->id);
    } // end setHasHistory()


    /**
     * Returns true/false if we should render the tour based on tour state/current user role type.
     * In order to use this tour (and return true) it must:
     *        A) Be a valid tour.
     *        B) Have at least 1 step.
     *        C) Be for the current user's user role type
     *        D) If NOT staff: Be active.
     *
     * @return Boolean true if the tour should be rendered, false if this helper should return an empty string
     */
    public function canRenderTour()
    {
        $render_tour = false;

        // check for A)
        if ($this->tour) {
            // check for B)
            if (count($this->tour->steps) > 0) {
                // check for C)
                if ($this->tour->role->id == $this->current_user->getCurrentRoleData()->id) {
                    // check for D) -- Staff accounts DO NOT need the tour to be active to see it
                    if ($this->tour->active) {
                        $render_tour = true;
                    } elseif ($this->current_user->isStaff()) {
                        $render_tour = true;
                    }
                }
            }
        }

        return $render_tour;
    } // end can_render_tour()


    /*
     * Renders a string for the data attributes that will be used on the step li
     *
     * @param GuidedTourStep $step the step object
     * @return String attributes to be included in an HTML element
    */
    public function getStepDataAttributes($step)
    {
        // save them into PHP variables to make life easier for reading this chunk of code
        $pointer = "data-pointer='" . $step->pointer . "'";
        $auto_xy_pos = "data-autoxypos='" . $step->auto_xy_pos . "'";
        $manual_x_pos = "data-manualxpos='" . $step->manual_x_pos . "'";
        $manual_y_pos = "data-manualypos='" . $step->manual_y_pos . "'";
        $hidden_on_page_load = "data-hiddenonpageload='" . $step->hidden_on_page_load . "'";

        return $pointer . ' ' . $auto_xy_pos . ' ' . $manual_x_pos . ' ' . $manual_y_pos . ' ' . $hidden_on_page_load . ' data-stepdbid="' . $step->id . '"';
    } // end getStepDataAttributes()


    /*
     * Renders a string for the welcome modal buttons
     * @return String $html to be rendered
    */
    public function getModalButtons()
    {
        $html = '<div class="extra-small guided_tour_welcome_modal_buttons">';

        $html .= '<span>';
        $html .= '<button id="later_tour_guide_' . $this->tour->id . '">Remind me later</button>';
        $html .= '<button id="end_tour_guide_' . $this->tour->id . '">No thanks</button>';
        $html .= '</span>';

        $html .= '<span class="green-buttons">';
        $html .= '<button id="start_tour_guide_' . $this->tour->id . '">Start tour</button>';
        $html .= '</span>';

        $html .= '<div class="clear"></div>';
        $html .= '</div>';

        return $html;
    } // end getStepDataAttributes()
} // end Fisdap_View_Helper_GuidedTourHelper
