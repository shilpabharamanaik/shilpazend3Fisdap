<?php
/**
 * Class Fisdap_Reports_GraduationRequirements
 * This is the Graduation Requirements Report class
 * Refer to Fisdap_Reports_Report for more documentation
 */
class Fisdap_Reports_TransitionCourse extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    // NOTE: Below we override some settings on the student picker, so this is not really a normal instance of the MSP
    public $formComponents = array(
        'multistudentPicklist' => array(
            'title' => 'Select one or more student(s)',
            'options' =>  array(
                'mode' => 'multiple',
                'loadJSCSS' => TRUE,
                'loadStudents' => TRUE,
                'showTotal' => TRUE,
				'studentVersion' => TRUE
            ),
        )
	);

    public $scripts = array("/js/library/Fisdap/Reports/transition-course.js");

	// Overriding the default constructor so we can initialize the multistudentPickList options dynamically before load
	public function __construct($report, $config = array()) {
		$this->initPicklistOptions();
		parent::__construct($report, $config);
	}
	
	/**
	 * This function finds all students who have a transition course product for this program
	 * and makes the multistudentPicklist show those as selectable.
	 */
	private function initPicklistOptions(){
		// Get a list of all students with the transition course property.
		$studentRepo = \Fisdap\EntityUtils::getRepository('StudentLegacy');
		$productRepo = \Fisdap\EntityUtils::getRepository('Product');
		
		// Get the 3 transition course products...
		$expr = \Doctrine\Common\Collections\Criteria::expr();
		$criteria = \Doctrine\Common\Collections\Criteria::create();
		$criteria->where($expr->in('id', array(13, 14, 15)));
		$products = $productRepo->matching($criteria);
		
		$config = 0;
		
		foreach($products as $p){
			$config = $config | $p->configuration;
		}
		
		$studentIds = $studentRepo->getStudentIdsByProductCodeConfig($config);
		
		$this->formComponents['multistudentPicklist']['options']['selectableStudentIds'] = $studentIds;
	}
	
    /**
     * Run a query and any processing logic that produces the data contained in the report
     * Return a multidimensional array and it will be rendered as tables
     * OR return a string and it will be rendered as HTML
     * @return array
     */
    public function runReport() {
    	$trans = $this->getTransitionCompletions();
    	
    	$reportTable = array(
    		'head' => array(
				'000' => array( // First row headers...
    				'Name',
    				'Certification Level',
    				'Course Completed',
    				'Completed Date',
    			)
    		),
    		'body' => array()
    	);
    	
    	$bodyRows = array();

    	foreach($trans as $tData){
    		foreach($tData['transitions'] as $t){
    			$bodyRow = array();
    			$bodyRow['name'] = $tData['name'];
    			$bodyRow['cert'] = $tData['cert'];
    			
    			$bodyRow['course'] = $t['coursename'];
    			$bodyRow['date'] = date('Y-m-d H:i:s', $t['timecreated']);
    			
    			$reportTable['body'][] = $bodyRow;
    		}
    	}
    	
    	$this->data['transition_report'] = array("type" => "table", "content" => $reportTable);		
    }
	
    public function getTransitionCompletions(){
    	$students = $this->getMultiStudentData(true);
    	
    	$usernames = array();
    	$certificationLevels = array();
    	
    	foreach($students as $sid => $nameOptions){
    		$std = \Fisdap\EntityUtils::getEntity('StudentLegacy', $sid);
    		$usernames[$sid] = $std->username;
    		$certificationLevels[$sid] = $std->getCertification(true);
    	}
    	
    	// Get the transition completions for these users
    	$trans = \Fisdap\MoodleUtils::getTransitionCourseCompletionsByUsernames($usernames);
    	
    	// Remap the transition completions so they're indexed by username...
    	$remapTrans = array();
    	foreach($trans as $t){
    		$remapTrans[$t['username']][] = $t;
    	}
    	
    	// Resort the data here so it's more readily usables
    	$flatTrans = array();
    	foreach($students as $sid => $nameOptions){
    		$username = strtolower($usernames[$sid]);
    		
    		$flatTransRow = array();
    		$flatTransRow['name'] = $nameOptions['first_last_combined'];
    		$flatTransRow['cert'] = $certificationLevels[$sid];
    		$flatTransRow['transitions'] = array();
    		
    		if(array_key_exists($username, $remapTrans)){
	    		foreach($remapTrans[$username] as $st){
	    			$flatTransRow['transitions'][] = $st;
	    		}
    		}
	    		
    		$flatTrans[] = $flatTransRow;
    	}
    	
    	return $flatTrans;
    }
    
    // No real need for validation- the MSP handles it.
	public function transitionCourseValidate($info) {}

	// Only allow instructors to view this report.
	public static function hasPermission($userContext) {
		return $userContext->isInstructor();
	}

}
