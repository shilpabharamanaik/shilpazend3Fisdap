<?php

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *                                                                           *
 *        Copyright (C) 1996-2011.  This is an unpublished work of           *
 *                         Headwaters Software, Inc.                         *
 *                            ALL RIGHTS RESERVED                            *
 *        This program is a trade secret of Headwaters Software, Inc. and    *
 *        it is not to be copied, distributed, reproduced, published, or     *
 *        adapted without prior authorization of Headwaters Software, Inc.   *
 *                                                                           *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * Helper to create a link to an eval.
 */

/**
 * @package Fisdap
 */
class Zend_View_Helper_EvalHookWidget extends Zend_View_Helper_Abstract
{

    /**
     * @param string $context What kind of hook this is
     * @param object $skill The skill that has been performed
     * @param Boolean $useSmileyIcons Flag to determine whether or not to use
     * the smiley icons.  Defaults to false, needs to be explicitely turned on.
     *
     * @return string the html to render
     */
    public function evalHookWidget($userId, $shiftId, $hookId)
    {
        $this->view->headLink()->appendStylesheet('/css/default/oldfisdap/eval-hook.css');
        
        $this->_html = '';
        
        // Update this to the real hook ID at some point...
        $evals = \Fisdap\EntityUtils::getRepository("EvalDefLegacy")->getEvalsByHook($hookId, \Fisdap\Entity\User::getLoggedInUser()->getProgramId());
        
        if ($shiftId > 0) {
            $this->_html .= "<h2 class='section-header'>Shift Evaluations</h2>";
        }

        $this->_html .= "<div id='eval-hook-widget-container'>";
        $this->_html .= "<div class='eval-subheader'>Choose evaluation: </div>";

        $this->_html .= "<select id='eval-list'>";
        $evalDefIds = array(); // need this for query for completed evals, below
        foreach ($evals as $eval) {
            $this->_html .= "<option value='" . $eval['id'] . "'>" . $eval['name'] . "</option>";
            $evalDefIds[] = $eval['id'];
        }
        $this->_html .= "</select>";

        $this->_html .= "<div class='blue-button extra-small'><button id='open-eval-btn'>Go</button></div>";
        $this->_html .= "</div>";
        $this->_html .= "<div class='clear'></div>";

        // get completed evals based on shiftid, user and eval definitions
        $completedEvals = \Fisdap\Entity\EvalSessionLegacy::getUsersCompletedEvals($shiftId, null, $userId, $evalDefIds);
        
        // if this is global shift hook, add all the unhooked evals from legacy
        if ($hookId == 113 || $hookId == 114 || $hookId == 115) {
            // a hookId of -1 will get us all the evals that were linked to this shift from the 'Add one' hook in legacy
            $legacyEvals = \Fisdap\Entity\EvalSessionLegacy::getUsersCompletedEvals($shiftId, -1, $userId, null);
            $completedEvals = array_merge($completedEvals, $legacyEvals);
        }
        
        // add the "completed evals" section if there are any completed evals
        if (count($completedEvals) > 0) {
            $this->_html .= "<div class='eval-subheader'>Completed evaluations</div>";
            $this->_html .= "<table class='fisdap-table' id='eval-table'>";

            $userContext = \Fisdap\Entity\User::getLoggedInUser()->getCurrentUserContext();
            
            foreach ($completedEvals as $eval) {
                $passFailText = "";
                if ($eval->eval_def->show_pass_fail) {
                    if ($eval->passed) {
                        $passFailText = "(passed)";
                    } else {
                        $passFailText = "(failed)";
                    }
                }
                $this->_html .= "<tr class='eval-row'>";
                if ($eval->userCanEdit($userContext)) {
                    $this->_html .= "<td class='eval-title-column'><a href='#' eval-session-id='" . $eval->id . "' class='edit-eval-link'>" . $eval->eval_def->eval_title . " " . $passFailText . "</a></td>";
                    $this->_html .= "<td class='eval-delete-column'>
                                        <div class='action-cell'>
                                            <a href='#' eval-session-id='" . $eval->id . "' class='delete-eval-link'>
                                                <img class='tiny-icon square' src='/images/icons/delete.svg'>
                                            </a>
                                        </div>
                                    </td>";
                } else {
                    $this->_html .= "<td class='eval-title-column'>" . $eval->eval_def->eval_title . " " . $passFailText . "</td>";
                }
                $this->_html .= "</tr>";
            }
            $this->_html .= "</table>";
        }
        
        $baseURL = "/oldfisdap/eval-hook/hid/$hookId/sid/$shiftId";
        
        $this->_html .= "
			<script>
				$(function(){
				    $('#eval-list').chosen();

					$('.edit-eval-link').each(function(index, el){
						$(el).click(function(){
							window.open('$baseURL/proc/edit/aid/' + $(this).attr('eval-session-id'), '_blank', 'width=1020,height=700');
							return false;
						});
					});
					
					$('.delete-eval-link').click(function(e) {
					    e.preventDefault();
                        var cell = $(this).closest('.action-cell');
                        var evalId = $(this).attr('eval-session-id');
                        var row = $(this).parents('tr.eval-row');
                        var evalTable = row.parents('#eval-table');
                        var evalHeader = evalTable.siblings('.eval-subheader');

					    // create the function that will happen once the countdown is complete
                        var deleteEvalAction = function() {
                            blockUi(true, $('#eval-table'), 'throbber');
                            $.post('/evals/delete-eval',
                                {'evalId': evalId },
                                function (resp) {
                                    blockUi(false, $('#eval-table'));
                                    if (resp === true) {
                                        // remove the eval from the table
                                        $(row).slideUp().remove();

                                        // if there are no other evals, remove the table and header
                                        if (evalTable.find('tr').length === 0) {
                                            evalTable.fadeOut();
                                            evalHeader.fadeOut();
                                        }

                                    } else if (!resp) {
                                        $(cell).html('An error has occured.');
                                    } else {
                                        $(cell).html(resp);
                                    }
                                }
                            );
                        };

                        // set up countdown
                        delayedAction(cell,  deleteEvalAction, 'deleteEval');
					});

                    $('#open-eval-btn').button();
					$('#open-eval-btn').click(function(){
						window.open('$baseURL/proc/add/aid/' + $('#eval-list').val(), '_blank');
					});
				});
			</script>
		";
        return $this->_html;
    }
}
