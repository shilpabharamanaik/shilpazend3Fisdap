<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Fisdap\EntityUtils;
use Fisdap\MoodleUtils;


/**
 * Entity class for the legacy MoodleTestData table.
 *
 * @Entity(repositoryClass="Fisdap\Data\MoodleTest\DoctrineMoodleTestDataLegacyRepository")
 * @Table(name="MoodleTestData")
 */
class MoodleTestDataLegacy extends EntityBaseClass
{
	/**
	 * @Column(name="TestData_id", type="integer")
	 * @GeneratedValue
	 * Note that this is NOT given the Doctrine "(at)Id" annotation. See Jesse's 8/17/12 email on the issue
	 */
	protected $id;
	
	/**
     * @ManyToOne(targetEntity="TestBlueprintsLegacy")
	 * @JoinColumn(name="Blueprint_id", referencedColumnName="tbp_id")
	 */
	protected $blueprint;
    
    /**
	 * @Id
	 * @Column(name="MoodleQuiz_id", type="integer")
	 * THIS is the Doctrine "id" field for the entity.  See Jesse's 8/17/12 email on the issue
	 */
	protected $moodle_quiz_id;
    
	/**
	 * @Column(name="TestName", type="string")
	 */
	protected $test_name;
    
	/**
	 * @Column(name="Active", type="integer")
	 */
	protected $active;
    
	/**
	 * @Column(name="MoodleCourse_id", type="integer")
	 */
	protected $moodle_course_id;
    
	/**
	 * @Column(name="ShowDetails", type="integer")
	 */
	protected $show_details;
    
	/**
	 * @Column(name="MoodleDatabase", type="string")
	 */
	protected $moodle_database;
    
	/**
	 * @Column(name="MoodlePrefix", type="string")
	 */
	protected $moodle_prefix;
    
	/**
	 * @Column(name="PublishScores", type="integer")
	 */
	protected $publish_scores;
    
	/**
	 * @Column(name="MoodleIDMod", type="integer")
	 */
	protected $moodle_id_modifier;
	
	/**
	 * @Column(name="CertLevel", type="string")
	 */
	protected $cert_level;
        
        /**
         * @Column(name="HelpText", type="string")
         * Optional help text to display along with the test score retrieval report
         * Default is null, which means no help text
         */
        protected $help_text;
        
        /**
	 * @Column(name="ShowTotals", type="integer")
	 * Should score totals for each student be shown (boolean)
	 */
	protected $show_totals;

    /**
     * @Column(name="national_stats_last_generated", type="datetime", nullable=true)
     * The time and date when national stats were last generated for this test
     * and stored in cache.
     */
    protected $national_stats_last_generated;

    /**
	 * @var \Doctrine\Common\Collections\ArrayCollection
     * @ManyToMany(targetEntity="Product", mappedBy="moodle_quizzes")
     */
    protected $products;
	
	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
     * @OneToMany(targetEntity="MoodleTestDocument", mappedBy="test")
     */
    protected $documents;

    /**
     * @var Array Only generated as necessary, so usually this will be empty
     * Holding place for ItemIDSectionMap Array
     */
    public $itemIDSectionMap = array();

    /**
     * @var Array Only generated as necessary, so usually this will be empty
     * Holding place for the Moodle Question IDs associated with this Moodle Quiz
     */
    public $moodleQuestionIds = array();

    /**
     * @var Array Only generated as necessary, so usually this will be empty
     * Holding place for the Moodle distractors (keyed by Moodle Question ID) associated with this Moodle Quiz
     */
    public $moodleDistractors = array();

    /**
     * @var Object Only generated as necessary, so usually this will be NULL
     * Zend DB Connection to the relevant moodle database.
     */
    private $moodleDBConnection = NULL;

    /**
     * @var Number Minimum grade % to filter when querying question responses via $this->getQuestionAnswersData()
     */
    public $minimumGradePercentageWhenQueryingAllResponses = .3;

    /**
	 * Getters
	 */
	public function get_test_name() {
		return $this->test_name;
	}

