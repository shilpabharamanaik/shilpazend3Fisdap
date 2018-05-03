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
 * This helper will display a list of the interventions tied to a particular
 * patient or shift.
 */

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_InterventionList extends Zend_View_Helper_Abstract 
{
	/**
	 * @var string the html to be rendered
	 */
	protected $_html;
	
	/**
	 * @var Array containing all valid modal types.
	 */
	public static $validModals = array("Iv", "Med", "Other", "Airway", "Vital", "Cardiac");
	
	
	public function interventionList($patientId = null, $shiftId = null, $includeModals = false, $shiftType = null, $includedModals = null, $source = "SkillsTracker")
	{
		// Default to showing all valid modals.
		if(!is_array($includedModals)){
			$includedModals = self::$validModals;
		}
		
        // Include a different intervention list JS file here if the source is Exchange.
        if($source == "Exchange"){
        	$this->view->headScript()->appendFile("/js/library/Exchange/View/Helper/intervention-list.js");
        	$this->view->headLink()->appendStylesheet("/css/library/Exchange/View/Helper/intervention-list.css");
        // Default to the original SkillsTracker one.
        }else{
        	$this->view->headScript()->appendFile("/js/library/SkillsTracker/View/Helper/intervention-list.js");
        	$this->view->headLink()->appendStylesheet("/css/library/SkillsTracker/View/Helper/intervention-list.css");
        }
		
		if ($patientId) {
			$shift = \Fisdap\EntityUtils::getEntity('Patient', $patientId)->shift;
		} else if ($shiftId) {
			$shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);
		}
		
		//if we don't know what the shift is yet, use the shiftType passed in above
		if ($shift->id) {
			$shiftType = $shift->type;
		}
		
		switch ($shiftType) {
			case "field":
				$buttonClass = "blue-button";
				break;
			case "clinical":
				$buttonClass = "green-buttons";
				break;
			case "lab":
				$buttonClass = "yellow-button";
				break;
		}
        
        $patient = \Fisdap\EntityUtils::getEntity('Patient', $patientId);
		$this->_html .= "<div id='intervention-list' class='grid_12'>";
		$this->_html .= $this->view->formHidden('patientId', $patientId);
		$this->_html .= $this->view->formHidden('shiftId', $shiftId);
        $this->_html .= "<div class='grid_3 $buttonClass'>";
        
        $this->_html .= $this->renderButtons($includedModals);
        
        $this->_html .= "</div>";

        $skills = array();
        
        if ($patientId) {
        	$skills = \Fisdap\EntityUtils::getRepository('Patient')->getSkillsByPatient($patientId, $source);
        } else if ($shiftId) {
        	$skills = \Fisdap\EntityUtils::getRepository('ShiftLegacy')->getSkillsByShift($shiftId, array('shiftOnly' => true));
        }
        
        $this->_html .= $this->renderSkillsTable($skills, $source);
        
        $this->_html .= "</div>";
        
		if($includeModals !== false){
			$this->_html .= $this->getModalFormsHtml($includedModals);
		}			
		
		return $this->_html;
	}
	
	private function renderSkillsTable($skills, $source){
		$html = "";
		
		if($source == "Exchange"){
			$priorityNames = array(1 => "Not Important", 2 => "Important", 3 => "Essential");
			$prioritizedSkillRecords = array(1 => "", 2 => "", 3 => "");
			
			foreach($skills as $skill){
				$viewscript = $skill->getViewScriptName();
				
				if($viewscript != "vital"){
					$skillHtml = $this->view->partial($viewscript . "Cell.phtml", "exchange", array($viewscript => $skill)); 
				
					$prioritizedSkillRecords[$skill->priority] .= $skillHtml;
				}
			}
			
			$html .= '<div class="grid_9">';
			$html .= '<p id="intervention-help">You can add interventions here. Click on a skill button to the left to get started.</p>';
			
			// Print out 3 tables here, one for each priority.
			for($i = 3; $i>=1; $i--){
				$html .= '
					<table id="intervention-table_' . $i . '" class="intervention-table priority_drop_target" data-priority="' . $i . '">
						<colgroup>
							<col class="procedure-grabby-column" />
							<col class="procedure-icon-column" />
							<col class="procedure-description-column" />
							<col class="procedure-tools-column" />
						</colgroup>
				';
				
				$html .= "
					<thead>
						<tr><td colspan='4' class='priority_header'>" . $priorityNames[$i] . "</td></tr>
					</thead>
				";
				
				$html .= '<tbody>';
				
				$html .= $prioritizedSkillRecords[$i];
				
				$html .= '</tbody></table> <br /><br />';
			}
			
			$html .= "</div>";
		}else{
			$html .= '
				<div class="grid_9">
					<p id="intervention-help">You can add interventions here. Click on a skill button to the left to get started.</p>
					<table id="intervention-table" class="intervention-table">
						<colgroup>
							<col class="procedure-grabby-column" />
							<col class="procedure-icon-column" />
							<col class="procedure-description-column" />
							<col class="procedure-tools-column" />
						</colgroup>
						<tbody>';
			
			foreach ($skills as $skill) {
				$viewscript = $skill->getViewScriptName();
				$html .= $this->view->partial($viewscript . "Cell.phtml", "skills-tracker", array($viewscript => $skill));
			}
			
			$html .= '</tbody></table></div>';
		}
		
		return $html;
	}
	
	private function renderButtons($includedModals){
		$html = "";
		
		// Not really a clever way of doing it, but make sure each button should be included in the intervention list interface.
		if($includedModals === true || (is_array($includedModals) && in_array('Vital', $includedModals))){
			$html .= "<a href='#' class='interventions-btn' id='vitals-btn'><img class='small-skill-icon' src='/images/icons/vitals_white.png'>Vitals</a>";
		}
		
		if($includedModals === true || (is_array($includedModals) && in_array('Airway', $includedModals))){
			$html .= "<a href='#' class='interventions-btn' id='airway-btn'><img class='small-skill-icon' src='/images/icons/airway_white.png'>Airway</a>";
		}
		
		if($includedModals === true || (is_array($includedModals) && in_array('Cardiac', $includedModals))){
			$html .= "<a href='#' class='interventions-btn' id='cardiac-btn'><img class='small-skill-icon' src='/images/icons/cardiac_white.png'>Cardiac</a>";
		}
		
		if($includedModals === true || (is_array($includedModals) && in_array('Iv', $includedModals))){
			$html .= "<a href='#' class='interventions-btn' id='iv-btn'><img class='iv-skill-icon' src='/images/icons/iv_white.png'>Venous Access</a>";
		}
		
		if($includedModals === true || (is_array($includedModals) && in_array('Med', $includedModals))){
			$html .= "<a href='#' class='interventions-btn' id='meds-btn'><img class='small-skill-icon' src='/images/icons/med_white.png'>Meds</a>";
		}
		
		if($includedModals === true || (is_array($includedModals) && in_array('Other', $includedModals))){
			$html .= "<a href='#' class='interventions-btn' id='other-btn'><img class='small-skill-icon' src='/images/icons/other_white.png'>Other</a>";
		}
		
		return $html;
	}
	
	public static function getModalFormsHtml($includedModals)
	{
		$html = "";
		
		foreach(self::$validModals as $modalName){
			if($includedModals === true || (is_array($includedModals) && in_array($modalName, $includedModals))){
				$modalClassName = "SkillsTracker_Form_" . $modalName . "Modal";
				$html .= new $modalClassName();
			}
		}
		
//		$html .= new SkillsTracker_Form_IvModal();
//		$html .= new SkillsTracker_Form_MedModal();
//		$html .= new SkillsTracker_Form_OtherModal();
//		$html .= new SkillsTracker_Form_AirwayModal();
//		$html .= new SkillsTracker_Form_VitalModal();
//		$html .= new SkillsTracker_Form_CardiacModal();
		
		return $html;
	}
}