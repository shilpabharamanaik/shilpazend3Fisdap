<?php

use Illuminate\Queue\Capsule\Manager as Queue;


class Reports_IndexController extends Fisdap_Controller_Private
{
	/**
	 * @var Queue
	 */
	private $queue;

    /**
     * @var Zend_Cache_Core The Zend Cache backend to use for retrieving cached report data
     */
    private $cache;


	/**
	 * @param Queue           $queue
	 * @param Zend_Cache_Core $cache
	 */
	public function __construct(Queue $queue, Zend_Cache_Core $cache)
	{
		$this->cache = $cache;
		$this->queue = $queue;
	}


	public function legacyAction()
	{
		$this->redirect(Util_GetLegacyTopNavLinks::getLink(Util_GetLegacyTopNavLinks::EVAL_LIST, $this->view->serverUrl()));
	}
	
	public function splashAction()
	{
		$this->view->pageTitle = "Reports";
	}

	/**
	 * Index page displays an interface for navigating to various reports
	 */
	public function indexAction()
	{
		$this->checkPermissions();
		
		// List the reports available
		$program = $this->userContext->program;
		$this->view->pageTitle = "All Reports";
		$this->view->program = $program;
		$this->view->categories = $program->profession->report_categories;
		
		// get the reports for this user
		$reports = $program->getActiveReports();
		$visible_reports = array();
		foreach ($reports as $report) {
			$reportClass = 'Fisdap_Reports_' . $report->class;
            if (class_exists($reportClass) && $reportClass::hasPermission($this->userContext)) {
				$visible_reports[] = $report;
			}
		}
		$this->view->reports = $visible_reports;
		
		// stuff we need for plugins
		$this->view->headScript()->appendFile("/js/jquery.fieldtag.js");
		
		$this->view->tour = new Fisdap_View_Helper_GuidedTourHelper();
		$this->view->tour_id = ($this->user->getCurrentRoleName() == 'instructor') ? 11 : 12;
	}
	
	/**
	 * History page displays reports run recently by this user
	 */
	public function historyAction()
	{
		$this->checkPermissions();
		
		// List the reports available
		$user_context = $this->user->getCurrentUserContext();
		$program = $user_context->program;
		$this->view->program = $program;
		$this->view->pageTitle = "Report History";
		
		// stuff we need for search box
		$this->view->headScript()->appendFile("/js/jquery.fieldtag.js");

		// List the user's last 100 saved report configurations (report history)
		$savedConfigurations = \Fisdap\EntityUtils::getRepository('Report')->getRecentActiveConfigs($user_context->id, 100);
		
		// make an array of useful info about the saved configs
		$configList = array();
		foreach($savedConfigurations as $reportConfig) {
			$configData = $reportConfig->get_config();
			$report = $this->_loadReport($reportConfig->report->id, $configData);
			
			if (is_object($report)) {
				// add the config if the report exists
				$configList[$reportConfig->id]['reportClass'] = $reportConfig->report->class;
				$configList[$reportConfig->id]['title'] = $reportConfig->report->name;
				$configList[$reportConfig->id]['label'] = $report->getShortConfigLabel();
				$configList[$reportConfig->id]['updated'] = $reportConfig->updated->format('n/j/Y');

                // are results available in cache? Check for existence of the master document
                $cacheId = 'reports_result_for_config_' . $reportConfig->id;
                $cachedResults = $this->cache->load($cacheId);
                if ($this->doesCacheIndicateReportIsPending($cachedResults)) {
                    $configList[$reportConfig->id]['resultsStatus'] = 'pending';
                } else if ($cachedResults) {
                    $configList[$reportConfig->id]['resultsStatus'] = 'ready';
                } else {
                    $configList[$reportConfig->id]['resultsStatus'] = 'none';
                }
                unset($cachedResults);
			}
		}
		$this->view->savedConfigurations = $configList;
	}

