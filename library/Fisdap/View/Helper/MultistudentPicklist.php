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

/**
 * A multistudent picker that uses a 'pick-list'
 * Previous authors: ahammond, astevenson, jmortenson
 * @package Scheduler
 * @author khanson
 */
use Fisdap\Entity\User;

class Fisdap_View_Helper_MultistudentPicklist extends Zend_View_Helper_Abstract
{
    /**
     * Primary view helper function to construct the HTML.
     *
     * Config is an array that contains information about which students are selected on load
     * $config = array(
     *  'student' => a single student id, int or string, for use in single mode only
     *  'multistudent_picklist_selected' => a comma-separated list of student ids, for use in multiple mode only
     *  'anonymous' => whether or not the anonymous box should be checked, 1 or NULL/0, for use in multiple mode only
     * );
     *
     * Also takes an $options array:
     * $options = array(
     *    'mode' => 'multiple' OR 'single': allow multiple students to be selected, or just one
     *        default 'multiple'
     *    'loadJSCSS' => boolean; whether to load JS/CSS assets via headscript/headlink or not,
     *        default FALSE
     *    'picklistJS' => '/js/library/Fisdap/View/Helper/shift-assign-multistudent-picklist.js'
     *        additional JS file used
     *    'picklistCSS' => '/css/library/Fisdap/View/Helper/multistudent-picklist.css'
     *        additional CSS file used
     *    'loadStudents' => boolean; whether to load a list of the program's students initially
     *        default FALSE
     *    'selectedStudents' => array; array of student IDs that should show up as selected already
     *    'helpText' => 'multistudent-picklist-help-scheduler.phtml'
     *        (needs to be in the current module's view/scripts folder) OR HTML string
     *    'showTotal' => boolean; whether to show total # of students selected,
     *        default FALSE
     *    'includeSubmit' => boolean; whether to include/show submit/cancel buttons in this output,
     *        default FALSE
     *    'longLabel' => boolean; whether to show certification/class info on each option,
     *        default FALSE
     *    'selectableStudentIds' => array; Array of selectable students
     *    'useSessionFilters' => boolean; whether to use filter defaults from a given session namespace, default FALSE
     *    'sessionNamespace' => string; the session namespace to use if using defaults ('useSessionFilters'), default 'MultiStudentPicklist'
     *    'studentVersion' => Alters the behavior of multi and single student picker (for students), when set to true,
     *                        both single and multi student pickers are hidden and a hidden element is
     *                        added to hold the student's ID. Also, in multi mode, when set to true, a
     *                        checkbox element is added to ask about anonymous classmates. When set to false, the
     *                        behavior of the form element is identical to instructors.
     * );
     *
     * @param User $user the logged in user
     * @param array|null $config a configuration for which students are selected
     * @param array $options options outlining how the picker should render
     * @param View|null $view the view
     *
     * @return string the html for the student picker
     */
    public function multistudentPicklist(User $user, $config = null, $options = array(), $view = null)
    {
        // set the view
        if (!$this->view && $view != null) {
            $this->view = $view;
        }

        // load JS and CSS
        $this->loadCssAndJs($options);

        // figure out what mode we're using (single or multi)
        $mode = $options['mode'];

        // get the array of filter default settings
        $filterDefaults = $this->getFilterDefaults($options);

        // get the html for the filters
        $filterForm = $this->getFilters($user, $filterDefaults);

        // get the html for the help bubble, if applicable
        $helpText = $this->getHelpText($options['helpText']);

        // load the student list, if applicable
        $studentArray = $this->getStudentArray($user, $config, $options, $filterDefaults, $mode, $filterForm);
        $students = $studentArray["students"];
        $anonChecked = $studentArray["anonChecked"];

        // Render the picklist using view script found in the default module
        $html = "";
        $renderInfo = array(
            "students" => $students,
            "anonChecked" => $anonChecked,
            "helpText" => $helpText,
            "user" => $user,
            "filters" => $filterForm,
            "showTotal" => (isset($options['showTotal']) && $options['showTotal']) ? true : false,
            "includeSubmit" => (isset($options['includeSubmit']) && $options['includeSubmit']) ? true : false,
            "longLabel" => (isset($options['longLabel']) && $options['longLabel']) ? true : false,
            "includeAnon" => (isset($options['includeAnon']) && $options['includeAnon']) ? true : false,
            "studentVersion" => ($this->isStudentVersion($options, $user)) ? true : false,
        );
        if (!isset($mode) || $mode == 'multiple') {
            $html .= $this->view->partial('multistudent-picklist.phtml', 'default', $renderInfo);
        } elseif ($mode == 'single') {
            $html .= $this->view->partial('multistudent-select.phtml', 'default', $renderInfo);
        }

        return $html;
    }

