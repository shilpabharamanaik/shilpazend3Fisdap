<?php

use Fisdap\Api\Users\CurrentUser\CurrentUser;

/**
 * Class Fisdap_Reports_Report
 * This is the base class for Fisdap 2.0 Reports
 * Includes methods for quickly generating forms and standard data display options
 * Create a new Fisdap Report by extending this class.
 */
class Fisdap_Reports_Report
{
    public $report_id;

    /**
     * @var string HTML legend/header for the report. Appears above the report results
     */
    public $header = 'Welcome to this new report.';

    /**
     * @var string HTML footer for the report. Appears below the report results
     */
    public $footer = 'Fisdap reports are generated using PHP.';

    public $user;

    /**
     * @var boolean
     */
    public $isStudent;

    /**
     * $this->formComponents should be an array of form components, which should work for most reports
     * Here is the format:
     *  $this->formComponents = array(
     * 'reportClassMethodName' => array(  // the name of a method on the report class that returns HTML
     * 'title' => 'Select shift information', // title of this component
     * ),
     * 'viewHelperClassName' => array( // the name of a viewHelper class that returns HTML
     * 'title' => 'Select one or more student(s)', // title of this component
     * 'options' =>  array( // any options that need to be passed as an $options array to the view helper
     * 'mode' => 'single',
     * 'loadJSCSS' => TRUE,
     * 'loadStudents' => TRUE,
     * 'showTotal' => TRUE,
     * ),
     * ),
     * // IMPORTANT caveats about using Zend Form:
     * // 1) a Zend Form component probably needs to omit a submit button
     * // 2) a Zend Form needs to implement a getReportSummary() method
     * // 3) a Zend Form needs to specify decorates, with a phtml view script for the form, otherwise <form></form> gets printed
     * //    which messes up the report form.
     * 'Zend_Form_Class_Name' => array( // the name of a Zend Form class (instantiates a Zend Form)
     * 'title' => 'Random test ZendForm!', // title of this component
     * 'arguments' => array( // any arguments that should be passed (in order) to the Zend Form constructor
     * array(),
     * ),
     * ),
     * );
     *
     * If the form is so custom that components cannot be used in this fashion, then you can instead override
     * the generateForm, validateForm and renderForm methods in your report class.
     * @var array Array of form components in a special format.
     */
    public $formComponents = array();

    /**
     * @var array Property used to compile the generated form
     */
    public $form = array();

    /**
     * @var array Holds user-legible label/value parts generated by $this->getSummary()
     */
    public $summaryParts = array();

    /**
     * @var array Report configuration, usually the result of AJAX POST form values
     */
    public $config = array();

    /**
     * @var object Zend View object from the controller
     */
    public $view;

    /**
     * @var array style sheets to be appended
     */
    public $styles = array();

    /**
     * @var array script libraries to be appended
     */
    public $scripts = array();

    /**
     * See runReport() method for how to properly structure $data array (table of tables)
     * @var array The data that is the result of running the report, to be rendered by renderReport()
     */
    public $data = array();

    public $valid = true;

    public $errors = array();

    // Constructor
    public function __construct($report, $config = array(), $reportUser = null)
    {
        $this->report_id = $report->id;
        $this->title = $report->name;
        $this->description = $report->description;

        // get the user and figure out if this is a student
        // unless report is being run by CLI

        if (PHP_SAPI == 'cli') {
            $this->currentUser = Zend_Registry::get('container')->make(CurrentUser::class);
            $this->user = $config->user_context->user;
            $this->currentUser->setUser($config->user_context->user);
        } else {
            $this->currentUser = Zend_Registry::get('container')->make(CurrentUser::class);
            $this->user = $this->currentUser->user();
            $this->isStudent = ($this->user->getCurrentRoleName() == 'student') ? true : false;

            // Get the view
            $this->view = Zend_Controller_Front::getInstance()
                ->getParam('bootstrap')
                ->getResource('view');
        }

        // generic processing of any data posted to the report
        if (!empty($config)) {
            // Set $this->config to posted values
            // CLI reports will send the ReportConfiguration entity in order to access the user above, convert that to an array before setting $this->config
            if (is_array($config)) {
                $this->config = $config;
            } else {
                $this->config = $config->get_config();
            }
        }
    }