	/**
	 * Display action displays the report form
	 */
	public function displayAction()
	{
		$this->view->headScript()->appendFile("/js/jquery.busyRobot.js");
		$this->view->headLink()->appendStylesheet("/css/jquery.busyRobot.css");
		
		$this->checkPermissions();
		
		// Identify the class name for the desired report and try to load
		$class_name = $this->_getParam('report');
		$report_entity = \Fisdap\Entity\Report::getByClass($class_name);
		$report_id = $report_entity->id;
		
		// See if we have a saved configuration to load via ID
		$savedConfig = $this->_getParam('config');
		if ($savedConfig) {
			// try to get the saved configuration
			$reportConfig = \Fisdap\EntityUtils::getEntity('ReportConfiguration', $savedConfig);
		}

		// do we have a pre-loaded report configuration?
		if ($reportConfig) {
			// make sure this config belongs to this user
			if ($this->user->getCurrentUserContext()->id != $reportConfig->user_context->id) {
				$this->displayError("You don't have permission to view this page.");
				return;
			}
			
			// make sure this config belongs to this report type
			if ($report_id != $reportConfig->report->id) {
				$this->displayError("You have reached this page in error.");
				return;
			}

			// if we made it here, we're good to go with this config
			$report = $this->_loadReport($report_id, $reportConfig->get_config());
		} else {
			$report = $this->_loadReport($report_id, array());
		}

		// if we don't have a report, bail
		if (!$report) {
			$this->redirect("/reports");
		}
		
		
		// Check permissions for this report
		$reportClass = 'Fisdap_Reports_' . $class_name;
		if (!$reportClass::hasPermission($this->userContext)) {
			$this->displayError("You don't have permission to view this page.");
			return;
		}
		
		// add JS
		$this->view->headScript()->appendFile("/js/reports/index/display.js");
		$this->view->headScript()->appendFile("/js/DataTables/media/js/jquery.dataTables.js");
		$this->view->headScript()->appendFile("/js/jquery.fieldtag.js");
		$this->view->headScript()->appendFile("/js/numeral.min.js");
		foreach ($report->scripts as $jsFile) {
			$this->view->headScript()->appendFile($jsFile);
		}
	
		// add CSS
		$this->view->headLink()->appendStylesheet('/js/DataTables/media/css/jquery.dataTables_themeroller.css');
		$this->view->headLink()->appendStylesheet('/css/forms.css');
		foreach ($report->styles as $cssFile) {
			$this->view->headLink()->appendStylesheet($cssFile);
		}

		
		// view properties
		$this->view->pageTitle = $report->title;
		$this->view->reportDescription = $report_entity->getDescription();
		$this->view->reportHeader = $report->header;
		$this->view->reportFooter = $report->footer;
		$this->view->standalone = $report_entity->standalone;
		
		$report->generateForm();
		$this->view->form = $report->renderForm(); // a structured array of form components
		$this->view->summary = $report->getSummary("table");

		// if we have a saved config, then load the report results as well
		if ($reportConfig) {
            // BOOM TIME LET'S DO IT BACKGROUND STYLE!
            $cacheId = 'reports_result_for_config_' . $reportConfig->id;
            $cachedValues = $this->_getCachedReportData($reportConfig->id);
            // If we actually have cached data, set those values within the report.
            if (is_array($cachedValues)) {
                $report->setValuesFromCache($cachedValues);
            } else {
                // If we have a configuration but no cached data (because it's an expired configuration
                // or we're directly linking to a new configuration), the report will be run again
                $report->data = FALSE;
            }

            // if we've been explicitly asked to rerun this data, clear out the cached data so it'll run again
            if ($this->_getParam('clearCache') && is_array($report->data) && !isset($report->data['placeholder'])) {
                // we need to clear the cached data so that the report will be regenerated
                $report->data = FALSE;
                $this->cache->remove($cacheId);
            }

            // if we have data, go ahead and render it and do not start the poller
            if ($report->data) {
                $this->view->reportContent = $report->renderReport();
                $this->view->startPoller = FALSE;
            } else {
                // run the report with the background workers!
                $this->view->startPoller = TRUE;
                $this->view->configurationId = $reportConfig->id;

                // store a cache placeholder so app knows a job has been started
                $this->cache->save(array('placeholder' => TRUE), $cacheId, array(), 0); //indefinite lifetime

                // Put the report job in the queue
                $data = array('configurationId' => $reportConfig->id);
                $this->queue->push('RunReport', $data);
            }
		}
	}
	