    public function get_moodleQuestionIds() {
        if (!is_array($this->moodleQuestionIds) || empty($this->moodleQuestionIds)) {
            $this->itemIDSectionMap = EntityUtils::getRepository('MoodleTestDataLegacy')->getItemIDSectionMapArray($this->moodle_quiz_id);
            if (empty($this->itemIDSectionMap)) {
                // without an item array, we cannot return any score records. :(
                throw new \Exception('Did not get an Item Array for this quiz, so cannot obtain list of Moodle Question IDs');
            } else {
                $this->moodleQuestionIds = array_keys($this->itemIDSectionMap['questions']);
            }
        }

        return $this->moodleQuestionIds;
    }

    public function get_moodleDistractors() {
        if (empty($this->moodleDistractors)) {
            $dbConnection = $this->get_moodleDBConnection(TRUE);
            $moodleQuestionIdString = implode(',', $this->get_moodleQuestionIds());
            $questionOptionQuery = "SELECT
            qa.question,
            qa.answer,
            qa.fraction,
            qa.id AS answer_id
            FROM
            {$this->moodle_prefix}_question_answers qa
            WHERE
            qa.question IN ({$moodleQuestionIdString})
            ORDER BY qa.id ASC";
            $questionOptions = $dbConnection->fetchAll($questionOptionQuery);
            $distractors = $distractorIds = array();
            $distractorCodeMap = array(
                0 => 'A', 1 => 'B', 2 => 'C', 3 => 'D', 4 => 'E', 5 => 'F', 6 => 'G', 7 => 'H', 8 => 'I', 9 => 'J', 10 => 'K',
                11 => 'L', 12 => 'M', 13 => 'N', 14 => 'O', 15 => 'P', 16 => 'Q', 17 => 'R', 18 => 'S', 19 => 'T', 20 => 'U',
                21 => 'V', 22 => 'W', 23 => 'X', 24 => 'Y', 25 => 'Z'
            );
            foreach($questionOptions as $option) {
                // Inject a generic code (A/B/C/D) for the selected distractor into the results
                if(isset($this->moodleDistractors[$option['question']])) {
                    $code = $distractorCodeMap[count($this->moodleDistractors[$option['question']])];
                } else {
                    $code = $distractorCodeMap[0];
                }
                $this->moodleDistractors[$option['question']][$option['answer_id']] = array(
                    'distractorMoodleId' => $option['answer_id'],
                    'distractorCode' => $code,
                    'distractor' => $option['answer'],
                    'distractorFraction' => $option['fraction'], // fraction of grade earned
                );
            }
        }

        return $this->moodleDistractors;
    }

    private function get_moodleDBConnection($readOnly = FALSE) {
        // load a new databaes connection. We're not re-using the moodle connection
        // because there is some weirdness when running a background process
        // but this is a little wasteful... ugh. -jesse
        $context = $this->getContext();
        $this->moodleDBConnection = MoodleUtils::getConnection($context, $readOnly);
        return $this->moodleDBConnection;
    }
	
	/**
	 * Given a context, return the moodle table prefix for that database
	 */
	public static function getPrefixByContext($context) {
		switch ($context) {
			case "secure_testing":
				return "fismdl";
			case "study_tools":
				return "revmdl";
			case "pilot_testing":
				return "ptmdl";
            case "transition_course":
				return "transmdl";
            case 'preceptor_training':
            case 'clinical_educator_training': //@todo this 'string' is deprecated
            	return "pcmdl";
		}
	}
 	
	/**
	 * Given a context, return the moodle table prefix for that database
	 */
	public static function getModifier($context) {
		switch ($context) {
			case "secure_testing":
				return 0;
			case "study_tools":
				return 20000;
			case "pilot_testing":
				return 10000;
			case 'clinical_educator_training':
				return 0;
		}
	}
	
	/**
	 * Get the real Moodle Quiz ID (as stored in Moodle DB) for this test, using the modifier
	 */
	public function getRealMoodleQuizId() {
		$context = $this->getContext();
		$modifier = $this::getModifier($context);
		
		return ($this->moodle_quiz_id - $modifier);
	}
    
