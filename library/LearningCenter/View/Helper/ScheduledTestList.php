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
 * This file contains a view helper to render the scheduled tests list
 */

/**
 * @package LearningCenter
 */
class Fisdap_View_Helper_ScheduledTestList extends Zend_View_Helper_Abstract 
{
	/**
	 * @var string the html to be returned
	 */
	protected $_html; 
	
	/**
	 * @param array $links an array of links
	 * @return string html to render a quick links box
	 */
    public function scheduledTestList($filters = array())
	{
		$stRepos = \Fisdap\EntityUtils::getRepository('ScheduledTestsLegacy');
		$scheduledTests = $stRepos->getFilteredTests($filters);
		
		$this->_html = "<div id='scheduled-tests'>";
		$this->_html .= "
	
		<table class='fisdap-table' id='scheduled-test-list'>
			<thead>
				<tr>
				<td>Date</td>
				<td>Test</td>
				<td>Students</td>
				<td>Contact</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			</thead>
			<tbody>";
			
		foreach ($scheduledTests as $test) {
			$this->_html .= "<tr>";
			$this->_html .= "<td>" . $test->start_date->format('m/d/Y') . "</td>";
			if($test->publish_scores > 0){
				$this->_html .= "<td>" . $test->test->test_name . "</td>";
			}
			else {
				$this->_html .= "<td>" . $test->test->test_name . " - <span class='npublished'>scores not published</span></td>";
			}
			$this->_html .= "<td class='student-cell'>" . count($test->get_scheduled_students()) . "</td>";
			$this->_html .= "<td class='contactCell'>" . $test->contact_name . "</td>";

			$this->_html .= "<td class='action'><a href='/learning-center/test/edit/stid/" . $test->id . "'><img src='/images/icons/edit_black.png'><br />edit</a></td>";
			$this->_html .= "<td class='action'><a href='/learning-center/test/details/stid/" . $test->id . "'><img src='/images/icons/details_black.png'><br />details</a></td>";

			
			$this->_html .= "<td class='action'><a href='/learning-center/index/retrieve/stid/" . $test->id . "'><img src='/images/icons/scores_black.png'><br />scores</a></td>";

			
			$this->_html .= "<td class='action'><a href='/learning-center/test/delete/stid/" . $test->id . "'><img src='/images/icons/delete-test_black.png'><br />delete</a></td>";

			$this->_html .= "</tr>";
		}

		$this->_html .= "</tbody></table>";
		$this->_html .= "</div>";
	
		return $this->_html;
    }
}