    /**
     * Mapper function to set report properties from results stored in cache
     * @param array $cachedData should be array('data' => array(), 'header' => '', 'footer' => '')
     */
    public function setValuesFromCache(array $cachedData)
    {
        $keysToSet = array('data', 'header', 'footer');
        foreach ($keysToSet as $key) {
            if (isset($cachedData[$key])) {
                $this->{$key} = $cachedData[$key];
            }
        }
    }

    /**
     * As a default, reports are viewable by all users.
     * Override this if your report has different permissions!
     */
    public static function hasPermission($userContext)
    {
        return true;
    }

    /**
     * Construct a form by checking for standard reusable forms
     * in $this->formComponents. Sets $this->form
     */
    public function generateForm()
    {
        if (is_array($this->formComponents) && !empty($this->formComponents)) {
            foreach ($this->formComponents as $method => $info) {
                $component = array(
                    'title' => $info['title'],
                    'description' => $info['description'],
                    'name' => $info['name'],
                    'options' => $info['options']
                );

                // this might be a custom method on the report class
                if (method_exists($this, $method)) {
                    if (isset($info['options'])) {
                        $component['content'] = $this->{$method}($this->config, $info['options']);
                    } else {
                        $component['content'] = $this->{$method}($this->config);
                    }
                } elseif ((bool)$this->view->getPluginLoader('helper')->load($method, false)) {
                    // or this might be a view helper
                    if ($method == "multistudentPicklist") {
                        $component['content'] = $this->view->{$method}($this->user, $this->config, $info['options']);
                    } elseif (isset($info['options'])) {
                        $component['content'] = $this->view->{$method}($this->config, $info['options']);
                    } else {
                        $component['content'] = $this->view->{$method}($this->config);
                    }
                } else {
                    // we just assume it's a Zend Form
                    if (isset($info['arguments']) && is_array($info['arguments'])) {
                        $reflector = new ReflectionClass($method); // http://stackoverflow.com/questions/3395914/pass-arguments-from-array-in-php-to-constructor
                        $arguments = array($this->config) + $info['arguments'];
                        $form = $reflector->newInstanceArgs($arguments);
                    } else {
                        $form = new $method($this->config);
                    }
                    $component['content'] = $form;
                    $component['ZendForm'] = true;
                }

                $this->form[$method] = $component;
            }

            // add the default "report ID" element
            $reportID = new Zend_Form_Element_Hidden('report_id');
            $reportID->setValue($this->report_id);
            $this->form['report-id'] = array(
                'title' => 'Report ID',
                'hidden' => true,
                'content' => $reportID,
            );

            // add the default "config ID" element
            $configID = new Zend_Form_Element_Hidden('config_id');
            $configID->setValue(0); // we're assuming no config id! @todo maybe this is wrong
            $this->form['config-id'] = array(
                'title' => 'Config ID',
                'hidden' => true,
                'content' => $configID,
            );

            // and the report class name, just for helpful reference
            $reportClass = new Zend_Form_Element_Hidden('report_class');
            $reportClass->setValue(str_replace('Fisdap_Reports_', '', get_class($this)));
            $this->form['report-class'] = array(
                'title' => 'Report Class',
                'hidden' => true,
                'content' => $reportClass,
            );
        }
    }

