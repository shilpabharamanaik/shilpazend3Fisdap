<?php

/****************************************************************************
 *
*         Copyright (C) 1996-2011.  This is an unpublished work of
*                          Headwaters Software, Inc.
*                             ALL RIGHTS RESERVED
*         This program is a trade secret of Headwaters Software, Inc.
*         and it is not to be copied, distributed, reproduced, published,
*         or adapted without prior authorization
*         of Headwaters Software, Inc.
*
****************************************************************************/

namespace Fisdap;

class MoodleUtils
{

    /**
     * Given a context ('secure_testing', 'study_tools', 'transition_course' or 'pilot_testing'), returns a Zend_Db
     * connection to the appropriate Moodle. Optionally can connect to the mysql slave for
     * read-only queries (SELECT)
     * @param $context string Moodle context string
     * @param $readOnly boolean Whether connection should writeable (FALSE) or read-only (TRUE)
     *
     * @return $moodle_connection Zend database connection
     */
    public static function getConnection($context, $readOnly = false)
    {
        $config = \Zend_Registry::get('config');
        
        // if we are in readOnly mode, then return a connection to the
        if ($readOnly) {
            $context = $context . '_readonly';
        }

        $moodle_connection = \Zend_Db::factory($config->$context);
        return $moodle_connection;
    }

    /**
     * Given a context ('secure_testing', 'study_tools', 'transition_course' or 'pilot_testing'),
     * returns a URL connection to the appropriate Moodle.
     *
     * @param string $context ('secure_testing', 'study_tools', 'transition_course' or 'pilot_testing')
     * @param bool $https
     *
     * @return string
     */
    public static function getUrl($context, $https = false)
    {
        $moodleParams = \Zend_Registry::get('config')->moodle->$context->params->toArray();
        return "http" . ($https ? "s" : "") ."://" . $moodleParams['host'];
    }
    
    /**
     * Get Moodle quiz attempts. Runs queries in read-only query mode.
     * IMPORTANT: keep in mind Moodle transforms usernames to lowercase. So username comparison needs to be case-insensitive
     */
    public static function getQuizAttempts($username, $context, $moodle_quiz_id = null)
    {
        $moodle_connection = MoodleUtils::getConnection($context, true);
        $usernameUppercase = strtoupper($username);
        $prefix = \Fisdap\Entity\MoodleTestDataLegacy::getPrefixByContext($context);
        $select = "SELECT a.uniqueid, a.quiz, a.sumgrades AS score, a.timestart, ".
                "q.sumgrades AS possible, q.name FROM ".
                $prefix."_quiz_attempts a ".
                "LEFT JOIN ".$prefix."_quiz q ON a.quiz = q.id ".
                "LEFT JOIN ".$prefix."_user u ON a.userid = u.id ".
                "WHERE upper(u.username) = '$usernameUppercase' ".
                "AND a.timefinish > 0";
        if ($moodle_quiz_id) {
            $select .= " AND a.quiz = $moodle_quiz_id";
        }
        $select .= " ORDER BY a.timestart DESC";
        $attempts = $moodle_connection->fetchAll($select);
        return $attempts;
    }
    
    /**
     * Get quiz data. Runs queries in read-only query mode.
     */
    public static function getQuizData($moodleTestData)
    {
        $select = "
			SELECT * FROM
			{$moodleTestData->moodle_prefix}_quiz q
			WHERE q.id = {$moodleTestData->moodle_quiz_id}
		";
            
        $moodle_connection = MoodleUtils::getConnection($moodleTestData->getContext(), true);
        
        $quizData = $moodle_connection->fetchAll($select);
        
        if ($quizData) {
            return $quizData[0];
        } else {
            return false;
        }
    }
    
