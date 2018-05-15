<?php

/* * **************************************************************************
 *
 *         Copyright (C) 1996-2011.  This is an unpublished work of
 *                          Headwaters Software, Inc.
 *                             ALL RIGHTS RESERVED
 *         This program is a trade secret of Headwaters Software, Inc.
 *         and it is not to be copied, distributed, reproduced, published,
 *         or adapted without prior authorization
 *         of Headwaters Software, Inc.
 *
 * ************************************************************************** */

/**
 * Description of StudentPicker
 *
 * @author astevenson
 */
class Fisdap_View_Helper_TestScoresResults extends Zend_View_Helper_Abstract
{
    
    /**
     * This helper prints out a table with test results in it.
     */
    public function testScoresResults($sections, $results, $testId, $scheduledTestId = null, $sectionDisplayOptions = null, $overallDisplayOptions = null)
    {
        $this->view->headScript()->appendFile("/js/tableSorter/jquery.tablesorter.min.js");
        $this->view->headScript()->appendFile("/js/library/Fisdap/View/Helper/testScoresResults.js");
        $this->view->headLink()->appendStylesheet("/css/library/Fisdap/View/Helper/testScoresResults.css");
        
        // dear developer: i know, i know super bad but just let it happen.
        // love, your favoriate developer (hammer, of course)
        $html = "<script>$('#retrieveThrobber').hide();
				$('#test_results').fadeIn();
				$('html,body').animate({scrollTop:1020}, 'slow');
				</script>";
        
        $test = \Fisdap\EntityUtils::getEntity('MoodleTestDataLegacy', $testId);
        
        $dateText = '';
        
        // should we show totals in this test?
        if ($overallDisplayOptions == null || !isset($overallDisplayOptions['showTotals']) || $overallDisplayOptions['showTotals']) {
            $showTotals = true;
        } else {
            $showTotals = false;
        }
        
        if ($scheduledTestId) {
            $scheduledTest = \Fisdap\EntityUtils::getEntity('ScheduledTestsLegacy', $scheduledTestId);
        
            $formatString = 'F d, Y';
        
            if ($scheduledTest->start_date->format('Ymd') > 0) {
                $dateText  = " - " . $scheduledTest->start_date->format($formatString);
            }
        
            // Don't show the second one if it doesn't exist, OR they are the same date.
            if ($scheduledTest->end_date->format('Ymd') > 0 && $scheduledTest->start_date->format('Ymd') != $scheduledTest->end_date->format('Ymd')) {
                $dateText .= " - " . $scheduledTest->end_date->format($formatString);
            }
        }

        // Print out the headers for the sections for this test...
        if ($sections && count($results['results']) >  0) {
            // Print out some info about the test...
            $html .= "<div class='clear'></div>";
            $html .= "<div class='test_results_container island' id='test_score_results_{$testId}'>";
            
            $html .= "<div class='grid_12'>";
            $html .= "<div class='grid_11'>";
            
            if ($overallDisplayOptions['customHelpBubble']) {
                // This sucks, but we have to repeat what the HelpBubble view helper already does
                // in adding JS/CSS. But necessary because we don't have framework for viewhelpers
                // doing their business in different DHTML/HTML modes.
                $bubble .= "<script type='text/javascript'>
			      $('#testHelpText').cluetip({activation: 'click',
				    local:true, 
				    cursor: 'pointer',
				    width: 450,
				    cluetipClass: 'jtip',
				    sticky: true,
				    closePosition: 'title',
				    closeText: '<img width=\"25\" height=\"25\" src=\"/images/icons/delete.png\" alt=\"close\" />'});
			      </script>";
                $bubble .= $this->view->HelpBubble('testHelpText', 'Information about this test', $overallDisplayOptions['customHelpBubble']);
            } else {
                $bubble = '';
            }
            
            $html .= "<h3 class='section-header'><span class='test-title'>" . $test->test_name . "</span> {$dateText} {$bubble}</h3>";
            $html .= "</div>";
            $html .= "<div class='grid_1'>";
            $html .= "<a href='#' id='create_pdf_{$testId}'><img src='/images/icons/pdf_small_icon.png' class='print_icons'/></a>";
            $html .= "
				<form id='pdfGenerate_{$testId}' method='post' action='/pdf/create-pdf-from-html'>
					<input type='hidden' name='pdfName' value='{$test->test_name} results.pdf' />
					<input type='hidden' id='pdf_contents_{$testId}' name='pdfContents' value='' />
				</form>
				<script>
					$(function(){
						$('#create_pdf_{$testId}').click(function(){
                            // get the HEAD and add absolute paths to CSS links
                            var absolute = window.location.protocol + '//' + window.location.hostname;
                            $('head link').each(function() {
                                // If the link is *not* absolute, then we need to change it
                                if ($(this).attr('href').search('://') == -1) {
                                    var newHref = absolute + $(this).attr('href');
                                    $(this).attr('href', newHref);
                                }
                            });


                            var pdfHtml = escape('<html><head>' + $('head').html() + '</head><body>'
                            + '<h1>' + $('#test_score_results_{$testId} h3.section-header span.test-title').text() + '</h1>'
                            + $('#test_score_results_{$testId} .test_results_table_wrapper').html() + '</body></html>');
							$('#pdf_contents_{$testId}').val(pdfHtml);
							$('#pdfGenerate_{$testId}').submit();
							return false;
						});
					});
				</script>
			";
            
            // only show the link to print all the learning rx if this test has one
            if ($test->show_details) {
                $code = $test->blueprint->id.":".$testId.":".$test->get_test_name();
                $student_ids = array();
                foreach ($results['results'] as $student) {
                    $student_ids[] = $student['student_id'];
                }
                if ($results['start_date']) {
                    $unix_start = strtotime($results['start_date']);
                } else {
                    $unix_start = strtotime('1996-01-01'); //before we started doing testing
                }
                if ($results['end_date']) {
                    $unix_end = strtotime($results['end_date']) + 24 * 60 * 60 - 1; // 1 minute before midnight on the end date
                } else {
                    $unix_end = strtotime(date('Y-m-d')) + 24 * 60 * 60 - 1; // 1 minute before midnight today
                }
    
                $html .= "<a href='#' id='print_learning_rx_link_{$testId}'><img src='/images/icons/icon_rx_pdf.png' class='print_icons'></a>";
            
                $landingPage = Util_HandyServerUtils::get_fisdap_members1_url_root() . "testing/printableResults.html";
            
                $html .= "<form id='print_learning_rx_form_{$testId}' action='{$landingPage}' method='POST'>\n";
                $html .= "<input type='hidden' name='destination' value='prescription' />\n";
                $html .= "<input type='hidden' name='test' value='$code' />\n";
                $html .= "<input type='hidden' name='UnixStartDate' value='$unix_start' />\n";
                $html .= "<input type='hidden' name='UnixEndDate' value='$unix_end' />\n";
                $html .= "<input type='hidden' name='studentArray' value='".serialize($student_ids)."' />\n";
                $html .= "</form>";
            
                $html .= "
					<script>
						$(function(){
							$('#print_learning_rx_link_{$testId}').click(function(){
								$('#print_learning_rx_form_{$testId}').submit();
								return false;
							});
						});
					</script>
				";
            }
            
            $html .= "</div>";
            $html .= "</div>";
            
            $tableId = 'student_test_results_' . $testId;
            $html .= "<div class='test_results_table_wrapper'><table class='student_test_results' id='{$tableId}'>";
            
            $html .= "<thead>";
            
            $html .= "<tr>";
            
            $html .= "<th class='notCenter'>Name</th>";
            $html .= "<th class='notCenter attempt'>Attempt</th>";
            
            foreach ($sections as $sectionName => $sectionCount) {
                $html .= "<th class='stat'>" . $sectionName . "</th>";
            }

            if ($showTotals) {
                $html .= "<th>Total</th>";
            }
            
            // only show the link to learning rx if this test has one
            if ($test->show_details) {
                $html .= "<th>Learning Rx</th>";
            }
            
            $html .= "</tr>";
            $html .= "</thead>";
            
            $scoreRollups = array();
            
            $html .= "<tbody>";
            
            $averageTotal = 0;
            
            // Now start printing out the data...
            foreach ($results['results'] as $data) {
                $html .= "<tr>";
                
                $html .= "<td>" . $data['first_name'] . " " . $data['last_name'] . "</td>";
                
                $html .= "<td class='attempt'>" . $data['attempt_number'] . "</td>";
                
                $studentRollup = 0;
                foreach ($sections as $sectionName => $sectionCount) {
                    if (is_array($sectionDisplayOptions) && isset($sectionDisplayOptions[$sectionName]) && $sectionDisplayOptions[$sectionName] != 'default') {
                        if ($sectionDisplayOptions[$sectionName] == 'absoluteNumberOnly') {
                            $html .= $this->dataCellAbsoluteNumberOnly($data[$sectionName]);
                        } elseif ($sectionDisplayOptions[$sectionName] == 'sliderBadToGood'
                        || $sectionDisplayOptions[$sectionName] == 'sliderGoodToBad') {
                            $html .= $this->dataCellSlider($sectionDisplayOptions[$sectionName], $data[$sectionName]);
                        }
                    } else {
                        $html .= $this->dataCellStandard($data[$sectionName], $sectionCount);
                    }
                    $studentRollup += $data[$sectionName];
                    $scoreRollups[$sectionName] += $data[$sectionName];
                }

                $studentScore = round(($studentRollup / array_sum($sections))*100);

                if ($showTotals) {
                    $html .= "<td class='student_table_centered_text'>" . $studentRollup . "/" . array_sum($sections) . "<br />" . $studentScore . "%</td>";
                }

                $averageTotal += $studentRollup;
                
                // only show the link to learning rx if this test has one
                if ($test->show_details) {
                    $baseURL = '/oldfisdap/redirect/?loc=testing/scoreDetails.html?';
                    $baseURL .= "student={$data['student_id']}";
                    $baseURL .= ";attempt_id={$data['attempt_id']}";
                    $baseURL .= ";test_id={$test->moodle_quiz_id}";
                    $html .= "<td class='student_table_centered_text'><a href='{$baseURL}'><img src='/images/icons/icon_rx.png' /></a></td>";
                }
                
                $html .= "</tr>";
            }
            
            // End of the body... Rest is footer (and won't be sorted along with the data).
            $html .= "</tbody>";
            
            // Print out the table footer for the overall score averages...
            $html .= "<tr class='averages'>";
            $html .= "<td colspan='2'>Grade Averages:</td>";
            
            foreach ($sections as $sectionName => $sectionCount) {
                $tests = count($results['results']);
                $sectionAvg = round($scoreRollups[$sectionName]/$tests);

                if (is_array($sectionDisplayOptions) && isset($sectionDisplayOptions[$sectionName]) && $sectionDisplayOptions[$sectionName] != 'default') {
                    if ($sectionDisplayOptions[$sectionName] == 'absoluteNumberOnly') {
                        $html .= $this->dataCellAbsoluteNumberOnly($sectionAvg);
                    } elseif ($sectionDisplayOptions[$sectionName] == 'sliderBadToGood'
                  || $sectionDisplayOptions[$sectionName] == 'sliderGoodToBad') {
                        $html .= $this->dataCellSlider($sectionDisplayOptions[$sectionName], $sectionAvg);
                    }
                } else {
                    $html .= $this->dataCellAverage($scoreRollups[$sectionName], ($sectionCount * $tests));
                }
            }

            if ($showTotals) {
                //$averageTotal = round($averageTotal / count($results['results']));
                // print the overall student average score...
                $html .= "<td class='student_table_centered_text'>";
                $html .= round(($averageTotal/(array_sum($sections)* count($results['results'])))*100) . '%';
                $html .= "</td>";
            }
            
            $serializedData = urlencode(serialize($results['groupData']));
            
            // only show the link to learning rx if this test has one
            if ($test->show_details) {
                $html .= "<td class='student_table_centered_text'>";
                $html .= "<a href='#' id='group_learning_rx_link_{$testId}'><img src='/images/icons/icon_group.png'></a>";
            
                $landingPage = Util_HandyServerUtils::get_fisdap_members1_url_root() . "setSessionVars.php";
            
                $html .= "<form id='group_learning_rx_form_{$testId}' action='{$landingPage}' method='POST'><input type='hidden' name='redirectTo' value='testing/groupDetails.html?group_id=" . $results['groupData']['group_id'] . "' /><input type='hidden' name='data' value='{$serializedData}'/></form>";
            
                $html .= "
					<script>
						$(function(){
							$('#group_learning_rx_link_{$testId}').click(function(){
								$('#group_learning_rx_form_{$testId}').submit();
								return false;
							});
						});
					</script>
				";
                $html .= "</td>";
            }
            
            $html .= "</tr>";
            $html .= "</table>";
            $html .= "</div></div>";
        } else {
            $html .= "<div class='clear'></div>";
            $html .= "<div class='test_results_container island'>";
            $html .= "<h3 class='section-header'>" . $test->test_name . " {$dateText}</h3>";
            $html .= "<div class='error'>No results were found for this request.</div>";
            $html .= "</div>";
        }
        
        return $html;
    }

