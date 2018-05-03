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
 * This helper displays the appropriate navigation for the portfolio.
 */

/**
 * @package Portfolio
 */
class Portfolio_View_Helper_PortfolioNavigation extends Zend_View_Helper_Abstract 
{
	/**
	 * @var string the html to be rendered
	 */
	protected $_html;
	
	/**
	 * @return string the navigation HTML
	 */
	public function portfolioNavigation($skillstracker, $compliance)
	{
		$this->view->headLink()->appendStylesheet("/css/library/Portfolio/View/Helper/portfolioNavigation.css");
		
		// Only display the student picker if an instructor is logged in.
		if(\Fisdap\Entity\User::getLoggedInUser()->getCurrentRoleName() == 'instructor'){
			$this->_html .= "<div class='find-student-container island grid_12'>".
								"<h3 class='section-header'>Select a student</h3>".
								$this->view->studentPicker .
							"</div>";
		}
		
		$tabs = array();
		
		// everybody gets the About tab
		$tabs['about'] = array('class' => 'main-tab', 'title' => "About " . $this->view->student->first_name);
		
		if ($compliance) {
			$tabs['compliance'] = array('class' => 'main-tab', 'title' => 'Compliance Status');
		}
		
		if ($skillstracker) {
			$tabs['competency'] = array('class' => 'main-tab', 'title' => 'Overall Competency');
			if(\Fisdap\Entity\User::getLoggedInUser()->getCurrentRoleName() == 'instructor' && \Fisdap\Entity\User::getLoggedInUser()->getCurrentRoleData()->hasPermission("Admin Exams")){
				$tabs['exams'] = array('class' => 'sub-tab', 'title' => 'Exams');
			}
			$tabs['skill-sheets'] = array('class' => 'sub-tab', 'title' => 'Lab Practice');
			$tabs['internship-records'] = array('class' => 'sub-tab', 'title' => 'Internship Records');
			$tabs['affective-evaluations'] = array('class' => 'sub-tab', 'title' => 'Affective Evaluations');
		}
		
		$tabs['attachments'] = array('class' => 'main-tab', 'title' => 'Attachments');
		$tabs['export-options'] = array('class' => 'main-tab', 'title' => 'Export Options');
		
		$this->_html .= "<div class='grid_3 menu'>";
		
		// spit out the tabs
		$requestAction = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
		foreach($tabs as $page => $tab){
			$classes = array('tab', $tab['class']);
			$divId = $page . '-div';
			$active = false;
			
			if ($page == $requestAction || ($page == 'about' && $requestAction == 'index')){
				$active = true;
				$classes[] = 'active-tab';
			} else {
				$this->_html .= "<a href='/portfolio/index/$page' class='tab-link'>";
			}
			
			$this->_html .= "<div id='$divId' class='" . implode(' ', $classes) . "'>";
			$this->_html .= $tab['title'];
			$this->_html .= "</div>";
			
			if (!$active) {
				$this->_html .= "</a>";
			}
		}
		
		$this->_html .= "</div>";
		
		$this->_html .= $javascript;
		
		return $this->_html;
	}
}