    public static function attemptIsPublished($moodle_quiz_id, $student, $date)
    {
        $student_program = $student->program->id;

        // Southwest Tennessee tests are alwasy published per CX request.
        if ($student_program == 286) {
            return true;
        }
        // ===== END DANGER ===  Hack solution complete.

        // See if the exam as a whole is published
        $test = \Fisdap\EntityUtils::getRepository('MoodleTestDataLegacy')->findOneBy(array('moodle_quiz_id'=>$moodle_quiz_id));
        if (empty($test) || $test->publish_scores == 1) {
            return true;
        }

        // If not, see if there any scheduled text for this attempt, and if so, if they are published
        $scheduled_tests = \Fisdap\EntityUtils::getRepository('ScheduledTestsLegacy')->getScheduledTestsByStudentAndDate($moodle_quiz_id, $student, $date);
        if (empty($scheduled_tests)) {
            return false;
        }
        foreach ($scheduled_tests as $scheduled_test) {
            if ($scheduled_test->publish_scores === 0) {
                return false;
            }
        }

        // if we've gotten here, all scheduled tests with this student and date are published, so return true
        return true;
    }
    
    
    /*
     * Check for Moodle user ID(s). Runs queries in read-only mode
     * Does one or more Fisdap Users have an account established at a particular Moodle installation?
     * IMPORTANT! Moodle appears to be changing all usernames to lowercase, so queries
     *
     * @param array $users Array of User entities or IDs for the accounts you want to check
     * @param string $context The context string (defined in application.ini) defining the relevant Moodle environment
     * @param Zend_DB $dbConnection Optionally a pre-instantiated instance of the Zend_DB connection to Moodle
     *
     * @return array An array of Moodle user IDs (or FALSE if not found), keyed to User Ids
     */
    public static function getMoodleUserIds($usernames, $context, $dbConnection = null)
    {
        if (!is_array($usernames) || empty($usernames)) {
            // we can't do anything if we don't have usernames.
            return array();
        }
        if ($dbConnection == null) {
            $dbConnection = MoodleUtils::getConnection($context, true);
        }
        // create an all-uppercase version of the usernames array. Have to do this to make search case-insensitive
        // because Fisdap allows mixed-case usernames but Moodle usernames are ONLY lowercase
        $usernamesUppercase = array();
        foreach ($usernames as $key => $value) {
            $usernamesUppercase[] = strtoupper($value);
        }
        $prefix = \Fisdap\Entity\MoodleTestDataLegacy::getPrefixByContext($context);
        $select = $dbConnection->select()
                ->from(
                    array('u' => $prefix . '_user'),
                       array('id', 'username')
                )
                ->where('upper(username) IN(?)', $usernamesUppercase); // conditional transforms to upper case to avoid case sensitivity issue
        $statement = $dbConnection->query($select);
        $moodleResult = $statement->fetchAll();
        $verifiedUsernames = array();
        foreach ($moodleResult as $verifiedUser) {
            // we're storing the moodle username in all-upper-case to address the case-sensitivity issue (Moodle usernames are all lower case, Fisdap mixed)
            $verifiedUsernames[$verifiedUser['id']] = strtoupper($verifiedUser['username']);
        }
        
        // return an array of each user ID with either a Moodle ID or FALSE if none found
        $result = array();
        foreach ($usernames as $userId => $username) {
            if (in_array(strtoupper($username), $verifiedUsernames)) {
                // here we are array_search-ing for an all-upper-case username to match the above logic
                $result[$userId] = array_search(strtoupper($username), $verifiedUsernames);
            } else {
                $result[$userId] = false;
            }
        }
        return $result;
    }
    
