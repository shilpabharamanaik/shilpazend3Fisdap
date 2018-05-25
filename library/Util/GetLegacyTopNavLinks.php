<?php

class Util_GetLegacyTopNavLinks {

	const MYFISDAP  = "MYFISDAP";
	const SCHEDULER = "SCHEDULER";
	const REPORTS   = "REPORTS";
	const ADMIN     = "ADMIN";
	const HELP      = "HELP";
	const LOGOUT    = "LOGOUT";
	const SUBMIT_A_TEST_ITEM_ONLINE    = "SUBMIT_A_TEST_ITEM_ONLINE";
	const VIEW_REWARDS_POINTS_BALANCE = "VIEW_REWARDS_POINTS_BALANCE";
	const VISIT_FISDAPS_TEST_BANK = "VISIT_FISDAPS_TEST_BANK";
	const TESTING_FAQS = "TESTING_FAQS";
	const RECORD_NREMT_RESULTS = "RECORD_NREMT_RESULTS";
	const TRAINING_VIDEO = "TRAINING_VIDEO";
	const REVIEW_STUDENT_SCORE_AND_LEARNING_PERSCRIPTION = "REVIEW_STUDENT_SCORE_AND_LEARNING_PERSCRIPTION";
	const SCHEDULE_A_SECURE_EXAM = "SCHEDULE_A_SECURE_EXAM";
	const STUDENT_SCORES = "STUDENT_SCORES_LINK";
	const RESOURCES = "RESOURCES";
	const CONSENT_FORM = "CONSENT_FORM";
	const SUBMIT_TEST_ONLINE = "SUBMIT_TEST_ONLINE";
	const EVAL_LIST = "EVAL_LIST";
	const SCHEDULER_ROOT = "SCHEDULER_ROOT";

	/**
	 *	Based on a standardized name, this method will return the associated FISDAP legacy link
	 *	@param string standard, symbolic link name I.E. my_fisdap or scheduler
	 *	@return string converted string
	 */
	public static function getLink( $getWhat , $serverUrl = "")
	{

		$theLink = "not-defined-yet";


		switch ($getWhat) {

			case self::SUBMIT_TEST_ONLINE:
				$theLink = "$serverUrl/oldfisdap/redirect?loc=shift/evals/addTestItem.html";
				break;

			case self::CONSENT_FORM:
				$theLink = "$serverUrl/oldfisdap/redirect?loc=admin/consent.html";
				break;

			case self::RESOURCES:
				$theLink = "$serverUrl/oldfisdap/redirect?loc=resources/forms.html";
				break;

			case self::STUDENT_SCORES:
				$theLink = "$serverUrl/learning-center/index/test-scores";
				break;

			case self::SCHEDULE_A_SECURE_EXAM:
				$theLink = "$serverUrl/oldfisdap/redirect?loc=testing/testSchedule.html";
				break;

			case self::REVIEW_STUDENT_SCORE_AND_LEARNING_PERSCRIPTION:
				$theLink = "$serverUrl/oldfisdap/redirect?loc=testing/getMoodleScores.html";
				break;

			case self::TRAINING_VIDEO:
				$theLink = "$serverUrl/oldfisdap/redirect?loc=OSPE/ContributorTraining/index.html";
				break;

			case self::RECORD_NREMT_RESULTS:
				$theLink = "$serverUrl/oldfisdap/redirect?loc=admin/setRegistryScores.html";
				break;

			case self::TESTING_FAQS:
				$theLink = "$serverUrl/oldfisdap/redirect?loc=testing/testingHome.html";
				break;

			case self::VISIT_FISDAPS_TEST_BANK:
				$theLink = "$serverUrl/oldfisdap/redirect?loc=shift/evals/search_test_bank.php";
				break;

			case self::VIEW_REWARDS_POINTS_BALANCE:
				$theLink = "$serverUrl/oldfisdap/redirect?loc=testing/prog_pop_summary.html";
				break;

			case self::SUBMIT_A_TEST_ITEM_ONLINE:
				$theLink = "$serverUrl/oldfisdap/redirect?loc=shift/evals/addTestItem.html";
				break;

			case self::LOGOUT:
				$theLink = "$serverUrl/oldfisdap/redirect?loc=auth/logout.php";
				break;

			case self::MYFISDAP:
				$theLink = "$serverUrl/oldfisdap/redirect?loc=index.html@target_pagename=my_fisdap/my_fisdap.php";
				break;

			case self::SCHEDULER:
				$theLink = "$serverUrl/oldfisdap/redirect?loc=index.html@target_pagename=scheduler/schedulercont.html";
				break;

			case self::REPORTS:
				$theLink = "$serverUrl/oldfisdap/redirect?loc=reports/index.html";
				break;

			case self::ADMIN:
				$theLink = "$serverUrl/oldfisdap/redirect?loc=index.html@target_pagename=admin/index.html";
				break;

			case self::HELP:
				$theLink = "not-defined-yet for environment: ".APPLICATION_ENV;
				break;

			case self::EVAL_LIST:
				$theLink = "$serverUrl/oldfisdap/redirect?loc=index.html@target_pagename=shift/evals/listAllEvalSessions.html?firstloaded=1";
				break;

			case self::SCHEDULER_ROOT:
				$theLink = "$serverUrl/oldfisdap/redirect?loc=index.html@target_pagename=scheduler/";
				break;

			default:
				break;
		}

		return $theLink;

	}

}
?>