    /**
     * Construct a nifty summary by checking for standard reusable forms
     * in $this->formComponents.
     * Also stores user-legible parts of the summary in $this->summaryParts
     */
    public function getSummary($format = "div")
    {
        if (empty($this->summaryParts)) {
            // generate the user-legible summary bits if we don't have them yet
            foreach ($this->formComponents as $method => $info) {
                $summaryMethod = $method . "Summary";
                if (method_exists($this, $summaryMethod)) {
                    // this might be a custom method on the report class
                    $this->summaryParts += $this->{$summaryMethod}($info['options'], $this->config);
                } elseif ((bool)$this->view->getPluginLoader('helper')->load($method, false)) {
                    // or this might be a view helper
                    $viewHelperObj = $this->view->getHelper($method);
                    $this->summaryParts += $viewHelperObj->{$summaryMethod}($info['options'], $this->config);
                } else {
                    // we just assume it's a Zend Form
                    if (isset($info['arguments']) && is_array($info['arguments'])) {
                        $reflector = new ReflectionClass($method); // http://stackoverflow.com/questions/3395914/pass-arguments-from-array-in-php-to-constructor
                        $form = $reflector->newInstanceArgs($info['arguments']);
                    } else {
                        $form = new $method();
                    }

                    $this->summaryParts += $form->getReportSummary($this->config);
                }
            }

            //Add the who/when for the report if a user is logged in (which they should be)
            if ($user = \Fisdap\Entity\User::getLoggedInUser()) {
                $this->summaryParts["Created by"] = $user->getName() . " on " . date_create()->format('m/d/y \a\t H:i');
            }
        }

        // generate html
        $summaryHtml = "";
        foreach ($this->summaryParts as $section => $info) {
            switch ($format) {
                case "div":
                    $summaryHtml .= "<span class='summary-section'>$section:</span>
										  <span class='summary-info'> $info</span>
										  <br>\n";
                    break;
                case "table":
                    $summaryHtml .= "<tr>
											 <td class='summary-section'>$section:</td>
											 <td class='summary-info'> $info</td>
										  </tr>\n";
                    break;
            }
        }
        return $summaryHtml;
    }

    /**
     * Validate submitted form with either
     *      a) customMethodValidate method on this class ($this)
     *      b) $zendFormObject->validate()
     *
     */
    public function validate()
    {
        // call validate functions available for each form component
        // @todo maybe $info array can have a validationMethod option?
        foreach ($this->formComponents as $method => $info) {
            $validateMethod = $method . "Validate";

            // this might be a custom method on the report class
            if (method_exists($this, $validateMethod)) {
                // look for a custom validation method corresponding to it
                $this->{$validateMethod}($info);
            } elseif ((bool)$this->view->getPluginLoader('helper')->load($method, false)) {
                // or this might be a view helper
                $viewHelperObj = $this->view->getHelper($method);
                if (method_exists($viewHelperObj, $validateMethod)) {
                    $errors = $viewHelperObj->{$validateMethod}($info['options'], $this->config);

                    foreach ($errors as $error) {
                        $this->valid = false;
                        $this->errors[] = $error;
                    }
                }
            } elseif (class_exists($method)) { // we just assume it's a Zend Form
                if (isset($info['arguments']) && is_array($info['arguments'])) {
                    $reflector = new ReflectionClass($method); // http://stackoverflow.com/questions/3395914/pass-arguments-from-array-in-php-to-constructor
                    $form = $reflector->newInstanceArgs($info['arguments']);
                } else {
                    $form = new $method();
                }

                // check validation
                if (!$form->isValid($this->config)) {
                    $this->valid = false;
                    $this->errors[] = $form->getMessages();
                }
            }
        }

        return $this->errors;
    }

    /**
     * Render the form into final output. Assumes that $this->form is an array of form components (generation already occurred)
     * @return array Array of output to be printed by view script
     */
    public function renderForm()
    {
        $output = array(); // structured output array, which viewscript can use/print

        // wrap form components in standard form wrappers
        foreach ($this->form as $key => $component) {
            if ($component['hidden']) {
                $output[] = array(
                    'component' => $component['content'],
                );
            } else {
                $header = "<div class='grid_12 report-form-section'>";
                $header .= "<h3 class='section-header " . $component['name'] . "'>" . $component['title'] . "</h3>";
                $header .= "<div class='form-desc'>" . $component['description'] . "</div>";
                $footer = "</div><div class='clear'></div>";

                // in some custom circumstances, don't show the header/footer
                // sorry this is kinda hacky
                if ($key == 'multistudentPicklist' && $component['options']['studentVersion'] && $this->isStudent) {
                    $header = "";
                    $footer = "";
                }

                $output[] = array(
                    'header' => $header,
                    'component' => $component['content'],
                    'footer' => $footer,
                );
            }
        }

        // add the submit button
        $output[] =
            array(
                'component' =>
                    '<div class="report-button green-buttons">
                         <input id="go-button" type="submit" value="Go" name="go" role="button">
                    </div>
                    <div class="report-button gray-button">
                         <a href="#" id="hide-report-form">Close</a>
                    </div>
                    <div class="clear"></div>',
            );

        return $output;
    }