    /*
     * Check if one or more users have taken one or more Moodle tests. Runs in read-only mode.
     *
     * @param array $users Array of User entities or IDs for the accounts you want to check
     * @param array $moodleTests Array of MoodleTestDataLegacy entiteis or IDs for the tests you want to check
     *
     * @return array An array keyed by Fisdap User ID of
     */
    public static function checkUsersQuizAttemptStatus($users = array(), $moodleTests = array())
    {
        $quizzes = array(); // array of context => array(quizIds)
        foreach ($moodleTests as $key => $moodleTest) {
            if (!($moodleTest instanceof \Fisdap\Entity\MoodleTestDataLegacy)) {
                $moodleTest = \Fisdap\EntityUtils::getEntity('MoodleTestDataLegacy', $moodleTest);
                $moodleTests[$key] = $moodleTest;
            }
            if (!$moodleTest->moodle_quiz_id) {
                return false; //error! we don't have a good MoodleTestDataLegacy
            } else {
                $context = $moodleTest->getContext();
                $quizzes[$context][] = $moodleTest;
            }
        }
                
        // assemble subset of supplied users who actually have Moodle accounts in this context
        $usernames = array();
        $userEntities = array();
        foreach ($users as $user) {
            if ($user instanceof \Fisdap\Entity\User) {
                $usernames[$user->id] = $user->username;
                $userEntities[$user->id] = $user;
            } else {
                $user = \Fisdap\EntityUtils::getEntity('User', $user);
                if ($user->username) {
                    $usernames[$user->id] = $user->username;
                    $userEntities[$user->id] = $user;
                }
            }
        }
        $checkedUsers = MoodleUtils::getMoodleUserIds($usernames, $context);
        $moodleUserIds = array();
        foreach ($checkedUsers as $userId => $moodleUserId) {
            if ($moodleUserId != false) {
                $moodleUserIds[$userId] = $moodleUserId;
            }
        }
        
        // If we have no valid Moodle users at this point, return an array of FALSE for all users.
        // can't check quiz attempts without valid moodle user IDs
        if (empty($moodleUserIds)) {
            $results = array();
            foreach ($userEntities as $user) {
                $results[$user->id] = false;
            }
            return $results;
        }

        // Find out the number of quiz attempts used by the users, for each of the selected quizzes
        // This needs to be done per-context, because each context is a different DB.
        //$attemptsUsedInfo = $dbConnection->fetchAll("SELECT COUNT(*) AS count, userid FROM " . $prefix . "_quiz_attempts WHERE userid IN(?) AND quiz = ?", $moodleUserIds, $quizId);
        $attemptsUsed = array();
        foreach ($quizzes as $context => $contextMoodleTests) {
            // get DB connection
            $prefix = \Fisdap\Entity\MoodleTestDataLegacy::getPrefixByContext($context);
            $dbConnection = MoodleUtils::getConnection($context, true);
            
            // get list of real moodle quiz IDs to Fisdap "modified" moodle quiz IDs for DB query
            $quizIds = array();
            foreach ($contextMoodleTests as $moodleTest) {
                $quizIds[$moodleTest->getRealMoodleQuizId()] = $moodleTest->moodle_quiz_id;
            }

            $select = $dbConnection->select()
                    ->from(
                        array('a' => $prefix . "_quiz_attempts"),
                           array(
                                 'quiz' => 'quiz',
                                 'userid' => 'userid',
                                 'sumgrades' => 'sumgrades',
                                 'attempt' => 'attempt',
                                 )
                    )
                    ->join(
                        array('q' => $prefix . "_quiz"),
                           'a.quiz = q.id',
                           array(
                                'quiz_sumgrades' => 'sumgrades',
                                'grade' => 'grade'
                                 )
                    )
                    ->where('userid IN(?)', array_values($moodleUserIds))
                    ->where('quiz IN(?)', array_keys($quizIds));

            $statement = $dbConnection->query($select);
            $attemptsUsedInfo = $statement->fetchAll();
            /*
             * Sort results into an array of moodle user ID => modified/Fisdap Moodle Quiz ID => number of attempts.
             * So we have an array of moodle User Ids, and the number of attempts for each Fisdap Moodle Quiz ID inside of that
             */
            foreach ($attemptsUsedInfo as $attempt) {
                // calculate this attempt's score
                $score = ($attempt['sumgrades'] / $attempt['quiz_sumgrades']) * $attempt['grade'];
                $attemptsUsed[$attempt['userid']][$quizIds[$attempt['quiz']]]['attempts'] = $attemptsUsed[$attempt['userid']][$quizIds[$attempt['quiz']]]['attempts'] + 1;
                $attemptsUsed[$attempt['userid']][$quizIds[$attempt['quiz']]]['bestScore'] = ($score >= $attemptsUsed[$attempt['userid']][$quizIds[$attempt['quiz']]]['bestScore']) ? $score : $attemptsUsed[$attempt['userid']][$quizIds[$attempt['quiz']]]['bestScore'];
                $attemptsUsed[$attempt['userid']][$quizIds[$attempt['quiz']]]['scores'][$attempt['attempt']] = $score;
            }
        }
        
        // compile results
        $results = array();
        foreach ($userEntities as $user) {
            $moodleUserId = $moodleUserIds[$user->id];
            if ($moodleUserId && isset($attemptsUsed[$moodleUserId])) {
                // grab the attempt info from the database results above
                $results[$user->id] = $attemptsUsed[$moodleUserId];
            } else {
                // did not have any attempts on the specified quizzes
                $results[$user->id] = false;
            }
        }
        return $results;
    }