	/**
	 * Run action accepts POST data via AJAX and runs the selected test with that data
	 */
	public function runAction()
	{
		$request = $this->getRequest();
		if ($request->isPost()) {
			$config = $request->getPost();
			
			// route POST data to the particular report
			$report_id = $config['report_id'];
			$report = $this->_loadReport($report_id, $config);
	
			$errors = $report->validate();
			if (empty($errors)) {

				// looks like we successfully ran a report
				// let's save this to the history of saved report configurations
                if ($config['config_id'] > 0) {
                    $savedConfig = \Fisdap\EntityUtils::getEntityManager('ReportConfiguration', $config['config_id']);
                } else {
                    $savedConfig = new \Fisdap\Entity\ReportConfiguration();
                    $savedConfig->set_user_context($this->user->getCurrentUserContext());
                    $savedConfig->set_config($config);
                    $savedConfig->set_report($config['report_id']);
                    $savedConfig->save(TRUE); // flush so we have the ID of this entity
                }

                // BOOM TIME LET'S DO IT BACKGROUND STYLE!
                $cacheId = 'reports_result_for_config_' . $savedConfig->id;
                $cacheValues = $this->cache->load($cacheId);
                if ($this->isCacheDataReady($cacheValues)) {
                    $report->setValuesFromCache($cacheValues);
                }

                if ($report->data['placeholder']) {
                    // we have a placeholder, which means the job is in the queue and we're waiting on it
                } else {
                    // either we have prior data, or no placeholder, which means we need to queue a job
                    $data = array('configurationId' => $savedConfig->id);
                    $this->queue->push('RunReport', $data);
                    // store a cache placeholder so app knows a job has been started
                    $this->cache->save(array('placeholder' => TRUE), $cacheId, array(), 0); //indefinite lifetime

                    // show a temporary message
                    $this->_helper->json(array(
                        'html' => '<p>Your report is being generated!</p>',
                        'isError' => FALSE,
                        'configId' => $savedConfig->id));
                }

			} else {
				// there were errors with form validation
				$this->_helper->json(array('isError' => !$report->valid, 'errors' => $errors));
			}
		} else {
			// @todo handle error
		}
		
		
	}

    /**
     * Check an array of report configuration IDs (config_ids) and return any of those
     * for which report results have been stored in the cache
     * @throws Zend_Exception
     */
    public function checkCachedReportsAction() {
        $configIds = $this->getParam('config_ids');

        // check for presence of each in cache.
        $reportsReady = $reportsError = array();
        foreach($configIds as $configId) {
            $cacheId = 'reports_result_for_config_' . $configId;
            $cachedResults = $this->cache->load($cacheId);

            if ($this->isCacheDataReady($cachedResults)) {
                $reportsReady[] = $configId;
            }

            if ($cachedResults && isset($cachedResults['isError']) && $cachedResults['isError']) {
                $reportsError[] = $configId;
            }
        }

        // are we still waiting for some reports?
        if (count($reportsReady) < (count($configIds) - count($reportsError))) {
            $waiting = TRUE;
        } else {
            $waiting = FALSE;
        }

        // return array of reports for which we have cached results
        $this->_helper->json(array(
            'reportsReady' => $reportsReady,
            'reportsError' => $reportsError,
            'waiting' => $waiting,
            'isError' => FALSE
        ));
    }