    /**
     * Run a query and any processing logic that produces the data contained in the report
     * Return a multidimensional array and it will be rendered as tables
     * OR return a string and it will be rendered as HTML
     * Sets $this->data
     */
    public function runReport()
    {
        // $this->config should have POSTed values for report configuration
        $this->data = array(
            // table 1
            array("type" => "table",
                "content" => array('title' => "Student Table",
                    'head' => array( // header row(s)
                        array(
                            'Name',
                            'Number',
                        ),
                    ),
                    'body' => array( // body rows
                        // row 1
                        array(
                            array(
                                'data' => 'Jesse Mortenson',
                                'class' => 'student-name',
                                'colspan' => 1,
                            ),
                            '254',
                        ),
                        // row 2
                        array(
                            array(
                                'data' => 'Scott McIntyre',
                                'class' => 'student-name',
                            ),
                            '184',
                        ),
                    ),
                ),
            ),

            // table 2
            array("type" => "table",
                "options" => array("noSort" => true, "noSearch" => true),
                "content" => array('title' => "Colors",
                    'head' => array( // head row(s)
                        array(
                            'Color1',
                            'Color2',
                            'Color3',
                        )
                    ),
                    'body' => array( // body row(s)
                        // row 1
                        array(
                            'red',
                            'blue',
                            'green'
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Render report results (the data)
     * @return string HTML of the finished/processed report data
     */
    public function renderReport()
    {
        $output = '<div id="report-results">';

        if (is_array($this->data)) {
            // render each data element
            foreach ($this->data as $key => $data) {
                $dataType = $data['type'];
                $content = $data['content'];
                switch ($dataType) {
                    case "html":
                        $output .= $content;
                        break;
                    case "eureka":
                        $output .= $this->renderEurekaGraph($content, $key);
                        break;
                    case "tabbed-tables":
                        $output .= $this->renderTabbedTables($content, $key, $data['options']);
                        break;
                    case "table":
                    default:
                        $output .= $this->renderTable($content, $key, $data['options']);
                        break;
                }
            }
        } else {
            // data is just a string, render directly as HTML
            $output = $this->data;
        }

        $output .= '</div>';

        // add the other report properties
        $header = '<div class="report-header">' . $this->header . '</div>';
        $footer = '<div class="report-footer">' . $this->footer . '</div>';

        $output = $header . $output . $footer;

        return $output;
    }

    /**
     * Render the a set of tabbed tables for this report
     * @return string HTML of the tables
     */
    public function renderTabbedTables($tables, $title, $options = array())
    {
        $output = "";

        // print out the table title, if there is one
        if ($title) {
            $output .= "<h3 class='section-header'>$title</h3>";
        }

        // set up the tabs
        $output .= '<div class="tabs">';
        $output .= '<ul>';
        foreach ($tables as $key => $table) {
            $output .= '<li><a href="#' . $key . '">' . $table['tab'] . '</a></li>';
        }
        $output .= '</ul>';

        foreach ($tables as $key => $table) {
            // set up the csv title for this table, so csv export grabs ALL the tables and labels them appropriately
            $options['csvTitle'] = "$title: " . $table['tab'];

            // render the individual table for this tab
            $output .= '<div id="' . $key . '" class="tabbed-table">';
            $output .= $this->renderTable($table, $key, $options);
            $output .= '</div>';
        }
        $output .= '</div>';
        return $output;
    }

    /**
     * Render the a table for this report
     * @return string HTML of the table
     */
    public function renderTable($table, $key, $options = array())
    {
        // figure out table options
        if ($options['noSort']) {
            $noSort = "data-noSort='TRUE'";
        }
        if ($options['noSearch']) {
            $noSearch = "data-noSearch='TRUE'";
        }
        if ($options['noInfo']) {
            $noInfo = "data-noInfo='TRUE'";
        }

        if ($options['tableClass']) {
            $tableClass = "class='" . $options['tableClass'] . "'";
        }

        if ($options['csvTitle']) {
            $csvTitle = "data-csvTitle='" . $options['csvTitle'] . "'";
        }

        $nullMsg = ($table['nullMsg']) ? $table['nullMsg'] : "Your search did not find any results.";

        // start the output
        $output = "<div class='table-container'>";

        // print out the table title, if there is one
        if ($table['title']) {
            $searchClass = ($noSearch) ? "no-search" : "";
            $output .= "<h3 class='section-header no-border table-title $searchClass'>" . $table['title'] . "</h3>";
        }

        $output .= "<table id='fisdap-report-" . get_class($this) . "-$key' $tableClass data-nullMsg='$nullMsg' width='100%' $noSort $noSearch $noInfo $csvTitle>";

        // first the header
        $output .= '<thead>';
        if ($table['head']) {
            foreach ($table['head'] as $row) {
                $output .= '<tr>';
                foreach ($row as $cell) {
                    if (is_array($cell)) {
                        $colspan = (isset($cell['colspan'])) ? ' colspan="' . $cell['colspan'] . '"' : '';
                        $rowspan = (isset($cell['rowspan'])) ? ' rowspan="' . $cell['rowspan'] . '"' : '';
                        $output .= '<th class="' . $cell['class'] . '"' . $colspan . '"' . $rowspan . '>' . $cell['data'] . '</th>';
                    } else {
                        $output .= '<th>' . $cell . '</th>';
                    }
                }
                $output .= '</tr>';
            }
        }
        $output .= '</thead><tbody>';

        // then the body
        if ($table['body']) {
            foreach ($table['body'] as $row) {
                $output .= '<tr>';
                foreach ($row as $cell) {
                    if (is_array($cell)) {
                        $class_output = (isset($cell['class'])) ? 'class="' . $cell['class'] . '"' : '';
                        $colspan = (isset($cell['colspan'])) ? ' colspan="' . $cell['colspan'] . '"' : '';
                        $rowspan = (isset($cell['rowspan'])) ? ' rowspan="' . $cell['rowspan'] . '"' : '';
                        $title = (isset($cell['title'])) ? ' title="' . $cell['title'] . '"' : '';
                        $output .= '<td ' . $class_output . ' ' . $colspan . '' . $rowspan . '' . $title . '>' . $cell['data'] . '</td>';
                    } else {
                        $output .= '<td>' . $cell . '</td>';
                    }
                }
                $output .= '</tr>';
            }
        }
        $output .= '</tbody>';

        // include a footer, if there is one
        if (isset($table['foot'])) {
            $output .= '<tfoot>';
            foreach ($table['foot'] as $footerType => $row) {
                $output .= '<tr class="' . $footerType . '">';
                foreach ($row as $cell) {
                    if (is_array($cell)) {
                        $colspan = (isset($cell['colspan'])) ? ' colspan="' . $cell['colspan'] . '"' : '';
                        $title = (isset($cell['title'])) ? ' title="' . $cell['title'] . '"' : '';
                        $output .= '<td class="' . $cell['class'] . '"' . $colspan . ' ' . $title . '>' . $cell['data'] . '</td>';
                    } else {
                        $output .= '<td>' . $cell . '</td>';
                    }
                }
                $output .= '</tr>';
            }
            $output .= '</tfoot>';
        }

        $output .= '</table>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Render a eureka graph for this report
     * @return string HTML of the graph
     */
    public function renderEurekaGraph($data, $key)
    {
        $output = "<div class='eureka_report_wrapper'>" . $data . "</div>";
        return $output;
    }

    /**
     * Return a short label/description of the report using report configuration
     * Useful in listing saved Report Configurations as a saved report history
     * Override this if your report should display something different!
     */
    public function getShortConfigLabel()
    {
        //var_export($this->config);
        // get the student name or # of students
        $studentsLabel = '';

        // if we're in single student mode
        if (isset($this->config['student']) &&
            is_numeric($this->config['student']) &&
            $this->config['picklist_mode'] != 'multiple'
        ) {
            $student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $this->config['student']);
            if ($student) {
                $studentsLabel = $student->user->getName();
            }
        } elseif (isset($this->config['multistudent_picklist_selected'])) {
            $students = explode(",", $this->config['multistudent_picklist_selected']);
            if (count($students) > 1) {
                $studentsLabel = count($students) . ' students';
            } else {
                $student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $students[0]);
                $studentsLabel = $student->user->getName();
            }

            if ($this->config['anonymous'] == 1) {
                $studentsLabel .= ", anon.";
            }
        }

        // return the label
        return $studentsLabel;
    }

    /**
     * processes the chosen results from the sites filter to return a clean array of site ids
     */
    public function getSiteIds($sortedByType = false)
    {
        $chosen = $this->config['sites_filters'];

        // get the data from the repo
        $site_ids = \Fisdap\EntityUtils::getRepository('SiteLegacy')->parseSelectedSites($chosen, $sortedByType);
        return $site_ids;
    }

    /**
     * processes the chosen results from the patient types filter to return a clean array of type ids
     */
    public function getTypeIds()
    {
        $chosen = $this->config['patient_filters'];

        $subjectRepository = \Fisdap\EntityUtils::getRepository('Subject');
        $subjectService = new \Fisdap\Service\CoreSubjectService();

        $type_ids = $subjectService->makeSubjectIdsArray($subjectRepository, $chosen);
        return $type_ids;
    }

    /**
     * processes the chosen results from the multistudent picklist
     * to return a clean array of student names (anonymized per request) keyed with student id
     */
    public function getMultiStudentData($sortableByLast = false)
    {
        // single student mode
        if (isset($this->config['student']) &&
            is_numeric($this->config['student']) &&
            $this->config['picklist_mode'] != 'multiple'
        ) {
            $students = array($this->config['student']);
        } else {
            // multi student mode
            $student_ids = $this->config['multistudent_picklist_selected'];
            $students = explode(",", $student_ids);
        }
        $anon = $this->config['anonymous'];

        // retrieve transformed data corresponding to the student IDs
        $user = \Fisdap\Entity\User::getLoggedInUser();
        $repo = \Fisdap\EntityUtils::getRepository('User');
        $studentService = new \Fisdap\Service\CoreStudentService();
        $studentsTransformed = $studentService->transformStudentIds($user, $repo, $students, $anon); //$this->newGetMultiStudentData($student_ids, $anon);

        // For some reason we're supporting the sortableByLast option inside of runReport(), so doing it here
        // similar to method on view helper, but this way we're not dependent on the view
        if ($sortableByLast) {
            $studentsTransformed = array_map(function ($student) {
                $newStudent = array(
                    'first_last_combined' => "<span class='hidden'>" . trim($student['last_name']) . "</span>" . $student['first_last_combined'],
                    'first_name' => $student['first_name'],
                    'last_name' => $student['last_name'],
                );

                return $newStudent;
            }, $studentsTransformed);
        }

        return $studentsTransformed;
    }

    // adds a page break to the report
    protected function addPageBreak()
    {
        // add a page break for pdf use
        $this->data[] = array("type" => "html",
            "content" => "<div style='page-break-after: always'></div>",
        );
    }

    // return a zero even for null values
    protected function getNumericValue($value)
    {
        return ($value) ? $value : 0;
    }

    // return an array formatted to be used as a shift query filter
    protected function getShiftFilter()
    {
        // process report configuration to change filters
        $filter = array();
        if ($this->config['startDate'] != '') {
            $filter['start_datetime']['min'] = new DateTime($this->config['startDate']);
        }
        if ($this->config['endDate'] != '') {
            $filter['start_datetime']['max'] = new DateTime($this->config['endDate']);
        }
        if (is_array($this->config['sites_filters']) && count($this->config['sites_filters']) > 0) {
            $filter['siteIds'] = $this->getSiteIds();
        }
        if ($this->config['multistudent_picklist_selected'] != '') {
            // using the getMultiStudentData method gets us the anonymized classmates if this is a student report
            $filter['studentIds'] = array_keys($this->getMultiStudentData());
        }

        return $filter;
    }
}