    /**
     * Get exam scores for a group of users. Exam scores are the final score for the entire exam,
     * not per-question or per-section scores
     * @param string $userReferenceType either 'id', 'username', 'entity' or 'allUsers': Which type of data is $users full of?
     * @param array $users Array of Fisdap User IDs, user entities or Usernames
     * @param array $moodleTests Array of MoodleTestDataLegacy entities
     * @param array $options Filter options
     *
     * @return array Array keyed by user ID, then by Fisdap MoodleTestData ID (not the real Moodle ID), then by attempt #
     * @throws \Exception
     */
    public static function getExamScores($userReferenceType = 'id', $users = array(), $moodleTests = array(), $options = array())
    {
        $quizzes = array(); // array of context => array(quizIds)
        foreach ($moodleTests as $key => $moodleTest) {
            if (!($moodleTest instanceof \Fisdap\Entity\MoodleTestDataLegacy)) {
                $moodleTest = \Fisdap\EntityUtils::getEntity('MoodleTestDataLegacy', $moodleTest);
                $moodleTests[$key] = $moodleTest;
            }
            if (!$moodleTest->moodle_quiz_id) {
                return false; //error! we don't have a good MoodleTestDataLegacy
            } else {
                $context = $moodleTest->getContext();
                $quizzes[$context][] = $moodleTest;
            }
        }

        $usersToCheck = array(); // needs to be userId => username
        if ($userReferenceType != 'all') {
            switch ($userReferenceType) {
                case 'id':
                    // get usernames for these ids
                    $repo = \Fisdap\EntityUtils::getRepository('User');
                    $userResults = $repo->getCertainUsers(array('id' => $users), array('id', 'username'));
                    foreach ($userResults as $user) {
                        $usersToCheck[$user['id']] = $user['username'];
                    }
                    break;
                case 'username':
                    // get user IDs for these usernames
                    $repo = \Fisdap\EntityUtils::getRepository('User');
                    $userResults = $repo->getCertainUsers(array('username' => $users), array('id', 'username'));
                    foreach ($userResults as $user) {
                        $usersToCheck[$user['id']] = $user['username'];
                    }
                    break;
                case 'entity':
                    foreach ($users as $userEntity) {
                        $usersToCheck[$userEntity->id] = $userEntity->username;
                    }
                    break;
                case 'all':
                    // nothing to do here
                    break;
                default:
                    throw new \Exception('Improper $userReferenceType passed to getExamScores');
                    break;
            }
        }

        $results = array();
        foreach ($quizzes as $context => $contextMoodleTests) {
            // get DB connection
            $prefix = \Fisdap\Entity\MoodleTestDataLegacy::getPrefixByContext($context);
            $dbConnection = MoodleUtils::getConnection($context, true);

            // get list of real moodle quiz IDs to Fisdap "modified" moodle quiz IDs for DB query
            $quizIds = array();
            foreach ($contextMoodleTests as $moodleTest) {
                $quizIds[$moodleTest->getRealMoodleQuizId()] = $moodleTest->moodle_quiz_id;
            }

            // get valid Moodle users
            $moodleUserIds = array();
            if (is_array($usersToCheck) && !empty($usersToCheck)) {
                $checkedUsers = MoodleUtils::getMoodleUserIds($usersToCheck, $context);

                foreach ($checkedUsers as $userId => $moodleUserId) {
                    if ($moodleUserId != false) {
                        $moodleUserIds[$userId] = $moodleUserId;
                    }
                }
            }

            $select = $dbConnection->select()
                ->from(
                    array('a' => $prefix . "_quiz_attempts"),
                    array(
                        'quiz' => 'quiz',
                        'userid' => 'userid',
                        'sumgrades' => 'sumgrades',
                        'attempt' => 'attempt',
                    )
                )
                ->join(
                    array('q' => $prefix . "_quiz"),
                    'a.quiz = q.id',
                    array(
                        'quiz_sumgrades' => 'sumgrades',
                        'grade' => 'grade'
                    )
                )
                ->where('quiz IN(?)', array_keys($quizIds));

            if ($userReferenceType != 'all' && is_array($moodleUserIds)) {
                $select->where('userid IN(?)', array_values($moodleUserIds));
            }

            if (isset($options['includeAttempts']) && $options['includeAttempts'] > 0) {
                $select->where('attempt <= ?', $options['includeAttempts']);
            }
            //$sql = $select->__toString();
            // DEBUG
            //$log = \Zend_Registry::get('logger');
            //$log->debug('get exam scores query: ' . $sql);

            $statement = $dbConnection->query($select);
            $attempts = $statement->fetchAll();
            /*
             * Sort results into an array of moodle user ID => modified/Fisdap Moodle Quiz ID => number of attempts.
             * So we have an array of moodle User Ids, and the number of attempts for each Fisdap Moodle Quiz ID inside of that
             */
            foreach ($attempts as $attempt) {
                // calculate this attempt's score
                $score = ($attempt['sumgrades'] / $attempt['quiz_sumgrades']) * $attempt['grade'];
                if ($userReferenceType != 'all') {
                    // get the matching fisdap user ID for this Moodle user ID
                    $userId = array_search($attempt['userid'], $moodleUserIds);
                } else {
                    // we're just getting all attempts, so we're not getting fisdap user IDs at all
                    $userId = $attempt['userid'];
                }
                $results[$userId][$quizIds[$attempt['quiz']]][$attempt['attempt']] = $score;
            }
        }

        return $results;
    }