	public function getContext(){
		$contexts = array(
			'fismdl' => 'secure_testing', 
			'revmdl' => 'study_tools', 
			'ptmdl' => 'pilot_testing',
			'pcmdl' => 'clinical_educator_training'
		);
		
		return $contexts[$this->moodle_prefix];
	}
	
	/**
	 * This function takes an array of student usernames and fetches back matching results for this specific test.
	 * 
	 * The $options array can be used to send in extra information and filters for the results.
	 * 
	 * @param Array $studentUsernames containing the names of students to fetch results for.
	 * @param Array $options Typically a post array containing the following optional indices:
	 * 		'start_date' - Determines the minimum date for the result set
	 * 		'end_state'  - Determines the maximum date for the result set
	 */
	public function getScoreRecords($studentUsernames, $options=array()){
		$itemArray = EntityUtils::getRepository('MoodleTestDataLegacy')->getItemIDSectionMapArray($this->moodle_quiz_id);
		if (empty($itemArray)) {
			// without an item array, we cannot return any score records. :(
			return FALSE;
		}

		// Now, get results for the quiz.
		$results = $this->getStudentAttempts($studentUsernames, $itemArray, $options);
		
		$flatDataArray = array('results' => array(), 'groupData' => array());
		
		$rollupCounts = array();
		
		$seenStudents = array();
		
		// This array is a bit messy now- flatten it out and calculate up scores for now.
		foreach($results as $studentId => $data){
                    foreach($data['attempt_data'] as $attemptNum => $questions){
                        $flatAtom = array();
                        
                        $flatAtom['student_id'] = array_search($studentId, $studentUsernames);
                        $seenStudents[$studentId]++;
                        
                        $flatAtom['first_name'] = $data['student_data']['first_name'];
                        $flatAtom['last_name'] = $data['student_data']['last_name'];

                        $sections = array();

                        foreach($questions as $questionId => $grade){
                            
                            $flatAtom['attempt_id'] = $data['attempt_ids'][$attemptNum];
                            $flatAtom['attempt_number'] = $attemptNum;
                            
                            $section = $sections[$itemArray['questions'][$questionId]] = $itemArray['questions'][$questionId];

                            $flatAtom[$section] += $grade;
                            
                            if($grade == 0){
                                    // Need to pull out the ItemMoodleMap entry here to get the FISDAP ItemID...  Natch.
                                    $imm = EntityUtils::getRepository('ItemMoodleMapLegacy')->findOneBy(array('moodle_id' => $questionId, 'moodle_quiz_id' => ($this->moodle_quiz_id - $this->moodle_id_modifier), 'moodle_db_prefix' => $this->moodle_prefix));
                                    $flatDataArray['groupData'][$section][$imm->item_id][] = $flatAtom['student_id'];
                            }
                        }



                        foreach($sections as $section) {
                            if (isset($itemArray['sectionOptions'][$section]['grade_scale'])) {
                                if ($itemArray['sectionOptions'][$section]['grade_scale']) {
                                    $flatAtom[$section] = $this->transformScoreToScaled($flatAtom[$section], $itemArray['sectionOptions'][$section]['grade_scale']);
                                }
                            }
                        }


                        $flatDataArray['results'][] = $flatAtom;
                    }
		}
		
		$flatDataArray['groupData']['group_id'] = md5($this->id . time());
		$flatDataArray['groupData']['test_id'] = $this->moodle_quiz_id;
		$flatDataArray['groupData']['date'] = '';
		$flatDataArray['groupData']['student_count'] = count($seenStudents);

		return $flatDataArray;
	}