    /**
     * Run action accepts POST data via AJAX and runs the selected test with that data
     */
    public function loadCachedReportAction()
    {
        $configData = \Fisdap\EntityUtils::getEntity('ReportConfiguration', $this->getParam('config_id'));
        if (!$configData instanceof \Fisdap\Entity\ReportConfiguration) {
            $this->_helper->json(
                array(
                    'isError' => TRUE,
                    'message' => 'Could not load configuration for ID submitted: ' . $this->getParam('config_id')
                ));
            return;
        }

        // construct the Report object
        $report_entity = $configData->report;
        $config = $configData->get_config();
        $reportClass = 'Fisdap_Reports_' . $report_entity->class;
        if (!class_exists($reportClass)) {
            $this->_helper->json(
                array(
                    'isError' => TRUE,
                    'message' => 'Could not find reportClass for config ID submitted: ' . $this->getParam('config_id')
                ));
            return;
        }
        $report = new $reportClass($report_entity, $config);

        // load data from cache
        $cachedData = $this->_getCachedReportData($configData->id);
        if (is_array($cachedData)) {
            $report->setValuesFromCache($cachedData);
        }

        if ($report->data && ($this->isCacheDataReady($report->data))) {
            // let's generate and return results
            $summary = $report->getSummary("table");
            $output = $report->renderReport();
            $navBar = $this->view->reportsNavBar("display");


            $this->_helper->json(array('summary' => $summary,
                'html' => $output,
                'isError' => FALSE,
                'waiting' => FALSE,
                'navBar' => $navBar));
        } else {
            $this->_helper->json(
                array(
                    'isError' => (isset($cachedData['isError'])) ? $cachedData['isError'] : FALSE,
                    'waiting' => (isset($cachedData['isError']) && $cachedData['isError']) ? FALSE : TRUE,
                    'message' => (isset($cachedData['isError']) && isset($cachedData['message'])) ? $cachedData['message'] : 'Report not yet ready (not found in cache).',
                ));
        }
    }

    /**
     * Load the cached data/results for a report based on a supplied configId from multiple cache documents
     * and assemble as a single array representing the cached report data
     * @param $configId integer ID of the report configuration
     * @return array Multidimensional array representing cached report data, keys: header, footer, data
     * @throws Zend_Exception
     */
    private function _getCachedReportData($configId) {
        $cacheId = 'reports_result_for_config_' . $configId;
        $mapDocument = $this->cache->load($cacheId);

        if (is_array($mapDocument) && isset($mapDocument['headerKey'], $mapDocument['footerKey'], $mapDocument['dataKeys'])) {
            // map document has the necessary map keys to retrieve the report results
            $headerCache = $this->cache->load($mapDocument['headerKey']);
            $footerCache = $this->cache->load($mapDocument['footerKey']);
            $cachedData = array(
                'header' => $headerCache['data'],
                'footer' => $footerCache['data']
            );
            foreach ($mapDocument['dataKeys'] as $key) {
                $dataDocument = $this->cache->load($key);
                $cachedData['data'][$dataDocument['key']] = $dataDocument['data'];
            }
        } else if ($this->isCacheDataReady($mapDocument) == FALSE) {
            // Map document is just a placeholder, return that
            $cachedData = $mapDocument;
        } else {
            // map document is ??!?!? so return an empty array
            $cachedData = array();
        }

        return $cachedData;
    }

	private function _loadReport($report_id, $config = array()) {
		
		if ($report_id == '') {
			// @todo handle error
			return FALSE;
		}
		
		$report = \Fisdap\EntityUtils::getEntity('Report', $report_id);
		$reportClass = 'Fisdap_Reports_' . $report->class;
		if (!class_exists($reportClass)) {
			// @todo handle error
			return FALSE;
		}

		return new $reportClass($report, $config);
	}
	
	public function updateAccreditationAccordionAction() {
		$sites_filters = $this->_getParam('sites_filters');
		
		$accordion = $this->view->accreditationInfoAccordion(array("sites_filters" => $sites_filters));
		
		$this->_helper->json($accordion);
	}
	