    public static function countProgramQuizAttempts($programs = array(), $moodleTests = array())
    {
        $quizzes = array(); // array of context => array(quizIds)
        foreach ($moodleTests as $key => $moodleTest) {
            if (!($moodleTest instanceof \Fisdap\Entity\MoodleTestDataLegacy)) {
                $moodleTest = \Fisdap\EntityUtils::getEntity('MoodleTestDataLegacy', $moodleTest);
                $moodleTests[$key] = $moodleTest;
            }
            if (!$moodleTest->moodle_quiz_id) {
                return false; //error! we don't have a good MoodleTestDataLegacy
            } else {
                $context = $moodleTest->getContext();
                $quizzes[$context][] = $moodleTest;
            }
        }

        $counts = array();
        $moodleUsers = array();

        // Go through each program and get valid Moodle Users for each context
        $userRepo = \Fisdap\EntityUtils::getRepository('User');
        foreach ($programs as $program) {
            if ($program instanceof \Fisdap\Entity\ProgramLegacy) {
                $programId = $program->id;
            } else {
                $programId = $program;
            }
            $users = $userRepo->findUsers($programId, "", null, array('student'), array('u.id', 'u.username'));
            $usernames = array();
            foreach ($users as $user) {
                $usernames[$user['id']] = $user['username'];
            }
            foreach ($quizzes as $context => $tests) {
                if (!isset($moodleUsers[$context][$programId])) {
                    $moodleUsers[$context][$programId] = array_values(array_filter(MoodleUtils::getMoodleUserIds($usernames, $context)));
                }
            }
        }

        // Now lets go through our quizzes and check for attempts by our groups of users
        foreach ($quizzes as $context => $tests) {
            // get DB connection in read-only mode
            $prefix = \Fisdap\Entity\MoodleTestDataLegacy::getPrefixByContext($context);
            $dbConnection = MoodleUtils::getConnection($context, true);

            foreach ($tests as $moodleTest) {
                foreach ($moodleUsers[$context] as $programId => $usernames) {
                    if (count($usernames) > 0) {
                        // check attempts for this Test in this context for each program
                        $select = $dbConnection->select()
                            ->from(
                                array('a' => $prefix . "_quiz_attempts"),
                                array('attempts' => 'COUNT(*)')
                            )
                            ->where('userid IN(?)', $usernames)
                            ->where('quiz = ?', $moodleTest->getRealMoodleQuizId());
                        $statement = $dbConnection->query($select);
                        $attemptsInfo = $statement->fetchAll();
                        $counts[$programId][$moodleTest->moodle_quiz_id] = $attemptsInfo[0]['attempts'];
                    } else {
                        $counts[$programId][$moodleTest->moodle_quiz_id] = 0;
                    }
                }
            }
        }


        // Yeah, we've got our counts!
        return $counts;
    }


    /*
     * Retrieve the default maximum allowed number of attempts in Moodle quiz corresponding to a MoodleTestDataLegacy
     * Runs in read-only mode
     *
     * @param \Fisdap\Entity\MoodleTestDataLegacy $moodleTest Either an ID or a MoodleTestDataLegacy entity
     * @param \Zend_DB $dbConnection Optional: A ZEND_DB connection to the moodle database
     * @param string $prefix Optional: The table prefix in the Moodle database
     *
     * @return integer The number of default maximum attempts allowed on the test
     */
    public static function getQuizDefaultMaxAttempts($moodleTest, $dbConnection = null, $prefix = null)
    {
        // get the actual factual Moodle Quiz ID from MoodleTestData
        if (!($moodleTest instanceof \Fisdap\Entity\MoodleTestDataLegacy)) {
            $moodleTest = \Fisdap\EntityUtils::getEntity('MoodleTestDataLegacy', $moodleTest);
        }
        if (!$moodleTest->moodle_quiz_id) {
            return false; //error! we don't have a good MoodleTestDataLegacy
        }
        $quizId = $moodleTest->getRealMoodleQuizId();
        
        if ($dbConnection == null || $prefix == null) {
            // get DB connection
            $context = $moodleTest->getContext();
            $prefix = $moodleTest->getPrefixByContext($context);
            $dbConnection = MoodleUtils::getConnection($context, true);
        }
        
        // Get the base number of attempts allowed for this quiz
        $quizInfo = $dbConnection->fetchRow("SELECT attempts FROM " . $prefix . "_quiz WHERE id = ?", $quizId);
        $quizMaxAttempts = $quizInfo['attempts'];
        
        return $quizMaxAttempts;
    }
    
