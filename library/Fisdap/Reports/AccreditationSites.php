<?php
/**
 * Class Fisdap_Reports_AccreditationSites
 * This is the CoAEMSP Appendix E/F CoAEMSP's Institutional Data Report class
 * Refer to Fisdap_Reports_Report for more documentation
 */
class Fisdap_Reports_AccreditationSites extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(
            'shiftInformationForm' => array(
                'title' => 'Select site information',
                'options' => array(
                    'pickPatientType' => false,
                    'selected' => array('startDate' => 'first day of January this year',
                                        'endDate' => 'last day of December this year',
                                        ),
                    'siteTypes' => array("Clinical", "Field"),
                    ),
                ),
            'accreditationInfoAccordion' => array(
                'title' => 'Selected sites',
                'options' => array(
                                   'selected_sites' => array("0-Clinical", "0-Field"),
                    ),
                ),
            'multistudentPicklist' => array(
                'title' => 'Select one or more student(s)',
                'options' =>  array(
                    'mode' => 'multiple',
                    'loadJSCSS' => true,
                    'loadStudents' => true,
                    'showTotal' => true,
                    'useSessionFilters' => true,
                    'sessionNamespace' => "ReportStudentFilter",
                    ),
                )
    );
    
    public $scripts = array("/js/library/Fisdap/Reports/accreditation-sites.js");
    
    public $styles = array("/css/library/Fisdap/Reports/accreditation-sites.css");

    /**
     * This report is only available to instructors
     */
    public static function hasPermission($userContext)
    {
        return ($userContext->isInstructor());
    }

    /**
     * Run a query and any processing logic that produces the data contained in the report
     * Return a multidimensional array and it will be rendered as tables
     * OR return a string and it will be rendered as HTML
     * @return array
     */
    public function runReport()
    {
        // get the form values
        $formValues = array();
        $formValues['startDate'] = $this->config['startDate'];
        $formValues['endDate'] = $this->config['endDate'];
        $studentInfo = $this->getMultiStudentData();
        $formValues['students'] = array();
        foreach ($studentInfo as $id => $nameOptions) {
            $formValues['students'][$id] = $nameOptions['first_last_combined'];
        }
        
        // add a page break for pdf use
        $this->data[] = array("type" => "html",
                              "content" => "<div style='page-break-after: always'></div>",
                              );
        
        // retrieve and order the sites
        $site_ids = $this->getSiteIds();
        $sites = \Fisdap\EntityUtils::getRepository("SiteLegacy")->findById($site_ids);
        @usort($sites, array('self', 'sortSitesByTypeName'));
        
        // loop through the sites and run the report for each
        foreach ($sites as $site) {
            // add the accreditation infor section
            $accred_info = $site->getAccreditationInfoByProgram(\Fisdap\Entity\User::getLoggedInUser()->getProgramId());
            $this->addAccreditationInfo($site, $accred_info);
            
            // add the report for this site type
            $method = $site->type == 'field' ? "addFieldReport" : "addClinicalReport";
            $this->{$method}($site, $formValues, $accred_info);
            
            // then add a page break
            $this->data[] = array("type" => "html",
                                  "content" => "<div class='page-break'></div>
												<div style='page-break-after: always'></div>",
                                  );
        }
        //var_export($this->data);
    }
    
    private function addAccreditationInfo($site, $accred_info)
    {
        // set up the table! start with the header
        $title = $site->type == 'field' ? "CoAEMSP (Appendix F) Field Internship Institutional Data Form" : "CoAEMSP (Appendix E) Clinical Affiliate Institutional Data Form";
        
        // first add the site info table
        $siteHeader = array('col1', 'col2');
        $siteBody = array(array(array('data' => 'Name:', 'class' => 'label'),
                            array('data' => $site->name, 'class' => '')),
                      array(array('data' => 'Address:', 'class' => 'label'),
                            array('data' => $site->getSiteAddress(), 'class' => '')),
                      array(array('data' => 'Chief Administrative Officer:', 'class' => 'label'),
                            array('data' => $accred_info->cao, 'class' => '')),
                      array(array('data' => 'Telephone #:', 'class' => 'label'),
                            array('data' => $accred_info->phone, 'class' => '')),
                      );
        $siteTableData = array('title' => $title,
                            'head' => array('siteInfo' => $siteHeader),
                            'body' => $siteBody,
                );
        $this->data[] = array("type" => "table",
                              "content" => $siteTableData,
                              "options" => array("noSort" => true,
                                                 "noSearch" => true,
                                                 "noInfo" => true,
                                                 "tableClass" => "hide-header",
                                                 "csvTitle" => $title . " - " . $site->name),
                              );
        
        // then add the questions table
        $infoHeader = array('col1', 'col2');
        
        // only fill in the data if the info is complete, otherwise leave it blank for them to fill in
        if (!empty($accred_info) && $accred_info->isComplete()) {
            $distance_from_program = $accred_info->getDistanceDescription();
            $signed_agreement = $accred_info->getYesNo('signed_agreement');
            $student_supervision_type = $accred_info->getSupervisionDescription();
            $written_policies = $accred_info->getYesNo('written_policies');
            $formally_trained_preceptors = $accred_info->getYesNo('formally_trained_preceptors');
            $preceptor_training_hours = $accred_info->getTrainingHoursDescription();
            
            // we have extra questions for field sites
            if ($site->type == "field") {
                $online_medical_direction = $accred_info->getYesNo('online_medical_direction');
                $advanced_life_support = $accred_info->getYesNo('advanced_life_support');
                $quality_improvement_program = $accred_info->getYesNo('quality_improvement_program');
            }
        }
        $infoBody = array(array(array('data' => 'Distance from the location of the program:', 'class' => 'question'),
                            array('data' => $distance_from_program, 'class' => '')),
                      array(array('data' => 'Is there a signed, current agreement with this clinical affliliate?', 'class' => 'question'),
                            array('data' => $signed_agreement, 'class' => '')),
                      array(array('data' => 'Who supervises the students?', 'class' => 'question'),
                            array('data' => $student_supervision_type, 'class' => '')),
                      array(array('data' => 'Are there written policies as to what students may do in each area?', 'class' => 'question'),
                            array('data' => $written_policies, 'class' => '')),
                      array(array('data' => 'Are the preceptors formally trained?', 'class' => 'question'),
                            array('data' => $formally_trained_preceptors, 'class' => '')),
                      array(array('data' => 'How many hours of preceptor training?', 'class' => 'question'),
                            array('data' => $preceptor_training_hours, 'class' => '')),
                      );
        
        // we have extra questions for field sites
        if ($site->type == "field") {
            $infoBody[] = array(array('data' => 'Is there online medical direction for this affiliate?', 'class' => 'question'),
                                array('data' => $online_medical_direction, 'class' => ''));
            $infoBody[] = array(array('data' => 'Does this affiliate provide Advanced Life Support?', 'class' => 'question'),
                                array('data' => $advanced_life_support, 'class' => ''));
            $infoBody[] = array(array('data' => 'Is there a quality improvement program that reviews runs?', 'class' => 'question'),
                                array('data' => $quality_improvement_program, 'class' => ''));
        }
            
        $infoTableData = array('title' => "",
                               'head' => array('accreditationInfo' => $infoHeader),
                               'body' => $infoBody
                               );
        $this->data[] = array("type" => "table",
                              "content" => $infoTableData,
                              "options" => array("noSort" => true,
                                                 "noSearch" => true,
                                                 "noInfo" => true,
                                                 "tableClass" => "hide-header",
                                                 "csvTitle" => "Accreditation Info - " . $site->name)
                              );
    }
    
    private function addClinicalReport($site, $formValues, $accred_info)
    {
        // get the form values
        $startDate = $formValues['startDate'];
        $endDate = $formValues['endDate'];
        $students = $formValues['students'];
        
        // add the stats table
        $statsHeader = array(array('data' => 'Rotation', 'class' => 'superheader'),
                             array('data' => 'Annual Visits/Shifts', 'class' => 'superheader'),
                             array('data' => 'Students Per Shift', 'class' => 'superheader'),
                             array('data' => 'Average # Shifts for a Student', 'class' => 'superheader'),
                             array('data' => 'Hours per Shift', 'class' => 'superheader'));
        
        $statsTableData = array('title' => "",
                                "nullMsg" => "No shifts where scheduled at ".$site->name." during this time.",
                                'head' => array('siteInfo' => $statsHeader),
                                'body' => array()
                                );
        
        // ok, now get the data
        $repo = \Fisdap\EntityUtils::getRepository('Report');
        $data = $repo->getClinicalSiteData($site->id, array_keys($students), $startDate, $endDate);

        // if there's data, loop through the results and add the rows
        if (count($data) > 0) {
            foreach ($data as $row) {
                $dataRow = array($row['Rotation'],
                                 array("data" => $row['AnnualVisits'], "class" => "center"),
                                 array("data" => number_format($row['StudentsPerShift'], 2), "class" => "center"),
                                 array("data" => number_format($row['ShiftsPerStudent'], 2), "class" => "center"),
                                 array("data" => number_format($row['HoursPerShift'], 2), "class" => "center")
                                );
        
                // add the row
                $statsTableData['body'][] = $dataRow;
            }
        }
        
        $this->data[] = array("type" => "table",
                              "content" => $statsTableData,
                              "options" => array("noSort" => true,
                                                 "noSearch" => true,
                                                 "csvTitle" => "Site Statistics - " . $site->name),
                              );
        
        // and now add the signature line
        $signatureLine = "<div class='signature'>CAO or Designate Signature: _______________________________</div>".
                         "<div class='signature-date'>Date: _____________________</div>".
                         "<div class='clear'></div>";
        $this->data[] = array("type" => "html",
                              "content" => $signatureLine
                              );
    }
    
    private function addFieldReport($site, $formValues, $accred_info)
    {
        // get the form values
        $startDate = $formValues['startDate'];
        $endDate = $formValues['endDate'];
        $students = $formValues['students'];
        
        // add the stats table
        $statsHeader = array('', 'Manually-entered<br>(data from the site)', 'Fisdap-calculated<br>(data from your program)');
        
        $statsTableData = array('title' => "Site Statistics",
                                "nullMsg" => "No shifts where scheduled during this time.",
                                'head' => array('siteInfo' => $statsHeader),
                                'body' => array()
                                );
        
        // ok, now get the data
        $repo = \Fisdap\EntityUtils::getRepository('Report');
        $data = $repo->getFieldSiteData($site->id, array_keys($students), $startDate, $endDate);
        // we just want the first row, since this is all subselects
        $data = $data[0];
        
        // add the rows
        $statsTableData['body'][] = array(array("data" => 'Number of runs per year',
                                                "class" => "statistic_category"),
                                          array("data" => $accred_info->number_of_runs ? $accred_info->number_of_runs : "",
                                                "class" => "right"),
                                          array("data" => $data['num_runs'] ? $data['num_runs'] : 0,
                                                "class" => "right"));

        $statsTableData['body'][] = array(array("data" => 'Number of active EMS units (excluding backups)',
                                                "class" => "statistic_category"),
                                          array("data" => $accred_info->active_ems_units ? $accred_info->active_ems_units : "",
                                                "class" => "right"),
                                          array("data" => "N/a",
                                                "class" => "right"));

        $statsTableData['body'][] = array(array("data" => 'Number of trauma calls per year',
                                                "class" => "statistic_category"),
                                          array("data" => $accred_info->number_of_trauma_calls ? $accred_info->number_of_trauma_calls : "",
                                                "class" => "right"),
                                          array("data" => $data['trauma'] ? $data['trauma'] : 0,
                                                "class" => "right"));


        $statsTableData['body'][] = array(array("data" => 'Number of critical trauma calls per year',
                                                "class" => "statistic_category"),
                                          array("data" => $accred_info->number_of_critical_trauma_calls ? $accred_info->number_of_critical_trauma_calls : "",
                                                "class" => "right"),
                                          array("data" => $data['critical'] ? $data['critical'] : 0,
                                                "class" => "right"));


        $statsTableData['body'][] = array(array("data" => 'Number of pediatric calls per year',
                                                "class" => "statistic_category"),
                                          array("data" => $accred_info->number_of_pediatric_calls ? $accred_info->number_of_pediatric_calls : "",
                                                "class" => "right"),
                                          array("data" => $data['peds'] ? $data['peds'] : 0,
                                                "class" => "right"));

        $statsTableData['body'][] = array(array("data" => 'Number of cardiac arrests per year',
                                                "class" => "statistic_category"),
                                          array("data" => $accred_info->number_of_cardiac_arrest_calls ? $accred_info->number_of_cardiac_arrest_calls : "",
                                                "class" => "right"),
                                          array("data" => $data['arrests'] ? $data['arrests'] : 0,
                                                "class" => "right"));

        $statsTableData['body'][] = array(array("data" => 'Number of cardiac calls (less cardiac arrest) per year',
                                                "class" => "statistic_category"),
                                          array("data" => $accred_info->number_of_cardiac_calls ? $accred_info->number_of_cardiac_calls : "",
                                                "class" => "right"),
                                          array("data" => $data['cardiac'] ? $data['cardiac'] : 0,
                                                "class" => "right"));

        $statsTableData['body'][] = array(array("data" => 'Average number of shifts per student',
                                                "class" => "statistic_category"),
                                          array("data" => "N/a",
                                                "class" => "right"),
                                          array("data" => number_format($data['ShiftsPerStudent'], 2),
                                                "class" => "right"));

        $statsTableData['body'][] = array(array("data" => 'Average number of runs per shift for a student',
                                                "class" => "statistic_category"),
                                          array("data" => "N/a",
                                                "class" => "right"),
                                          array("data" => number_format($data['RunsPerShift'], 2),
                                                "class" => "right"));
        
        // the hours data is a separate query
        $hoursData = $repo->getFieldSiteHoursData($site->id, array_keys($students), $startDate, $endDate);
        // we just want the first row, since this is a subselect
        $hoursData = $hoursData[0];
        $statsTableData['body'][] = array(array("data" => 'Number of hours per shift',
                                                "class" => "statistic_category"),
                                          array("data" => "N/a",
                                                "class" => "right"),
                                          array("data" => number_format($hoursData['HoursPerShift'], 2),
                                                "class" => "right"));
        
        // add the table
        $this->data[] = array("type" => "table",
                              "content" => $statsTableData,
                              "options" => array("noSort" => true,
                                                 "noSearch" => true,
                                                 "noInfo" => true,
                                                 "tableClass" => "narrow",
                                                 "csvTitle" => "Site Statistics - " . $site->name),
                              );
    }
    
    
    /**
     * Return a short label/description of the report using report configuration
     * This label lists the sites
     */
    public function getShortConfigLabel()
    {
        // get the site name or # of sites
        $sitesLabel = '';
    
        // if we just chose ONE site type, say that
        if ($this->config['sites_filters'] == array("0-Field") ||
            $this->config['sites_filters'] == array("0-Clinical")) {
            return "All ".substr($this->config['sites_filters'][0], 2)." sites";
        }
        
        $sites = $this->getSiteIds();
        if (count($sites) > 1) {
            $sitesLabel = count($sites) . ' sites';
        } else {
            $site = \Fisdap\EntityUtils::getEntity('SiteLegacy', $sites[0]);
            $sitesLabel = $site->name;
        }
  
        // return the label
        return $sitesLabel;
    }
    
    public static function sortSitesByTypeName($a, $b)
    {
        if ($a->type == $b->type) {
            return ($a->name < $b->name ? -1 : 1);
        }

        return ($a->type < $b->type ? -1 : 1);
    }
}