    /**
     * Get an array that tells which form options should be selected upon render, either the default or getting
     * saved session variables.
     *
     * @param array $options contains flag for whether or not to use session variables and the session namespace if so
     *
     * @return array $defaults a keyed array containing information about which filters should be selected
     */
    private function getFilterDefaults($options)
    {
        $defaults = array();
        $useBlankSlate = true;

        // set the namespace and defaults, if we're set up to use session filters
        if (isset($options['useSessionFilters']) && $options['useSessionFilters']) {
            $namespace = (isset($options['sessionNamespace'])) ? $options['sessionNamespace'] : "MultiStudentPicklist";
            $session = new \Zend_Session_Namespace($namespace);
            $defaults['namespace'] = $namespace;

            // use the session variables instead of the blank slate defaults, if appropriate
            if ($session->activated) {
                $useBlankSlate = false;
                $defaults['certificationLevels'] = $session->selectedCertifications;
                $defaults['graduationStatus'] = $session->selectedStatus;
                $defaults['graduationMonth'] = $session->selectedGradMonth;
                $defaults['graduationYear'] = $session->selectedGradYear;
                $defaults['section'] = $session->selectedSection;
            }
        } else {
            // otherwise, we're not even tracking namespace
            $defaults['namespace'] = null;
        }

        // set up the default state: all cert levels, active grad status, all grad dates, all sections
        if ($useBlankSlate) {
            $defaults['certificationLevels'] = array();
            $defaults['graduationStatus'] = array(1);
            $defaults['graduationMonth'] = 0;
            $defaults['graduationYear'] = 0;
            $defaults['section'] = 0;
        }

        return $defaults;
    }

    /**
     * Returns the html for the student filter form
     *
     * @param User $user the logged in user
     * @param $defaults a keyed array containing information about which filters should be selected
     *
     * @return string the html for the student filter form
     */
    private function getFilters(User $user, $defaults)
    {
        $filters = "<div id='ms-picklist-filter-form'>";
        $filters .= "<div class='grid_5 first_col'>";
        $filters .= $this->getCertificationCheckboxes($defaults['certificationLevels']);
        $filters .= $this->getSectionElements($defaults['section']);
        $filters .= "</div>";

        $filters .= "<div class='grid_6'>";
        $filters .= $this->getGraduatingElements($user, $defaults['graduationMonth'], $defaults['graduationYear']);
        $filters .= $this->getGradStatusElements($defaults['graduationStatus']);
        $filters .= "</div>";

        // create a hidden element to let the form know whether or not to update a given namespace during processing
        if (isset($defaults['namespace'])) {
            $filters .= "<input type='hidden' value='" . $defaults['namespace'] . "' name='session_namespace'>";
        }

        $filters .= "</div>";

        return $filters;
    }

    /**
     * Returns the html for the certification level filter
     *
     * @param $defaults an array containing the ids of which certifications should be selected
     *
     * @return string the html for the certification level filter
     */
    private function getCertificationCheckboxes($defaults)
    {
        $certLevels = \Fisdap\Entity\CertificationLevel::getFormOptions(false, false, "description");

        $filters = "<div class='certification-levels'>";
        $filters .= $this->getElementTitle("Certification Level");
        $filters .= $this->createCheckboxes($certLevels, "certificationLevels", $defaults);
        $filters .= "</div>";

        return $filters;
    }

    /**
     * Returns the html for the student group filter
     *
     * @param null|string|int $selectedSection the id of the selected student group
     *
     * @return string the html for the student group filter
     */
    private function getSectionElements($selectedSection = null)
    {
        $user = \Fisdap\Entity\User::getLoggedInUser();
        $classSectionRepository = \Fisdap\EntityUtils::getRepository('ClassSectionLegacy');
        $classSections = $classSectionRepository->getFormOptions($user->getProgramId());
        array_unshift($classSections, array(0 => "Any group"));

        $filters = "<div class='class-section-elements'>";
        $filters .= "<div class='class-section-section'>";
        $filters .= '<label for="section">' . $this->getElementTitle("Groups") . '</label>';
        $filters .= $this->view->formSelect('section', $selectedSection, array(), $classSections);
        $filters .= "</div>";

        $filters .= "</div>";

        return $filters;
    }