    /**
     * Build and execute query that pulls raw question answer data from Moodle for this quiz
     * based on the input of $userPoolType and $userPool Criteria as described in
     * \Fisdap\Reports\TestItemAnalysis->getQuestionResponses() doc
     *
     * @param string $userPoolType
     * @param array $userPoolCriteria
     * @param array $options
     * @return \Zend_Db_Statement Zend DB Statement object, which can be used to fetch data from the DB
     * @throws \Exception
     */
    public function getQuestionAnswersDataQuery($userPoolType = 'byUsername', $userPoolCriteria = array(), $options = array()) {
        // how should we select our pool of users?
        $poolCriteria = '';
        switch($userPoolType) {
            case 'all':
                break;
            case 'byProgram':
                if (isset($userPoolCriteria) && !empty($userPoolCriteria)) {
                    // get all the usernames for the supplied program IDs
                    $usernames = array();
                    $userRepo = EntityUtils::getRepository('User');
                    foreach($userPoolCriteria as $programId) {
                        $usernames += $userRepo->findUsers($programId, "", NULL, array('student'), array('u.username'));
                    }

                    // Load valid moodle user accounts based on the usernames supplied
                    $moodleUsers = MoodleUtils::getMoodleUserIds($usernames, $this->getContext(), $this->get_moodleDBConnection(TRUE));
                    if (empty($moodleUsers)) {
                        throw new \Exception('Cannot run Moodle quiz data query: no valid Moodle users found for the supplied usernames.');
                    }
                    $moodleUserIdString = implode(',', array_filter($moodleUsers));
                    $poolCriteria = "AND moodle_user.id IN ({$moodleUserIdString})";
                } else {
                    throw new \Exception('Cannot run Moodle quiz data query: byProgram selected and no programID provided');
                }
                break;
            default:
            case 'byUsername':
                if (isset($userPoolCriteria) && !empty($userPoolCriteria)) {
                    // Load valid moodle user accounts based on the usernames supplied
                    $moodleUsers = MoodleUtils::getMoodleUserIds($userPoolCriteria, $this->getContext(), $this->get_moodleDBConnection(TRUE));
                    if (empty($moodleUsers)) {
                        throw new \Exception('Cannot run Moodle quiz data query: no valid Moodle users found for the supplied usernames.');
                    }
                    $moodleUserIdString = implode(',', array_filter($moodleUsers));
                    $poolCriteria = "AND moodle_user.id IN ({$moodleUserIdString})";
                } else {
                    throw new \Exception('Cannot run Moodle quiz data query: byUsername selected and no usernames provided');
                }
                break;
        }
        // Load exam item array so we can identify the moodle questions for which we're getting responses
        $moodleQuestionIdString = implode(',', $this->get_moodleQuestionIds());

        // use the minimum grade % set in the class to filter out attempts below that grade
        // this excludes specious data (fake attempts, test attempts, etc.)
        $minimumGrade = floatval($this->minimumGradePercentageWhenQueryingAllResponses);
        $poolCriteria .= " AND (attempt.sumgrades / quiz.sumgrades) >= {$minimumGrade}";


        // handle date range filtering, if set
        $filterCriteria = '';
        if (isset($options['start_date'])) {
            // attempt must be finished greater than or equal to midnight on the date specified
            $filterCriteria .= "AND attempt.timefinish >= " . strtotime($options['start_date']);
        }
        if (isset($options['end_date'])) {
            // attempt must be finished less than or equal to end of the day specified (add 24 hrs worth of seconds)
            $filterCriteria .= " AND attempt.timefinish <= " . (strtotime($options['end_date']) + (60*60*24));
        }

        // handle attempt # filtering
        if (isset($options['includeAttempts'])) {
            // if set to all, we don't need to add filter language
            if ($options['includeAttempts'] != 'all' && $options['includeAttempts'] > 0) {
                $filterCriteria .= " AND attempt.attempt =" . intval($options['includeAttempts']);
            }
        }

        // do we need to group by username or attemptid? why question.id?
        $rawanswersquery = <<<EOT
SELECT
    moodle_user.username,
    question.id AS question_id,
    IF(COALESCE( (qa.maxmark * qas.fraction), 0 ) > 0, 'correct', 'wrong') AS answer_state,
    qas.fraction,
    qa.maxmark,
    qa.id AS question_attempt_id,
    attempt.id AS quiz_attempt_id,
    qa.responsesummary as response_summary,
    attempt.timestart
FROM
    {$this->moodle_prefix}_question_attempts AS qa
    INNER JOIN {$this->moodle_prefix}_quiz_attempts attempt ON attempt.uniqueid = qa.questionusageid
    INNER JOIN {$this->moodle_prefix}_quiz quiz ON attempt.quiz = quiz.id
    INNER JOIN {$this->moodle_prefix}_user moodle_user ON moodle_user.id = attempt.userid
    INNER JOIN {$this->moodle_prefix}_question question ON question.id = qa.questionid
    INNER JOIN {$this->moodle_prefix}_question_attempt_steps AS qas ON qas.id = (
        SELECT
            MAX(summarks_qas.id)
        FROM
            {$this->moodle_prefix}_question_attempt_steps AS summarks_qas
        WHERE
            summarks_qas.questionattemptid = qa.id
        )
WHERE
    qa.questionid IN ({$moodleQuestionIdString})
    {$poolCriteria}
    {$filterCriteria}
EOT;
        // log this query language for debug purposes
        //$log = \Zend_Registry::get('logger');
        //$log->debug('getQuestionAnswersDataQuery for ' . $userPoolType . ' on test ' . $this->get_test_name() . ': ' . $rawanswersquery);

        $dbConnection = $this->get_moodleDBConnection(TRUE);
        $statement = $dbConnection->query($rawanswersquery);

        return array('statement' => $statement, 'query' => $rawanswersquery);
    }


