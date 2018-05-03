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
 * This helper will display a list of the goal reports in scrollable tables
 */

/**
 * @package Reports
 * @author Maciej
 */
class Reports_View_Helper_GoalResultsMultipleStudents extends Zend_View_Helper_Abstract 
{
	/**
	 * @var string the html to be rendered
	 */
	protected $_html;
	
	public function goalResultsMultipleStudents($goal, $goalsResults, $heading)
	{
		$this->_html='';
		
		// get goal set:
		//$anyGoal = current(current(current($goalsResults)));
		//$goalSet = $anyGoal->goal->goalSet;
		$goalSet = $goal->goalSet;
		
		// goal set heading
		$this->_html .= $this->view->partial('goal/goal-set-heading.phtml', array(
			'goalSet' => $goalSet,
			'heading' => $heading,
			'studentCount' => count($goalsResults),
		));
		
		// goal categories
		$anyStudentsResults = current($goalsResults);
		foreach ($anyStudentsResults as $goalCategory => $vals) {
			$goalCategories[] = $goalCategory;
		}
		
		// Sort the categories so they appear in the correct order (alphabetically)...
		asort($goalCategories);
		
		foreach ($goalCategories as $goalCategory) {
			$this->_html .= $this->view->partial('goal/goals-results-multiple-students.phtml', array(
				'goalsResults' => $goalsResults,
				'goalCategory' => $goalCategory,
				'goalSet' => $goalSet,
			));
		}
		
		$this->_html .= '</div></div>';
		
		return $this->_html;
	}
}
?>