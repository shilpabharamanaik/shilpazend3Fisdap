<?php

/* ***************************************************************************
 *
 *         Copyright (C) 1996-2014.  This is an unpublished work of
 *                          Headwaters Software, Inc.
 *                             ALL RIGHTS RESERVED
 *         This program is a trade secret of Headwaters Software, Inc.
 *         and it is not to be copied, distributed, reproduced, published,
 *         or adapted without prior authorization
 *         of Headwaters Software, Inc.
 *
 * ************************************************************************** */

/**
 * table to select a goal set and link to editing goalsets
 * @package Fisdap
 * @author khanson
 */
class Fisdap_View_Helper_GoalSetTable extends Zend_View_Helper_Abstract
{
    public function goalSetTable($config = array(), $options = array(), $selectable = true) {
		$selectableClass = ($selectable) ? "" : "not-selectable";
		$user = \Fisdap\Entity\User::getLoggedInUser();
		if ($user->getCurrentRoleName() == "instructor" && $user->hasPermission("Edit Program Settings")) {
			$canEdit = TRUE;
		} else {
			$canEdit = FALSE;
		}
		$programId = $user->getCurrentProgram()->id;
		$goalSets = \Fisdap\EntityUtils::getRepository('Goal')->getProgramGoalSets($programId, true, (array_key_exists("requiredGoalDefs", $options) ? $options['requiredGoalDefs'] : null));
		
		// figure out what the selected goalset is
		if (isset($config['selected-goalset'])) {
			// use the config goalset if there is one
			$selectedGoalset = $config['selected-goalset'];
		} else if (count($goalSets) == 1) {
			// otherwise, if there's only one goal set, pick that one
			$selectedGoalset = $goalSets[0]->id;
		} else if ($user->getCurrentRoleName() == "student") {
			// otherwise, if this is a student, default to the default goalset for that student's cert level
			$selectedGoalset = $user->getCurrentRoleData()->getGoalSet()->id;
		}
        
        // JS / CSS for the widget
		$this->view->headLink()->appendStylesheet('/css/library/Fisdap/View/Helper/goal-set-table.css');
        $this->view->headScript()->appendFile("/js/library/Fisdap/View/Helper/goal-set-table.js");
		
		$html = "<div id='goalset-table'>";
		
		// build the table!
		if ($canEdit) {
			$html .= "<div class='goalset-buttons'>
						<a id='add-goalset' href='/reports/goal/customize/goalset/new'>Add Goalset</a>
					</div>
					<div class='clear'></div>";
		}
		$html .= "<div class='goalset-table fisdap-table-scrolling-container'>";
		$html .= "<table class='goalset-table fisdap-table scrollable'>";
		$html .= "<tbody>";
		
		// add each of the program's goal sets to the table
		foreach ($goalSets as $goalSet) {
			
			//if there are any exluded goalset templates, get rid of them
			if (array_key_exists("excludeGoalSetTemplates", $options)) {
				if (in_array($goalSet->goalset_template->id, $options['excludeGoalSetTemplates'])) {
					continue;
				}
			}
			
			$default = ($goalSet->default_goalset) ? "default" : "";
			
			if ($goalSet->isStandard()) {
				// we cannot edit or delete standard goal sets
				$edit = "<span class='standard-goalset'>(standard)</span>";
				$delete = "";
			} else {
				$edit = "<a href='/reports/goal/customize/goalset/".$goalSet->id."'>Edit</a>";
				$delete = "<a href='#' data-goalsetid='".$goalSet->id."'><img class='small-icon' src='/images/icons/delete.png'></a>";
			}
			
			$html .= "<tr>";
			$html .= 	"<td class='goalset-name $selectableClass' data-goalsetid='".$goalSet->id."'>".$goalSet->name."</td>";
			$html .= 	"<td class='goalset-cert-level'>".$goalSet->getCertificationLevel()."</td>";
			$html .= 	"<td class='goalset-default'>$default</td>";
			if ($canEdit) {
				$html .= 	"<td class='edit-goalset'>$edit</td>";
				$html .= 	"<td class='delete-goalset'>$delete</td>";
			}
			$html .= "</tr>";
		}
		
		$html .= "</tbody>";
		$html .= "</table>";
		$html .= "</div>";
		$html .= "<input type='hidden' id='selected-goalset' name='selected-goalset' value='".$selectedGoalset."'>";
		
		// if this person can edit, add the deletion confirmation modal
		if ($canEdit) {
			$html .= 	"<div id='deleteGoalsetConfirmationModal'>
							<div class='confirmation-modal-text'>
								Are you sure you want to delete the \"<span class='goalset-name'>selected</span>\" goal set?
							</div>
							<div class='confirmation-modal-buttons'>
								<div class='buttonWrapper small gray-button'>
									<a href='#' id='delete-goalset-cancel'>Cancel</a>
								</div>
								<div class='buttonWrapper small green-buttons'>
									<a href='#' id='delete-goalset-confirm' data-goalsetid=''>Ok</a>
								</div>
							</div>
						</div>";
		}
		$html .= "</div>";
		
        return 	$html;
    }

    /**
     * Validation method used by Reports when this view helper is used as a report configuration form element
     *
     * @param array $options Array of options set for this form component
     * @param array $config Array of configuration data for the report being validated
     *
     * @return array $errors Array of validation errors (if any)
     */
    public function goalSetTableValidate($options = array(), $config = array()) {
        $errors = array();
        if (!isset($config['selected-goalset']) || $config['selected-goalset'] == '') {
            $errors['selected-goalset'][] = 'You must select a goal set.';
        }

        return $errors;
    }


    public function GoalSetTableSummary($options = array(), $config = array())
    {
		$goalSetId = $config['selected-goalset'];
		$goalSet = \Fisdap\EntityUtils::getEntity('GoalSet', $goalSetId);
		$summary["Goal set"] = $goalSet->name;
		
		return $summary;
	}
}