    // Display test score TD in the standard way, conventional for test score retrieval in fisdap
    private function dataCellStandard($score, $max)
    {
        $line1 = $score . '/' . $max;
        $line2 = ($max>0)?round(($score/$max)*100).'%':'N/A';

        return "<td class='student_table_centered_text stat'>{$line1} <br /> {$line2}</td>";
    }

    /**
     * Display test score TD in the average column way, displays less information that standard.
     *
     * @param $score
     * @param $max
     * @return string
     */
    private function dataCellAverage($score, $max)
    {
        $line1 = ($max>0)?round(($score/$max)*100).'%':'N/A';

        return "<td class='student_table_centered_text stat'>{$line1}</td>";
    }

    // Display a test score TD with only one number, the score (absolute, not percentage)
    private function dataCellAbsoluteNumberOnly($score)
    {
        return "<td class='student_table_centered_text stat'>{$score}</td>";
    }

    // Display a test score TD with a visual slider (non-interactive) that shows the score on a continuum
    // $mode is either "sliderBadToGood" or "sliderGoodToBad" which changes the direction of slider's scale
    // right now this method assumes the scale is 0-100, $score will be a number in that range
    private function dataCellSlider($mode, $score)
    {
        if ($mode == 'sliderGoodToBad') {
            $modeClass = 'good-to-bad';
        } else {
            $modeClass = 'bad-to-good';
        }

        return "<td class='student_table_centered_text stat'>
            <div class='score-slider " . $modeClass . "'><div class='slider-indicator' style='width: " . intval($score) . "%;'>&nbsp</div></div>
          </td>";
    }
}
