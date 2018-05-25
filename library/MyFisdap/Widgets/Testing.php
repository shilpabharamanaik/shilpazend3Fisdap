<?php

use Fisdap\Service\ProductService;

class MyFisdap_Widgets_Testing extends MyFisdap_Widgets_Base
{
    public function render()
    {
        $sn = $this->getWidgetUser()->getCurrentUserContext()->getPrimarySerialNumber();

        // js and html for opening the score help bubble
        $html = "<script>
				$(function(){
					baseOptions = {
						activation: 'click',
						local:true, 
						cursor: 'pointer',
						width: 450,
						cluetipClass: 'jtip',
						sticky: true,
						closePosition: 'title',
						closeText: '<img width=\"25\" height=\"25\" src=\"/images/icons/delete.png\" alt=\"close\" />'
					}

					$('.score-help').cluetip(baseOptions);
				});
			</script>
		";

        // secure testing section
        if ($sn->hasProductAccess('secure_testing')) {
            $testingData = $this->getTestingData(array('secure_testing', 'pilot_testing'), 'Fisdap Testing');
            $testingData['image'] = '<img src="'.ProductService::COMPREHENSIVE_EXAM_ICON.'" class="shield" />';
            $testingMessage = null;

            if (empty($testingData['moodle_attempts'])) {
                $testingMessage = "It doesn't look like you have taken any exams yet.";
            }

            $html .= $this->printSection($testingData, $testingMessage);
        }

        // study tools section
        $studyData = $this->getTestingData(array('study_tools'), 'Fisdap Study Tools');
        $studyData['image'] = '<img src="'.ProductService::STUDY_TOOLS_ICON.'" class="shield" />';
        $studyMessage = null;

        if (!($sn->hasProductAccess('emtb_study_tools') || $sn->hasProductAccess('prep'))) {
            $studyMessage = "<a href='http://www.fisdap.net/study_tools'>Get ready for your certification exam! Check out our Study Tools</a>";
        } elseif (empty($studyData['moodle_attempts'])) {
            $studyMessage = "It doesn't look like you have taken any exams yet.";
        }

        $html .= $this->printSection($studyData, $studyMessage);

        // help section
        $html .= "<div><h4 class='header'>Getting Ready to Take an Exam?</h4>";
        $html .= "<a href='https://testing-instructions.s3.amazonaws.com/how_to_succeed.pdf'>How to Succeed on a Fisdap Exam</a></div>";

        return "<div class='fisdap-exams-widget'>" . $html . "</div>";
    }


    public function printSection($section_attempts, $message = null)
    {
        $html = "<div><table>\n";
        $html .= "<tr><th colspan=3><h4 class='header' style='margin-top:0;'>{$section_attempts['title']}{$section_attempts['image']}</h4></th></tr>\n";

        if ($message != null) {
            $html .= "<tr class='test-result'><td colspan=3>$message</td></tr>\n";
        } else {
            foreach ($section_attempts['moodle_attempts'] as $attempt) {
                if ($attempt['published']) {
                    $html .= "<tr class='test-result clickable' onclick='window.location=\"{$attempt['rx_url']}\"'>";
                } else {
                    $html .= "<tr class='test-result'>";
                }
                $html .= "<td>{$attempt['name']}</td>" .
                    "<td style='padding: 0px 10px'>{$attempt['date']}</td>" .
                    "<td style='text-align: right;'>{$attempt['score']}</td>" .
                    "</tr>\n";
            }
        }
        $html .= "</table></div>\n";

        return $html;
    }


    public function getTestingData($contexts, $finalTitle)
    {
        // Most of the logic for this was taken from:
        // modules/portfolio/controllers/IndexController.php::examsAction()

        $student = $this->getWidgetUser()->getCurrentUserContext()->getRoleData();

        // create an array to store these attempts
        $tests = array();

        // get exam attempts for each context
        foreach ($contexts as $context) {
            $moodle_attempts = \Fisdap\MoodleUtils::getQuizAttempts($student->user->username, $context);
            $moodle_modifier = \Fisdap\Entity\MoodleTestDataLegacy::getModifier($context);

            // for each attempt, parse the data and get it ready for the view
            foreach ($moodle_attempts as $i => $attempt) {
                // get moodle test data
                $moodle_quiz_id = $attempt['quiz'] + $moodle_modifier;
                $mysqldate = date('Y-m-d', $attempt['timestart']);

                $moodle_test_data = \Fisdap\EntityUtils::getRepository('MoodleTestDataLegacy')->findOneBy(array('moodle_quiz_id' => $moodle_quiz_id));

                // If we have the test registered in MoodleTestData
                // AND if the test is set to "show totals = 1" (since this table is just test totals)
                // then display the test result
                if (!(empty($moodle_test_data))) {
                    // make 'timestart' the key for each attempt, so they can be sorted later
                    $key = "ts" . $attempt['timestart'];
                    // get the name
                    if (empty($moodle_test_data)) {
                        $tests[$key]['name'] = $attempt['name'];
                    } else {
                        $tests[$key]['name'] = $moodle_test_data->test_name;
                    }

                    // configure the date
                    $date = date('m-d-Y', $attempt['timestart']);
                    $tests[$key]['date'] = $date;

                    if ($moodle_test_data->show_totals) {
                        // display a score
                        // figure out if the score is published
                        $published = \Fisdap\MoodleUtils::attemptIsPublished($moodle_quiz_id, $student, $mysqldate);
                        $tests[$key]['published'] = $published;
                        if ($published) {
                            $tests[$key]['score'] = round(($attempt['score'] / $attempt['possible']) * 100, 0) . "%";
                            $tests[$key]['rx_url'] = "/oldfisdap/redirect?loc=testing/stuScoreDetails.html@attempt=" . $attempt['uniqueid'] . "@testid=$moodle_quiz_id";
                        } else {
                            $tests[$key]['score'] = "<a class='score-help' rel='#score-popup-$key' href='#score-popup-$key' title='Score not published'>" .
                                "<img class='question-mark' src='/images/icons/question_mark.svg'></a>" .
                                "<div id='score-popup-$key' style='display: none;'>" .
                                "Your " . $tests[$key]['name'] . " scores are hidden. To see your results, please contact your instructor." .
                                "</div>";

                        }
                    } else {
                        // totals are not displayed for this test, so we can't show a score in the widget
                        $tests[$key]['score'] = 'N/A';
                    }
                }
            }
        }

        // add the pilot exams to the secure exams array
        krsort($tests); // re-order by descending chronological order

        return array('title' => $finalTitle, 'moodle_attempts' => $tests);
    }

    public function getDefaultData()
    {
        return array();
    }

    public static function userCanUseWidget($widgetId)
    {
        $currentContext = \Fisdap\EntityUtils::getEntity('MyFisdapWidgetData', $widgetId)->user->getCurrentUserContext();

        if ($currentContext->isInstructor()) {
            return false;
        } else {
            $sn = $currentContext->getPrimarySerialNumber();

            return ($sn->hasProductAccess('all_testing'));
        }
    }
}
