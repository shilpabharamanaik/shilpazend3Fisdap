<?php

/**
 * Class Fisdap_Reports_Report
 * This is the base class for Fisdap 2.0 Reports
 * Includes methods for quickly generating forms and standard data display options
 * Create a new Fisdap Report by extending this class.
 */
class Fisdap_Reports_SkillsFinder extends Fisdap_Reports_Report
{
    public $header = '<h2 class="section-header no-border">Key</h2>
                      <div class="header-section">O = Observed<br>P = Performed</div>';

    public $footer = '';

    public $formComponents = array(
        'shiftInformationForm' => array(
            'title' => 'Select shift information',
            'options' => array(
                'pickPatientType' => false,
                'selected' => array('sites' => array())
            ),
        ),
        'skillFinderComponent' => array(
            'title' => 'Select skills information',
        ),
        'multistudentPicklist' => array(
            'title' => 'Select one or more student(s)',
            'options' => array(
                'loadJSCSS' => true,
                'loadStudents' => true,
                'showTotal' => true,
                'studentVersion' => true,
                'includeAnon' => true,
                'useSessionFilters' => true,
                'sessionNamespace' => "ReportStudentFilter",
            ),
        ),
    );

    public $skill_types = array(
        'Vital' => 'Vitals',
        'Airway' => 'Airway',
        'CardiacIntervention' => 'Cardiac',
        'Iv' => 'Venous Access',
        'Med' => 'Meds',
        'ALSSkills' => 'ALS Skills',
        'OtherIntervention' => 'Other Procedures',
        'Complaints' => 'Patient Complaints',
    'Impressions' => 'Impressions',
    );

    /**
     * arrays to be used for the table headers for each skill type
     */
    public $VitalHeader = array('1' => array('Date', 'Subject', 'Patient', 'O/P',
        'BP', 'Pulse', 'RR', 'SpO2', 'BGL', 'APGAR', 'GCS'));

    public $AirwayHeader = array('1' => array('Date', 'Subject', 'Patient', 'O/P',
        'Airway Type', 'ALS/BLS', 'Success', 'Size', 'Attempts'));

    public $CardiacInterventionHeader = array('1' => array('Date', 'Subject', 'Patient', 'Rhythm O/P',
        'Rhythm', '12 lead', 'Treatment O/P', 'Treatment'));

    public $IvHeader = array('1' => array('Date', 'Subject', 'Patient', 'O/P',
        'Procedure', 'Site', 'Size', 'Fluid', 'Attempts', 'Success'));

    public $MedHeader = array('1' => array('Date', 'Subject', 'Patient', 'O/P',
        'Medication', 'Route', 'Dose'));

    public $ALSSkillsHeader = array('1' => array('Date', 'Subject', 'Patient', 'O/P', 'ALS Skill', 'Success',
        'Attempts'));

    public $OtherInterventionHeader = array('1' => array('Date', 'Subject', 'Patient', 'O/P',
        'Skill'));

    public $ComplaintsHeader = array('1' => array('Date', 'Subject', 'Patient',
        'Complaint', 'Exam', 'Interview', 'Team Lead'));
        
    public $ImpressionsHeader = array('1' => array('Date', 'Subject', 'Patient', 'Primary Impression', 'Secondary Impression', 'Exam', 'Interview', 'Team Lead'));
 

    protected function skillFinderComponent($config = null)
    {
        $selected = $config['skill_type'];

        // A custom form component for this report
        $skillTypeSelect = new Zend_Form_Element_Select('skill_type');
        $skillTypeSelect->setLabel('Skill Type:')
            ->setAttribs(array("class" => "chzn-select"))
            ->setDecorators(array(
                'ViewHelper',
                'Errors',
                array(array('elementDiv' => 'HtmlTag'), array('tag' => 'div')),
                'Label',
                array('HtmlTag', array('tag' => 'div', 'class' => 'grid_6 input-section')),
            ));
        $skillTypeSelect->setMultiOptions($this->skill_types);

        if ($selected) {
            $skillTypeSelect->setValue($selected);
        }

        return $skillTypeSelect;
    }

    /**
     * Provides the array used to describe the "Skill Type" part of the report configuration summary.
     * @param array $options
     * @param array $config
     * @return array
     */
    protected function skillFinderComponentSummary($options = array(), $config = array())
    {
        $type = $this->skill_types[$config['skill_type']];
        return array("Skill Type" => $type);
    }

    /**
     * Run a query and any processing logic that produces the data contained in the report
     * Return a multidimensional array and it will be rendered as tables
     * OR return a string and it will be rendered as HTML
     * @return array
     */
    public function runReport()
    {
        // get the form info
        $skill_type = $this->config['skill_type'];
        $table_header = $skill_type . "Header";

        $students = $this->getMultiStudentData(true);
        $site_ids = $this->getSiteIds();
        $start_date = $this->config['startDate'];
        $end_date = $this->config['endDate'];

        $this->data = array();

        // Run a query to get data.
        $repo = \Fisdap\EntityUtils::getRepository('Report');
        $data = $repo->getStudentSkillsFinderData($skill_type, array_keys($students), $site_ids, $start_date, $end_date);

        // get the data for each of the chosen students
        foreach ($students as $student_id => $nameOptions) {
            $title = $nameOptions['first_last_combined'] . ": ".$this->skill_types[$skill_type];

            $table_data = array('title' => $title,
                'nullMsg' => "No skills found.",
                'head' => $this->{$table_header},
                'body' => array(),
            );

            if ($data[$student_id]) {
                foreach ($data[$student_id] as $id => $skill_info) {
                    // add the row
                    $table_data['body'][$id] = $skill_info;
                }
            }

            // add the table
            $this->data[] = array("type" => "table",
                "content" => $table_data);
        }
        //var_export($this->data);
    }

    /**
     * Return a custom short label/description of the productivity report
     * Overrides parent method
     */
    public function getShortConfigLabel()
    {
        // get the student name or # of students
        $studentsLabel = '';
        $students = explode(",", $this->config['multistudent_picklist_selected']);
        if (count($students) > 1) {
            $studentsLabel = count($students) . ' students';
        } else {
            $student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $students[0]);
            $studentsLabel = $student->user->getName();
        }
        if ($this->config['anonymous'] == 1) {
            $studentsLabel .= ", anon";
        }

        // return the label
        return $studentsLabel . ": " . $this->skill_types[$this->config['skill_type']];
    }
}
