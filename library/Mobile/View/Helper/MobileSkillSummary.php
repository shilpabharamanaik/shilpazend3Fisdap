<?php

class Mobile_View_Helper_MobileSkillSummary extends Zend_View_Helper_Abstract
{
    protected $_html;
    
    /**
	 * @param string $title
	 * @param string $action the Controller Action to link to
	 * @param array $skills
	 * @param integer $patientId
	 * @param boolean $isSingularSkill determines whether only one skill is allowed for this summary
	 *
	 * @return string the mobile ticker menu rendered as an html table
	 */
	public function mobileSkillSummary($title, $action, $skills, $patientId, $isSingularSkill = false)
	{
		$patient = \Fisdap\EntityUtils::getEntity('Patient', $patientId);
		
        $this->_html = '<div class="grid_12 skill-grouping">';
        $this->_html .= '<h2 class="page-title" style="float:left;">' . $title . '</h2>';
		
		//Only display the new link if the user is able to edit details about this shift.
		if ($patient->shift->isEditable() && !($isSingularSkill && count($skills) > 0)) {
			$this->_html .= '<a class="new-skill" href="/mobile/patients/' . $action . '/patientId/' . $patientId . '" style="float:right;">+ New</a>';
		}
        $this->_html .= '</div>';
        $this->_html .= '<div class="island">';
        $this->_html .= $this->view->partial('skillsSummary.phtml', array('skill' => $action, 'data' => $skills));
        $this->_html .= '</div>';

        return $this->_html;
    }
}