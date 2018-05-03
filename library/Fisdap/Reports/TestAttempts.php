<?php
/**
 * Created by PhpStorm.
 * User: jmortenson
 * Date: 4/30/14
 * Time: 2:16 PM
 */

class Fisdap_Reports_TestAttempts extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(

        'Fisdap_Reports_Form_SingleTestPicker' => array(
            'title' => 'Report options',
            'options' => array(
                'dateRange' => TRUE, // show the date range
            ),
        ),

    );

    /**
     * @var \Fisdap\Entity\MoodleTestDataLegacy $test
     */
    public $test = NULL;


    /**
     * This report is only available to STAFF ONLY
     */
    public static function hasPermission($userContext) {
        return $userContext->getUser()->isStaff();
    }

    /**
     * Run a query and any processing logic that produces the data contained in the report
     * Sets resulting tables as $this->data
     */
    public function runReport() {
        // process report configuration to change filters
        $filter = array();
        if ($this->config['dateRange']['startDate'] != '') {
            $filter['start_date'] = $this->config['dateRange']['startDate'];
        }
        if ($this->config['dateRange']['endDate'] != '') {
            $filter['end_date'] = $this->config['dateRange']['endDate'];
        }
        if ($this->config['test_id']) {
            $this->test = \Fisdap\EntityUtils::getEntity('MoodleTestDataLegacy', $this->config['test_id']);
        }

        // do we have the information we need?
        if (!$this->config['test_id'] || !$this->test instanceof \Fisdap\Entity\MoodleTestDataLegacy) {
            throw new Exception('An invalid test was submitted. Cannot generate report.');
        }

        // OK! Let's get all the attempts for this quiz within this period of time
        // and sort+count by program
        $progRepo = \Fisdap\EntityUtils::getRepository('ProgramLegacy');
        $programResults = $progRepo->getAllPrograms(array('id', 'name'));
        $programs = array();
        foreach($programResults as $programInfo) {
            $programs[$programInfo['id']] = $programInfo['name'];
        }
        $counts = \Fisdap\MoodleUtils::countProgramQuizAttempts(array_keys($programs), array($this->test));

        $body = array();
        foreach($counts as $programId => $tests) {
            $row = array($programs[$programId], $programId);
            foreach($tests as $testId => $count) {
                $row[] = $count;
            }
            $body[] = $row;
        }

        // OK let's make tables
        $this->data['TestAttempts'] = array(
            'type' => 'table',
            'options' => array(),
            'content' => array(
                'title' => 'Attempts for the selected tests',
                'nullMsg' => 'None found.',
                'head' => array(
                    array(
                        'Program Name',
                        'Program ID',
                        $this->test->get_test_name()
                    ),
                ),
                'body' => $body,
            )
        );
    }


    /**
     * Return a custom short label/description of the Test Attempts report
     * Overrides parent method
     */
    public function getShortConfigLabel() {
        // generate the form summary
        $this->getSummary('div');

        // return the label
        $label = $this->summaryParts['Exam'] . ": attempts ";
        if ($this->summaryParts['Date range']) {
            $label .= " from " . $this->summaryParts['Date range'];
        }

        return $label;
    }
}