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

use Fisdap\Entity\User;

/**
 * This helper displays the appropriate navigation for the portfolio.
 */

/**
 * @package Portfolio
 */
class Portfolio_View_Helper_AboutStudent extends Zend_View_Helper_Abstract
{
    /**
     * @var string the html to be rendered
     */
    protected $_html;

    public $view;

    public function __construct($view = null)
    {
        if ($view) {
            $this->view = $view;
        }
    }

    /**
     * Return the rendered HTML for a student's about profile
     *
     * Options syntax:
     *   $options['student'] StudentLegacy student that the about student helper should show
     *   $options['helpers'] array List of any subhelpers that you would like to include, with their options
     *
     * @param array $options Any optional options used by this view helper
     * @return string  The rendered html of the student's about profile
     */
    public function aboutStudent($options = array())
    {
        // Get the logged in user
        $user = User::getLoggedInUser();

        // If we're an instructor looking up a student
        if ($user->isInstructor()) {

            // ... and we're explicitly asking for a student
            if (isset($options['student'])) {

                // Set the student to the one we asked for
                $student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $options['student']);
            }

            // If instead, we're a student
        } else {
            // Set the student to ourself
            $student = $user->getCurrentRoleData();
        }

        // only move forward if we actually got a student, otherwise we don't need to display any html
        if (!isset($student)) {
            return '';
        }

        // If we didn't get an array of helpers, use a blank one
        $helpers = isset($options['helpers']) ? $options['helpers'] : array();

        /**
         * @todo Break each of these cases out into a viewhelper
         */

        // Profile photo column
        if (isset($helpers['profile-pic']) || in_array('profile-pic', $helpers)) {

            // Default options for this view helper
            $profilePicDefaultOptions = array(
                'size' => '265',
                'class' => 'grid_4',
            );

            // Similar to underscore.js's extend function for objects, this allows us to override sets of defaults
            if (isset($helpers['profile-pic']['options']) && is_array($helpers['profile-pic']['options'])) {
                $profilePicOptions = array_replace_recursive($profilePicDefaultOptions, $helpers['profile-pic']['options']);
            } else {
                $profilePicOptions = $profilePicDefaultOptions;
            }

            $this->_html .= "<div class='" . $profilePicOptions['class'] . "'>";
            $this->_html .= "<div id='profile-pic'>" . Util_Image::get_gravatar($student->email, $profilePicOptions['size'], 'mm', 'g', true) . "</div>";
            if (!$user->isInstructor()) {
                $this->_html .= "<a href='http://en.gravatar.com/site/login'>Edit image</a>";
            }
            $this->_html .= "</div>";
        }

        // "Everything else" column
        $this->_html .= isset($helpers['profile-pic']) || in_array('profile-pic', $helpers) ? "<div class='grid_8'>" : "<div class='grid_12'>";

        // Info
        if (isset($helpers['info']) || in_array('info', $helpers)) {

            $studentCertification = ucfirst($student->getCertification('formatted'));
            $studentGraduationMonth = Util_FisdapDate::get_short_month_name($student->graduation_month);
            $this->_html .= "
				<div id='info'>
					<h2 class='section-header no-border' title='Student id: {$student->id}'>
						{$student->user->getFullName()}
					</h2>
					<div class='cert-program'>{$studentCertification} student at {$student->program->name}</div>
					<div class='graduation'>Graduation: {$studentGraduationMonth} {$student->graduation_year}</div>
					<div class='student-email'>{$student->user->email}</div>
				</div>
			";
        }

        // Product shields
        if (isset($helpers['badges']) || in_array('badges', $helpers)) {
            $sn = $student->getUserContext()->getPrimarySerialNumber();
            $this->_html .= $this->view->productShields($sn->configuration, $student);
        }

        // Student self-description
        if (isset($helpers['description']) || in_array('description', $helpers)) {

            $this->_html .= "<div id='description' class='section-body'>
                                       <div id='student_description_display'>";
            if ($student->portfolioDetails->first()->portfolio_description == '') {
                $name = ($user->isInstructor()) ? $student->user->first_name . " has" : "You have";
                $this->_html .= $name . " not provided a description yet.";
                $addEditVerb = 'Add';
            } else {
                $this->_html .= $student->portfolioDetails->first()->portfolio_description;
                $addEditVerb = 'Edit';
            }
            $this->_html .= "</div>";
            if (!$user->isInstructor()) {
                $this->_html .= "<span id='student_description_display_edit'>
							<textarea rows='10' cols='40' id='student_description'>" . $student->portfolioDetails->first()->portfolio_description . "</textarea>
							<input type='button' value='Save' id='save_description_button' />
						</span>
						<a href='#' id='edit_description_link'>$addEditVerb your description</a>";
            }
            $this->_html .= "</div>";
        }

        // Emergency contact info
        if (isset($helpers['contact-info']) || in_array('contact-info', $helpers)) {
            $this->_html .= "<div id='contact-info'>
					<h3 class='section-header'>Emergency Contact</h3>";

            if ($this->view->contact_info . $student->contact_phone == '') {
                $addEditVerb = 'Add';
                $this->_html .= "<p>" . $student->first_name . " has not provided emergency contact info yet.</p>";
            } else {
                $addEditVerb = 'Edit';
                $this->_html .= $this->view->contact_info . " | " . $student->contact_phone;
            }

            if (!$user->isInstructor() || $user->getCurrentRoleData()->hasPermission("Edit Student Accounts")) {
                $this->_html .= "<br><a href='/account/edit/student/studentId/" . $student->id . "'> $addEditVerb contact info</a>";
            }
            $this->_html .= "</div>";
        }

        $this->_html .= "</div>
			<div class='clear'></div>";

        return $this->_html;
    }
}