    /*
     * Retrieve information about a particular set of Fisdap users' attempts allowed/used
     * for a particular Moodle Quiz ID (this is the quiz id IN THE MOODLE DATABASE,
     * not a blueprint or scheuduled test ID) and a given context
     * Runs in read-only mode
     *
     * @param array $users An array of User info with a "username" property OR an array of User entities
     * @param \Fisdap\Entity\MoodleTestDataLegacy $moodleTest A MoodleTestDataLegacy entity (or MoodleQuiz_id) representing the quiz for which you want to check attempt info
     * @param string $context The context string (defined in application.ini) defining the relevant Moodle environment
     *
     * @return array Array keyed by Fisdap user ID containing attributes describing attempt number/limit info, and permissions
     */
    public static function getUsersQuizAttemptLimitInfo($users = array(), $moodleTest)
    {
        // get the actual factual Moodle Quiz ID from MoodleTestData
        if (!($moodleTest instanceof \Fisdap\Entity\MoodleTestDataLegacy)) {
            $moodleTest = \Fisdap\EntityUtils::getEntity('MoodleTestDataLegacy', $moodleTest);
        }
        if (!$moodleTest->moodle_quiz_id) {
            return false; //error! we don't have a good MoodleTestDataLegacy
        }
        $quizId = $moodleTest->getRealMoodleQuizId();
        $context = $moodleTest->getContext();
        $prefix = $moodleTest->getPrefixByContext($context);
        
        // get DB connection
        $dbConnection = MoodleUtils::getConnection($context, true);
        
        // assemble subset of supplied users who actually have Moodle accounts in this context
        $usernames = array();
        foreach ($users as $key => $user) {
            if (is_array($user) && isset($user['username'])) {
                $usernames[$key] = $user['username'];
            } elseif ($user instanceof \Fisdap\Entity\User) {
                $usernames[$user->id] = $user->username;
            } elseif (is_numeric($user)) {
                $user = \Fisdap\EntityUtils::getEntity('User', $user);
                if ($user->username) {
                    $usernames[$user->id] = $user->username;
                }
            }
        }
        $checkedUsers = MoodleUtils::getMoodleUserIds($usernames, $context, $dbConnection);
        $moodleUserIds = array();
        foreach ($checkedUsers as $userId => $moodleUserId) {
            if ($moodleUserId != false) {
                $moodleUserIds[$userId] = $moodleUserId;
            }
        }

        // get the default max attempts value for this test
        $quizMaxAttempts = MoodleUtils::getQuizDefaultMaxAttempts($moodleTest, $dbConnection, $prefix);
            
        // Can't check attempt info unless we have some valid moodle users at this point.
        if (!empty($moodleUserIds)) {
            // Check to see these users have any overrides for this quiz.
            //$overrideInfo = $dbConnection->fetchAll("SELECT userid, attempts FROM " . $prefix . "_quiz_overrides WHERE userid IN(?) AND quiz = ?;", $moodleUserIds, $quizId);
            $select = $dbConnection->select()
                    ->from(
                        array('o' => $prefix . "_quiz_overrides"),
                           array('userid', 'attempts')
                    )
                    ->where('userid IN(?)', array_values($moodleUserIds))
                    ->where('quiz = ?', $quizId);
            $statement = $dbConnection->query($select);
            $overrideInfo = $statement->fetchAll();
            $overrides = array();
            foreach ($overrideInfo as $override) {
                $overrides[$override['userid']] = $override['attempts'];
            }
            
            // Find out the number of quiz attempts used by the users
            //$attemptsUsedInfo = $dbConnection->fetchAll("SELECT COUNT(*) AS count, userid FROM " . $prefix . "_quiz_attempts WHERE userid IN(?) AND quiz = ?", $moodleUserIds, $quizId);
            $select = $dbConnection->select()
                    ->from(
                        array('a' => $prefix . "_quiz_attempts"),
                           array('userid' => 'userid')
                    )
                    ->where('userid IN(?)', array_values($moodleUserIds))
                    ->where('quiz = ?', $quizId);
            $statement = $dbConnection->query($select);
            $attemptsUsedInfo = $statement->fetchAll();
            $attemptsUsed = array();
            foreach ($attemptsUsedInfo as $attempt) {
                $attemptsUsed[$attempt['userid']] = $attemptsUsed[$attempt['userid']] + 1;
            }
        }
        
        // combine the info
        $results = array();
        foreach ($moodleUserIds as $userId => $moodleUserId) {
            $results[$userId] = array(
                'moodleUserId' => $moodleUserId,
                'maxAllowed' => (isset($overrides[$moodleUserId]) && $overrides[$moodleUserId] != null) ? $overrides[$moodleUserId] : $quizMaxAttempts,
                'used' => (isset($attemptsUsed[$moodleUserId]) && $attemptsUsed[$moodleUserId] != null) ? $attemptsUsed[$moodleUserId] : 0,
                'hasOverride' => (isset($overrides[$moodleUserId]) && $overrides[$moodleUserId] != null) ? true : false,
            );
            $results[$userId]['remaining'] = max(array(($results[$userId]['maxAllowed'] - $results[$userId]['used']), 0));
        }
        
        // add in the users who didn't have moodle user accounts
        foreach ($checkedUsers as $userId => $status) {
            if ($status === false) {
                $results[$userId] = array(
                    'moodleUserId' => false,
                    'maxAllowed' => null,
                    'used' => null,
                    'remaining' => null,
                    'hasOverride' => false,
                );
            }
        }
        
        return $results;
    }
    