	/**
	 * Get the question grade map. Runs queries in read-only mode to the MySQL slave server
	 */
	private function getQuestionGradeMap($itemIDs){
		$moodleConnection = MoodleUtils::getConnection($this->getContext(), TRUE); // run in read-only mode
		
		$itemIDStr = implode(',', $itemIDs);
		
		$query = "
			SELECT
				question, grade
			FROM
				" . $this->moodle_prefix . "_quiz_question_instances
			WHERE
				quiz = {$this->getRealMoodleQuizId()}
				AND question IN ({$itemIDStr})
		";
		
		$gradeMap = $moodleConnection->fetchAll($query);
		
		$cleanGrades = array();
		
		// Rework the grade map so the index is the ID of the question, the value at that
		// index is the grade weight
		foreach($gradeMap as $gradeNode){
			$cleanGrades[$gradeNode['question']] = $gradeNode['grade'];
		}
		
		return $cleanGrades;
	}
	
	/**
	 * Get student attempts for this test. Runs in read-only mode from the MySQL slave server.
	 */
	public function getStudentAttempts($studentUsernames, $questionGradeMap, $options=array()){
		$moodleConnection = MoodleUtils::getConnection($this->getContext(), TRUE); // get read-only connection
		
		$rawQuestionIds = array_keys($questionGradeMap['questions']);
		
		$questionIdString = implode(',', $rawQuestionIds);
		
		$gradeMap = $this->getQuestionGradeMap($rawQuestionIds);
		
		$pre = self::getPrefixByContext($this->getContext());
		
		$studentAttemptsArray = array();
		
		$dateWhereClause = "";
		
		$startTimeInt = strtotime($options['start_date']);
                $endTimeInt =  strtotime($options['end_date']);
                // Make the end time + 24hours and -1s.  This makes it so that any test taken during that day should be caught
		$augmentedEndTimeInt = $endTimeInt + (24 * 60 * 60) - 1;
		
		if ($startTimeInt > 0) {
		    $dateWhereClause .= " AND qa.timestart >= '{$startTimeInt}' ";
		}
		if ($endTimeInt > 0) {
		    $dateWhereClause .= " AND qa.timefinish <= '{$augmentedEndTimeInt}' ";
		}
		
		foreach($studentUsernames as $studentUsername){
			// Get attempt IDs for this student username...
			$attemptsQuery = "
				SELECT 
					qa.uniqueid AS attempt_id,
					qa.attempt as attempt_number,
					u.firstname,
					u.lastname,
					u.username
				FROM
					{$pre}_quiz_attempts qa
					INNER JOIN {$pre}_user u ON qa.userid = u.id
				WHERE
					qa.quiz = {$this->getRealMoodleQuizId()}
					AND u.username = '{$studentUsername}'
					{$dateWhereClause}
			";
			
			$attemptsRes = $moodleConnection->fetchAll($attemptsQuery);
			
			foreach($attemptsRes as $attemptRow){
				$scoreQuery = "
					SELECT
						qa.questionid,
						qa.maxmark,
						COALESCE( (qa.maxmark * qas.fraction), 0 ) AS grade
					FROM
						{$pre}_question_attempts AS qa
						JOIN {$pre}_question_attempt_steps AS qas ON qas.id = (
							SELECT
								MAX(summarks_qas.id)
							FROM
								{$pre}_question_attempt_steps AS summarks_qas
							WHERE
								summarks_qas.questionattemptid = qa.id)
					WHERE
						qa.questionusageid = {$attemptRow['attempt_id']}
						AND qa.questionid IN ({$questionIdString})
				";
				
				$scoreRes = $moodleConnection->fetchAll($scoreQuery);
				
				foreach($scoreRes as $scoreRow){
					$studentAttemptsArray[$studentUsername]['student_data']['first_name'] = $attemptRow['firstname'];
					$studentAttemptsArray[$studentUsername]['student_data']['last_name'] = $attemptRow['lastname'];
				
					$studentAttemptsArray[$studentUsername]['attempt_data'][$attemptRow['attempt_number']][$scoreRow['questionid']] = $scoreRow['grade'];
				
					$studentAttemptsArray[$studentUsername]['attempt_ids'][$attemptRow['attempt_number']] = $attemptRow['attempt_id'];
				}
			}
			
/*
			$qry = "
				SELECT
					qa.id AS attempt_id, 
					qa.attempt as attempt_number,
					u.firstname,
					u.lastname,
					u.username,
					qs.question, 
					qs.grade
				FROM
					{$pre}_question_states qs
					INNER JOIN {$pre}_quiz_attempts qa ON qa.id = qs.attempt
					INNER JOIN {$pre}_user u ON qa.userid = u.id
				WHERE
					qa.quiz = {$this->getRealMoodleQuizId()}
					AND qs.question IN ({$questionIdString})
					AND u.username = '$studentUsername'
					{$dateWhereClause}
				ORDER BY 
					u.lastname DESC,
					u.firstname DESC,
					attempt_number ASC
			";
			
			$res = $moodleConnection->fetchAll($qry);

			foreach($res as $row){
				$studentAttemptsArray[$studentUsername]['student_data']['first_name'] = $row['firstname'];
				$studentAttemptsArray[$studentUsername]['student_data']['last_name'] = $row['lastname'];
				
				$studentAttemptsArray[$studentUsername]['attempt_data'][$row['attempt_number']][$row['question']] = $row['grade'];
				
				$studentAttemptsArray[$studentUsername]['attempt_ids'][$row['attempt_number']] = $row['attempt_id'];
		       }
*/			
		}
		
		return $studentAttemptsArray;
	}