    /**
     * Returns the html for the graduation date filter
     *
     * @param User $user the logged in user
     * @param string|int $defaultMonth numeric representation of the graduation month
     * @param string|int $defaultYear 4 digit numeric representation of the graduation year
     *
     * @return string the html for the graduation date filter
     */
    private function getGraduatingElements(User $user, $defaultMonth, $defaultYear)
    {
        $program = $user->getProgram();
        $years = $program->get_possible_graduation_years();
        $months = Util_FisdapDate::get_month_prompt_names();

        $filters = $this->getElementTitle("Graduating");
        $filters .= $this->view->formLabel("", "", array());

        $filters .= $this->view->formSelect("graduationMonth", $defaultMonth, array(), $months);
        $filters .= $this->view->formSelect("graduationYear", $defaultYear, array(), $years);

        return $filters;
    }

    /**
     * Returns the html for the graduation status filter
     *
     * @param array $defaults an array containing the values of the selected graduation statuses
     *
     * @return string the html for the graduation status filter
     */
    private function getGradStatusElements($defaults)
    {
        $statusOptions = array(
            1 => "Active",
            4 => "Left Program",
            2 => "Graduated",
        );

        $filters = "<div class='grad-status'>";
        $filters .= "<label for='graduationStatus'>" . $this->getElementTitle("Graduation Status") . "</label>";
        $filters .= $this->createCheckboxes($statusOptions, "graduationStatus", $defaults);
        $filters .= "</div>";

        return $filters;
    }

    /**
     * Returns the html for a filter title
     *
     * @param string $title
     *
     * @return string the html for a filter title
     */
    private function getElementTitle($title)
    {
        return "<h4 class='element-title'>" . $title . ":</h4>";
    }

    /**
     * Returns the html for a checkbox on the filter, and checks it if indicated by the defaults
     *
     * @param array $data an array with the checkbox options, keyed by value
     * @param string $elementName the name of the checkbox group
     * @param array|null $defaults an array containing the values of the checkboxes that are checked
     *
     * @return string the html for a group of checkboxes
     */
    private function createCheckboxes(array $data, $elementName, $defaults)
    {
        $filters = "";
        foreach ($data as $dataId => $dataName) {
            $elementId = $elementName . "-" . $dataId;

            $checked = "";
            if (is_array($defaults) && in_array($dataId, $defaults)) {
                $checked = "checked";
            }

            $filters .= "<input type='checkbox' value='" . $dataId . "' name='" . $elementName . "[]' id='" . $elementId . "' " . $checked . ">";
            $filters .= "<label for='" . $elementId . "'>" . $dataName . "</label><br />";
        }

        return $filters;
    }

    /**
     * If custom help text is specified, return the html for the help bubble
     *
     * @param string $helpText either the name of a view script to be rendered (with phtml suffix), or the text to be put in the help bubble
     *
     * @return string | null the html for the help bubble
     */
    private function getHelpText($helpText = null)
    {
        // include help text if requested by helpText option
        if (isset($helpText)) {
            // if this string has a .phtml suffix, we're being asked to render a view script
            if (stripos(strrev($helpText), 'lmthp.') === 0) {
                // render a .phtml file as specified
                $renderedHelpText = $this->view->render($helpText);
                return $renderedHelpText;
            } else {
                // just render the HTML string that was provided
                $renderedHelpText = '<div class="multistudent-picker-help">' . $helpText . '</div>';
                return $renderedHelpText;
            }
        }
        return;
    }