    /*
     * Set the number of attempts that one or more users should be allowed to take on a specific quiz
     * This can take a numeric value to set the new limit in absolute terms, or it can take a string in the form of
     * "+1" "-2" "+7" etc. A string value in that format will add/remove that number of attempts to/from the
     * user's existing limit.
     *
     * @param array $users Array of User entities or IDs for the accounts you want to check
     * @param \Fisdap\Entity\MoodleTestDataLegacy $moodleTest A MoodleTestDataLegacy entity (or MoodleQuiz_id) representing the quiz for which you want to check attempt info
     * @param mixed $newLimit An integer representing the absolute new limit to set, or a string describing a relative change ("-1" or "-1" for example)
     */
    public static function setUsersQuizAttemptLimit($users, $moodleTest, $newLimit)
    {
        if (!($moodleTest instanceof \Fisdap\Entity\MoodleTestDataLegacy)) {
            $moodleTest = \Fisdap\EntityUtils::getEntity('MoodleTestDataLegacy', $moodleTest);
        }

        // get information about the users' existing test attempts/limit
        $limitInfo = MoodleUtils::getUsersQuizAttemptLimitInfo($users, $moodleTest);
        if (is_array($limitInfo)) {
            // we have a valid MoodleTestDataLegacy and some results
            $quizId = $moodleTest->getRealMoodleQuizId();
            
            // get database connection
            $context = $moodleTest->getContext();
            $prefix = $moodleTest->getPrefixByContext($context);
            $dbConnection = MoodleUtils::getConnection($context);
            
            // determine if we are setting a relative or absolute value
            if (strpos($newLimit, '+') === 0 && is_numeric(substr($newLimit, 1))) {
                $modifierType = 'relative';
                $newLimit = intval(substr($newLimit, 1));
            } elseif (strpos($newLimit, '-') === 0 && is_numeric(substr($newLimit, 1))) {
                $modifierType = 'relative';
            } elseif (is_numeric($newLimit) && $newLimit >= 0) {
                $modifierType = 'absolute'; // we are making an absolute modification to the students' limits
            } else {
                return false; // improper value for $newLimit
            }
            
            // go through (valid) users' limits and make modification queries
            $modified = array();
            foreach ($limitInfo as $userId => $info) {
                if ($info['moodleUserId'] > 0) {
                    // set the new limit
                    if ($modifierType == 'absolute') {
                        $thisUserNewLimit = $newLimit;
                    } else {
                        // relative limit. Shouldn't be less than zero (wanna go for a ride?).
                        $thisUserNewLimit = max(($info['maxAllowed'] + $newLimit), 0);
                    }
                    
                    // make the change
                    if ($info['hasOverride']) {
                        //UPDATE fismdl_quiz_overrides SET attempts = 8 WHERE userid = 36330 AND quiz = 46;
                        $data = array(
                            'attempts' => $thisUserNewLimit,
                        );
                        $where = array();
                        $where['userid = ?'] = $info['moodleUserId'];
                        $where['quiz = ?'] = $quizId;
                        $dbConnection->update($prefix . "_quiz_overrides", $data, $where);
                        $modified[] = $userId;
                    } else {
                        $data = array(
                            'quiz' => $quizId,
                            'userid' => $info['moodleUserId'],
                            'attempts' => $thisUserNewLimit
                        );
                        $dbConnection->insert($prefix . "_quiz_overrides", $data);
                        $modified[$userId] = $userId;
                    }
                }
            }
            
            // return results with list of those users whose limits were modified, and those who were not (because no Moodle account found, or because of an error with the user account itself)
            $results = array('modified' => $modified, 'noMoodleAccount' => array(), 'error' => array());
            foreach ($users as $user) {
                if ($user instanceof \Fisdap\Entity\User) {
                    $userId = $user->id;
                } else {
                    $userId = $user;
                }
                if (!isset($modified[$userId]) && $limitInfo[$userId]['moodleUserId'] === false) {
                    $results['noMoodleAccount'][$userId] = $userId;
                } elseif (isset($modified[$userId])) {
                    $results['error'][$userId] = $userId;
                }
            }
            
            return $results;
        } else {
            return false;
        }
    }
    
    
    /*
     * Clear out user quiz overrides for one or more users on one or more quizzes
     * This is useful when re-enrolling a user who has previously unenrolled. If they
     * come back later to re-purchase the product (enroll again), any lingering overrides
     * will negatively change the default behavior of quiz limits.
     *
     * @param array $users Array of User entities or IDs for the accounts you want to check
     * @param array $tests An array of MoodleTestDataLegacy entities (or MoodleQuiz_ids) representing the quiz for which you want to check attempt info
     */
    public static function removeUserQuizOverrides($users, $tests)
    {
        // get Moodle Tests
        $moodleTests = array();
        foreach ($tests as $test) {
            if (!($test instanceof \Fisdap\Entity\MoodleTestDataLegacy)) {
                $test = \Fisdap\EntityUtils::getEntity('MoodleTestDataLegacy', $test);
            }
            $context = $test->getContext();
            $moodleTests[$context][$test->moodle_quiz_id] = $test;
        }
        
        // get users
        $usernames = array();
        foreach ($users as $key => $user) {
            if (is_array($user) && isset($user['username'])) {
                $usernames[$key] = $user['username'];
            } elseif ($user instanceof \Fisdap\Entity\User) {
                $usernames[$user->id] = $user->username;
            } elseif (is_numeric($user)) {
                $user = \Fisdap\EntityUtils::getEntity('User', $user);
                if ($user->username) {
                    $usernames[$user->id] = $user->username;
                }
            }
        }
        
        if (count($usernames) > 0 && count($moodleTests) > 0) {
            // do the work
            foreach ($moodleTests as $context => $tests) {
                $dbConnection = \Fisdap\MoodleUtils::getConnection($context);
                $prefix = \Fisdap\Entity\MoodleTestDataLegacy::getPrefixByContext($context);
                $checkedUsers = \Fisdap\MoodleUtils::getMoodleUserIds($usernames, $context, $dbConnection);
                $moodleUserIds = array();
                foreach ($checkedUsers as $id => $moodleId) {
                    if ($moodleId != false) {
                        $moodleUserIds[$id] = $moodleId;
                    }
                }
                
                $quizIds = array_keys($tests);
                
                if (count($moodleUserIds) > 0) {
                    $dbConnection->delete($prefix . '_quiz_overrides', 'userid IN (' . implode(', ', $moodleUserIds) . ') AND quiz IN (' . implode(', ', $quizIds) . ')');
                }
            }
        } // otherwise, no proper moodle user Ids and/or moodle quizzes were found, so we can't do anything
    }
    