        /**
         * Transform a quiz score reported by Moodle into a custom Fisdap-scaled grade
         */
        function transformScoreToScaled($score, $gradeScale) {
            switch($gradeScale) {
                // Entrance exam: these scores are scaled on a defined array of transformations
                case 'eep_anatomy_physiology':
                case 'eep_math':
                case 'eep_reading':
                case 'eep_emt':
                case 'emtea_gse':
                    $maps = $this->getEntranceExamGradeMaps();
                    $scaledScore = $maps[$gradeScale][$score];
                    break;
                
                // Entrance exam: this score is scaled by a defined formula for Agreeableness
                case 'eep_agreeableness':
                    $scaledScore = round(100 * (round(((($score-30.72)/5.99)*9.5)+50,0) / 65));
                    break;
                
                // Entrance exam: this score is scaled by a defined formula for Conscientiousness
                case 'eep_conscientiousness':
                    $scaledScore = round(100 * ((round(((($score-30.07)/6.37)*9.5)+50,0) - 4) / 61));
                    break;
                
                // Entrance exam: this score is scaled by a defined formula for Neuroticism
                case 'eep_neuroticism':
                    $scaledScore = round(100 * ((round(((($score-8.72)/5.84)*9.5)+50,0) - 35) / 56));
                    break;
            }
            
            return $scaledScore;
        }
        