    /**
     * Get the students that will be listed in the picker
     *
     * @param User $user the current user
     * @param array $options the student picker options array
     * @param array $filterDefaults the full array of filters
     * @param string $filterForm the html for the filters, passed by reference so we can clear it, if necessary
     *
     * @return array an array of students, each student in array format
     */
    private function getStudents(User $user, $options, $filterDefaults, &$filterForm)
    {
        // get the explicitly specified students
        if (isset($options['selectableStudentIds']) && is_array($options['selectableStudentIds'])) {
            $rawStudents = array();
            foreach ($options['selectableStudentIds'] as $sid) {
                $student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $sid);
                $rawStudents[] = $student->toArray();
            }

            // clear out the filter, since we don't need it if we've been given an explicit list of students
            $filterForm = "";

            return $rawStudents;
        } else {
            // or get the students for this program, filtered using the defaults given
            $rawStudents = \Fisdap\EntityUtils::getRepository('User')->getAllStudentsByProgram($user->getProgramId(), $filterDefaults);
            return $rawStudents;
        }
    }

    /**
     * Spits out an array containing summarizing of what student/group of students we're looking at
     * Currently only used by the reports engine
     *
     * @param array $options options outlining how the picker should render
     * @param array|null $config a configuration for which students are selected
     *
     * @return array summary of selected students, keyed by section title
     */
    public function MultistudentPicklistSummary($options = array(), $config = array())
    {
        // Render the picklist using view script found in the default module
        if (!isset($options['mode']) || $options['mode'] == 'multiple') {
            $section = "Student(s)";

            //If hybrid mode is on and we received a single student, handle it
            if ($config['picklist_mode'] == "single") {
                $student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $config['student']);
                if ($student) {
                    $info = $student->getFullname();
                }
            } else {
                $students = explode(",", $config['multistudent_picklist_selected']);
                if (count($students) > 1) {
                    $info = count($students) . ' students';
                } elseif (count($students) == 1 && $students[0] > 0) {
                    $student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $students[0]);
                    $info = $student->getFullname();
                }
            }
        } elseif ($options['mode'] == 'single') {
            $section = "Student";
            $student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $config['student']);
            if ($student) {
                $info = $student->getFullname();
            }
        }

        if ($config['anonymous'] == 1) {
            $user = \Fisdap\Entity\User::getLoggedInUser();
            $info .= $this->isStudentVersion($options, $user) ? ", with anonymous class data" : ", anonymized";
        }

        return array($section => $info);
    }

    /**
     * Validates the student picker based on current user and mode.
     * Currently only used by the reports engine.
     *
     * @param array $options options outlining how the picker should render
     * @param array|null $config a configuration for which students are selected
     *
     * @return array an array of errors for each aspect that failed validation
     */
    public function multistudentPicklistValidate($options, $config)
    {
        $errors = array();
        $mode = $options['mode'];
        if ($mode == "single") {
            $input = "student";
            $input_id = "available-list";
            $studentPhrase = "student";
        } else {
            //We have to handle the "hybrid" case, where we have a single student picker element
            //inside of the multistudent picklist
            if ($config['picklist_mode'] == "single") {
                $input = "student";
                $input_id = "available-list";
                $studentPhrase = "student";
            } else {
                $input = $input_id = "multistudent_picklist_selected";
                $studentPhrase = "or more students";
            }
        }

        $selection = explode(',', $config[$input]);

        // make sure a student is chosen
        if (count($selection) < 1 || $selection[0] == "") {
            $errors[$input_id][] = "Please select one $studentPhrase.";
        }

        // make sure only one student is chosen in single mode
        if (count($selection) > 1 && $mode == "single") {
            $errors[$input_id][] = "Please select only one student.";
        }

        // if this is a student, make sure they are the one chosen
        $user = \Fisdap\Entity\User::getLoggedInUser();
        if (!$user->isInstructor()) {
            if (count($selection) != 1 || $user->getCurrentRoleData()->id != $selection[0]) {
                $errors[$input_id][] = "You cannot view that student.";
            }
        }

        return $errors;
    }

    /**
     * Are we currently looking at the student version of this picker?
     *
     * @param array $options options outlining how the picker should render
     * @param $user current user
     *
     * @return bool true if we're currently looking at the student version of this picker
     */
    private function isStudentVersion($options, $user)
    {
        return (isset($options['studentVersion']) && $options['studentVersion'] && $user->getCurrentRoleName() == 'student') ? true : false;
    }

    /**
     * Get an array of the students selected by the picker
     *
     * @param array|null $config a configuration for which students are selected
     * @param string|null $mode the mode (single or multiple) that the picker is in, defaults to multiple
     *
     * @return array an array of the student ids of the selected students
     */
    private function getSelectedStudents($config, $mode)
    {
        if ($mode == 'single') {
            $selectedStudents = array($config['student']);
            return $selectedStudents;
        } else {
            $selectedStudents = ($config['picklist_mode'] == "single") ? array($config['student']) : explode(",", $config['multistudent_picklist_selected']);
            return $selectedStudents;
        }
    }

    /**
     * Go through the student options and add their formatted names/ids to the appropriate list(s)
     *
     * @param array $options options outlining how the picker should render
     * @param array $rawStudents an array of student entities in array format
     *
     * @return array an array of formatted student options, sorted by list
     */
    private function sortStudents($options, $rawStudents)
    {
        $selected = $selectable = array();
        $longLabel = (isset($options['longLabel']) && $options['longLabel']) ? true : false;
        foreach ($rawStudents as $student) {
            $label = $student['first_name'] . ' ' . $student['last_name'];
            if ($longLabel) {
                $label .= ", " . $student['cert_description'] . ": " . $student['graduation_month'] . "/" . $student['graduation_year'];
            }

            // if this is an selected student, add it to both lists
            if (is_array($options['selectedStudents']) && in_array($student['id'], $options['selectedStudents'])) {
                $selected[$student['id']] = $label;
                $selectable[$student['id']] = $label;
            } else {
                $selectable[$student['id']] = $label;
            }
        }
        return array('selectable' => $selectable, 'selected' => $selected);
    }

    /**
     * Load the appropriate Javascript and CSS files into the view
     *
     * @param array $options options outlining how the picker should render
     *
     * @return null
     */
    private function loadCssAndJs($options)
    {
        if (isset($options['loadJSCSS']) && $options['loadJSCSS']) {
            // everybody gets the standard js function library
            $this->view->headScript()->prependFile("/js/library/Fisdap/View/Helper/multistudent-picklist-library.js");

            // everybody gets the standard stylesheet
            $this->view->headLink()->appendStylesheet("/css/library/Fisdap/View/Helper/multistudent-picklist.css");

            // now figure out if this instance of the picklist is standard or custom javascript functionality
            if (isset($options['picklistJS'])) {
                // use custom javascript file
                $this->view->headScript()->appendFile($options['picklistJS']);
            } else {
                // use standard javascript files
                $this->view->headScript()->prependFile("/js/library/Fisdap/View/Helper/multistudent-picklist.js");
            }

            // now figure out if this instance of the picklist gets custom styling, too
            if (isset($options['picklistCSS'])) {
                $this->view->headLink()->appendStylesheet($options['picklistCSS']);
            }

            // JS/CSS for fancy filters
            $this->view->headScript()->appendFile("/js/jquery.fancyFilters.js");
            $this->view->headLink()->appendStylesheet("/css/jquery.fancyFilters.css");
        }

        return;
    }

    /**
     * Get the fully formatted and sorted array of student options
     *
     * @param User $user the current user
     * @param array|null $config a configuration for which students are selected
     * @param array $options options outlining how the picker should render
     * @param array $filterDefaults the full array of filters
     * @param string|null $mode the mode (single or multiple) that the picker is in, defaults to multiple
     * @param string $filterForm the html for the filters, passed by reference so we can clear it, if necessary
     *
     * @return array the fully formatted and sorted array of student options, if applicable
     */
    private function getStudentArray(User $user, $config, $options, $filterDefaults, $mode, &$filterForm)
    {
        if (isset($options['loadStudents']) && $options['loadStudents']) {
            // get the students we're interested in
            $rawStudents = $this->getStudents($user, $options, $filterDefaults, $filterForm);

            // figure out which student(s) are already selected, whether or not to anonymize data
            if ($config) {
                // the selected students defined in the config array overwrite the ones found in the options array
                $options['selectedStudents'] = $this->getSelectedStudents($config, $mode);
                $anonChecked = ($config['anonymous']) ? "checked" : "";
            }

            // go through all the student options we figured out above and add their formatted names/ids to the appropriate list(s)
            $students = $this->sortStudents($options, $rawStudents);

            return array("anonChecked" => $anonChecked, "students" => $students);
        } else {
            // otherwise, don't load any students
            $students = array();
            return array("students" => $students);
        }
    }
}