    /**
     * Get all transition course completions within a date range
     * @param \DateTime $startDate can also be given a string date representation
     * @param \DateTime $endDate can also be given a string date representation
     * @return array containing course completions
     */
    public function getTransitionCourseCompletions($startDate, $endDate)
    {
        $context = "transition_course";
        $dbConnection = MoodleUtils::getConnection($context);
        $prefix = \Fisdap\Entity\MoodleTestDataLegacy::getPrefixByContext($context);
        
        //Turn startDate and endDate into DateTimes if they aren't already
        if (!($startDate instanceof \DateTime)) {
            $startDate = new \DateTime($startDate);
        }
        if (!($endDate instanceof \DateTime)) {
            $endDate = new \DateTime($endDate);
        }

        $endDate->setTime(23, 59, 59);

        $select = $dbConnection->select()
                            ->from(
                                array('c' => $prefix . "_simplecertificate_issues"),
                                   array('coursename', 'timecreated')
                            )
                            ->join(array('u' => $prefix . "_user"), 'c.userid = u.id', array("username"))
                            ->where('c.timecreated > ?', $startDate->format("U"))
                            ->where('c.timecreated < ?', $endDate->format("U"));
        $statement = $dbConnection->query($select);
        return $statement->fetchAll();
    }
    
    /**
     * This function finds and returns any completed transition courses from Moodle for the supplied
     * list of users.
     *
     * @param array $usernames Array containing the usernames to find completions for.
     */
    public static function getTransitionCourseCompletionsByUsernames($usernames)
    {
        $context = "transition_course";
        $dbConnection = MoodleUtils::getConnection($context);
        $prefix = \Fisdap\Entity\MoodleTestDataLegacy::getPrefixByContext($context);
        
        // Clean up the usernames here...
        $cleanNames = array();
        
        foreach ($usernames as $un) {
            $cleanNames[] = "'" . strtolower($un) . "'";
        }
        
        $query = "
			SELECT
			    u.username,
				si.timecreated,
				si.coursename
			FROM
				{$prefix}_simplecertificate_issues si
				INNER JOIN {$prefix}_user u ON si.userid = u.id
			WHERE
				u.username IN (" . implode(',', $cleanNames) . ")
		;
		";
                
        $res = $dbConnection->query($query);
        
        return $res->fetchAll();
    }
    
    public static function getPreceptorTrainingCompletionsById($ids)
    {
        $context = "preceptor_training";
        $dbConnection = MoodleUtils::getConnection($context);
        $prefix = \Fisdap\Entity\MoodleTestDataLegacy::getPrefixByContext($context);
        
        // These two values were effectively hardcoded in legacy
        // FISDAP/phputil/classes/ClinicalEducatorTrainingUtils.inc:76
        $final_quiz_id = 40;
        $final_passing_grade = 15;
        
        $instructorNames = array();
        
        foreach ($ids as $id) {
            $instructor = \Fisdap\EntityUtils::getEntity('InstructorLegacy', $id);
            $instructorNames[$id] = "'" . strtolower($instructor->username) . "'";
        }
        
        $instructorNameString = implode(',', $instructorNames);
        
        $query = "
			SELECT
				q.*,
				u.username
			FROM
				{$prefix}_user u
				INNER JOIN {$prefix}_quiz_attempts q ON q.userid = u.id
			WHERE
				u.username IN ({$instructorNameString})
				AND q.quiz = {$final_quiz_id}
				AND q.timefinish > 0
				AND q.sumgrades >= {$final_passing_grade}
			ORDER BY
				u.id, timestart DESC
		;
		";
                
        $res = $dbConnection->query($query);
        
        $rows = $res->fetchAll();
        
        $cleanRows = array();
        
        foreach ($rows as $row) {
            $newRow = $row;
            
            $newRow['instructor_id'] = array_search("'" . $row['username'] . "'", $instructorNames);
            
            $cleanRows[] = $newRow;
        }
        
        return $cleanRows;
    }
}