	/**
	 * Entrance Exam: Provide manual grade translation/lookup maps for different exam sections
	 * because the Entrance Exam uses a carefully-calculated and irregular scaling curve
	 */
	function getEntranceExamGradeMaps() {
	  $maps = array();

	  $maps['eep_anatomy_physiology'] = array(
					      0 => 60,
					      1 => 60,
					      2 => 60,
					      3 => 60,
					      4 => 61,
					      5 => 61,
					      6 => 61,
					      7 => 61,
					      8 => 62,
					      9 => 62,
					      10 => 62,
					      11 => 62,
					      12 => 62,
					      13 => 62,
					      14 => 62,
					      15 => 62,
					      16 => 64,
					      17 => 65,
					      18 => 66,
					      19 => 67,
					      20 => 68,
					      21 => 70,
					      22 => 73,
					      23 => 75,
					      24 => 77,
					      25 => 80,
					      26 => 83,
					      27 => 85,
					      28 => 87,
					      29 => 89,
					      30 => 90
					      );
	  $maps['eep_math'] = array(
				0 => 60,
				1 => 60,
				2 => 60,
				3 => 60,
				4 => 61,
				5 => 61,
				6 => 62,
				7 => 62,
				8 => 65,
				9 => 66,
				10 => 68,
				11 => 70,
				12 => 73,
				13 => 77,
				14 => 80,
				15 => 83,
				16 => 90
				);

	  $maps['eep_emt'] = array(
				     0 => 60,
				     1 => 60,
				     2 => 60,
				     3 => 60,
				     4 => 60,
				     5 => 60,
				     6 => 60,
				     7 => 60,
				     8 => 60,
				     9 => 61,
				     10 => 61,
				     11 => 61,
				     12 => 61,
				     13 => 61,
				     14 => 61,
				     15 => 61,
				     16 => 62,
				     17 => 63,
				     18 => 65,
				     19 => 67,
				     20 => 69,
				     21 => 70,
				     22 => 72,
				     23 => 74,
				     24 => 77,
				     25 => 80,
				     26 => 83,
				     27 => 85,
				     28 => 88,
				     29 => 88,
				     30 => 90
				     );

	  $maps['eep_reading'] = array(
						  0 => 60,
						  1 => 60,
						  2 => 61,
						  3 => 62,
						  4 => 63,
						  5 => 64,
						  6 => 65,
						  7 => 65,
						  8 => 66,
						  9 => 67,
						  10 => 68,
						  11 => 70,
						  12 => 74,
						  13 => 78,
						  14 => 82,
						  15 => 86,
						  16 => 90
						  );

	  $maps['emtea_gse'] = array(
          13 => 2,
          14 => 3,
          15 => 3,
          16 => 3,
          17 => 4,
          18 => 4,
          19 => 4,
          20 => 4,
          21 => 4,
          22 => 5,
          23 => 5,
          24 => 5,
          25 => 5,
          26 => 5,
          27 => 6,
          28 => 6,
          29 => 6,
          30 => 7,
          31 => 7,
          32 => 7,
          33 => 9,
          34 => 10,
          35 => 11,
          36 => 15,
          37 => 19,
          38 => 25,
          39 => 32,
          40 => 39,
          41 => 47,
          42 => 54,
          43 => 62,
          44 => 69,
          45 => 75,
          46 => 81,
          47 => 86,
          48 => 91,
          49 => 95,
          50 => 98,
          51 => 99,
          52 => 99
      );
	  return $maps;
	}

}