	public function generateAccreditationInfoAction()
	{
		$siteId = $this->_getParam("siteId");
		$site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $siteId);
		$form = new Account_Form_Accreditation($site);
		$this->_helper->json(array(
								   "form" => $form->__toString(),
								   "siteName" => $site->name,
								   "siteId" => $siteId));
	}
	
	// instructors with permission can view reports and students with skills tracker can view reports
	private function checkPermissions() {
		
		if (!$this->userContext->isInstructor() && !$this->userContext->getPrimarySerialNumber()->hasSkillsTracker()) {
			// if this is a student without skills tracker, they can't be here
			$this->displayError("You don't have permission to view this page.");
			return;
		} else if ($this->userContext->isInstructor() && !$this->userContext->hasPermission("View Reports")) {
			// if this is an instructor without reports permission, they can't be here
			$this->displayPermissionError("View Reports");
			return;
		}
	}
	
	public function createReportConfigAction()
	{
		$reportType = $this->_getParam("reportType");
		$report_entity = \Fisdap\Entity\Report::getByClass($reportType);
		$studentId = $this->_getParam("studentId");
		$student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $studentId);
        $goalSetId = $this->_getParam("goalSetId");
        $settings = $this->user->getCurrentUserContext()->getProgram()->getProgramSettings();

        // Filter the available shift types to only include ones the program has configured
        $shiftTypes = array();
        if($settings->include_lab_in_mygoals){
            $shiftTypes[] = 'lab';
        }

        if($settings->include_clinical_in_mygoals){
            $shiftTypes[] = 'clinical';
        }

        if($settings->include_field_in_mygoals){
            $shiftTypes[] = 'field';
        }

		// CHECK PERMISSIONS!!!
		if (!$this->user->isInstructor() && $this->user->getCurrentRoleData()->id != $studentId) {
			// if this is a student who doesn't match this student id
			$this->_helper->json(false);
			return;
		} else if ($this->user->isInstructor() && $this->user->getCurrentProgram()->id != $student->program->id) {
			// if this is an instructor from a different program
			$this->_helper->json(false);
			return;
		}


		
		// this is ghetto for now and if we end up linking to more reports directly, we may want
		// to write functions to grab the default config in real time
		// but for now this will do
		switch ($reportType) {
            case "AirwayManagement":
                if(!$goalSetId){
                    $selectedGoalset = $student->getGoalSet()->id;
                    $selectedGoalset = ($selectedGoalset > 0) ? $selectedGoalset : 1; //default to NSC if there's no program default
                }
                else {
                    $selectedGoalset = $goalSetId;
                }

                $config = array("sites_filters" => array (0 => '0-Clinical',
                                                          1 => '0-Field',
                                                          2 => '0-Lab',
                                                         ),
                                'startDate' => '',
                                'endDate' => '',
                                'selected-goalset' => $selectedGoalset,
                                'types' => array(),
                                'audit-status-filters' => 'on',
                                'auditStatus' => 'all',
                                'picklist_mode' => 'multiple',
                                'section' => 'Any group',
                                'graduationMonth' => '0',
                                'graduationYear' => '0',
                                'graduationStatus' => array(0 => '1'),
                                'student' => '',
                                'longLabel' => '',
                                'multistudent_picklist_selected' => $studentId,
                                'report_id' => $report_entity->id,
                                'airway_management_report_type' => 'detailed',
                                'include_observed_airway_managements' => 1
                          );
                break;
			case "Hours":
				$config = array('sites_filters' => array (0 => '0-Clinical',
														  1 => '0-Field',
														  2 => '0-Lab',
														  ),
								'startDate' => '',
								'endDate' => '',
								'display_format' => 'site-and-department',
								'hours_scheduled' => '1',
								'hours_locked' => '1',
								'hours_audited' => '1',
								'picklist_mode' => 'multiple',
								'section' => 'Any group',
								'graduationMonth' => '0',
								'graduationYear' => '0',
								'graduationStatus' => array(0 => '1'),
								'student' => '',
								'longLabel' => '',
								'multistudent_picklist_selected' => $studentId,
								'report_id' => $report_entity->id,
								);
				break;
			case "GraduationRequirements":
                if(!$goalSetId){
                    $selectedGoalset = $student->getGoalSet()->id;
                    $selectedGoalset = ($selectedGoalset > 0) ? $selectedGoalset : 1; //default to NSC if there's no program default
                }
                else {
                    $selectedGoalset = $goalSetId;
                }

				$config = array('selected-goalset' => $selectedGoalset,
								'startDate' => '',
								'endDate' => '',
                                'display_format' => 'site-and-department',
                                'patient_filters' => $settings->getSubjectTypesInMygoals(),
                                'sites_filters' => $shiftTypes,
								'audit-status-filters' => 'on',
								'auditStatus' => 'all',
								'picklist_mode' => 'multiple',
								'section' => 'Any group',
								'graduationMonth' => '0',
								'graduationYear' => '0',
								'graduationStatus' => array(0 => '1'),
								'student' => '',
								'longLabel' => '',
								'multistudent_picklist_selected' => $studentId,
								'report_id' => $report_entity->id,
								);
				break;
			case "Attendance":
				$enddate =  date('m/d/Y');
				$config = array('startDate' => '',
								'endDate' => $enddate,
								'display_format' => 'site-and-department',
								'reportType' => 'detailed',
								'picklist_mode' => 'single',
								'section' => 'Any group',
								'graduationMonth' => '0',
								'graduationYear' => '0',
								'graduationStatus' => array(0 => '1'),
								'student' => $studentId,
								'longLabel' => '',
								'multistudent_picklist_selected' => '',
								'report_id' => $report_entity->id,
								);
				break;
			case "Skills":
				$config = array('startDate' => '',
								'endDate' => '',
								'patient_filters' => array (0 => '1'),
								'section' => 'Any group',
								'graduationMonth' => '0',
								'graduationYear' => '0',
								'graduationStatus' => array(0 => '1'),
								'student' => $studentId,
								'longLabel' => '',
								'report_id' => $report_entity->id,
								);
				break;
			case "LabPracticeGoals":
				$config = array('certLevel' => $student->getCertification()->id,
								'dateRange' => array ('startDate' => '',
													  'endDate' => ''),
								'reportType' => 'detailed',
								'picklist_mode' => 'single',
								'certificationLevels' => array(0 => $student->getCertification()->id),
								'section' => 'Any group',
								'graduationMonth' => '0',
								'graduationYear' => '0',
								'graduationStatus' => array(0 => '1'),
								'student' => $studentId,
								'longLabel' => '',
								'multistudent_picklist_selected' => '',
								'report_id' => $report_entity->id,
								);
				break;
		}
		
		// let's save this to the history of saved report configurations
		$savedConfig = new \Fisdap\Entity\ReportConfiguration();
		$savedConfig->set_user_context($this->user->getCurrentUserContext());
		$savedConfig->set_config($config);
		$savedConfig->set_report($report_entity->id);
		$savedConfig->save();
		
		$this->_helper->json($savedConfig->id);
	}
	
	public function reportDisplayErrorAction()
	{
		$subject = "Reports bug submission";
		$server_url_root = Util_HandyServerUtils::getCurrentServerRoot();
		$email = "support@fisdap.net";
		
		$userAgent = new Zend_Http_UserAgent();
		$device = $userAgent->getDevice();
		$browser = $device->getBrowser();
		$browser .= " " . $device->getBrowserVersion();
		
		$production = false;
		if (strpos($server_url_root, 'fisdapdev') !== FALSE){
			$production = false;
			$subject .= " ---- from DEV server ----";
			$email = array("mmayne@fisdap.net", "rwalberg@fisdap.net");
		} else if(strpos($server_url_root, 'fisdapqa') !== FALSE) {
			// we're on dev or qa
			$production = false;
			$subject .= " ---- from QA server ----";
			$email = array("mmayne@fisdap.net", "rwalberg@fisdap.net");
		}
		else {
			// we're on production
			$production = true;
		}
		
		$params = $this->getRequest()->getPost();
		$config = $params;
		
		$mail = new \Fisdap_TemplateMailer();
		$mail->addTo($email)
			 ->setSubject($subject)
			 ->setViewParam('prod', $production)
			 ->setViewParam('config', $config)
			 ->setViewParam('browser', $browser)
			 ->setViewParam('user', $this->user)
			 ->sendHtmlTemplate('reports-bug-report.phtml');
			 
		$this->_helper->json($mail->getHtmlTemplateBody('reports-bug-report.phtml'));
	}

    /**
     * Is the cached report data ready to display?
     * @param $cachedResults
     * @return bool
     */
    private function isCacheDataReady($cachedResults)
    {
        return $cachedResults && (!isset($cachedResults['placeholder']) || $cachedResults['placeholder'] == FALSE);
    }

    /**
     * Is the cached report data in a state that indicates the report is in progress (results pending)?
     * If $cachedResults is FALSE, then cache probably just expired (no value found in cache)
     *
     * @param $cachedResults
     * @return bool
     */
    private function doesCacheIndicateReportIsPending($cachedResults) {
        return $cachedResults && isset($cachedResults['placeholder']) && $cachedResults['placeholder'];
    }

}

