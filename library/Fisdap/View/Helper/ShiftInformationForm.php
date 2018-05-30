<?php

/* ***************************************************************************
 *
 *         Copyright (C) 1996-2013.  This is an unpublished work of
 *                          Headwaters Software, Inc.
 *                             ALL RIGHTS RESERVED
 *         This program is a trade secret of Headwaters Software, Inc.
 *         and it is not to be copied, distributed, reproduced, published,
 *         or adapted without prior authorization
 *         of Headwaters Software, Inc.
 *
 * ************************************************************************** */

/**
 * Shift information / Educational setting form widget
 * @package Fisdap
 * @author jmortenson
 */
class Fisdap_View_Helper_ShiftInformationForm extends Zend_View_Helper_Abstract
{
    public $user;
    
    /**
     * This view helper optionally takes an array of options:
     * $options = array(
        selected => array(  // set values that should be initially selected on the form (defaults)
            sites => array
            types => array
            startDate => string
            endDate => string
            auditStatus => string
        ),
        pickSiteType => boolean, // should site type field be shown?
        pickDateRange => boolean, // should the date range fields be shown?
        pickPatientType => boolean, // should the patient type field be shown?
        pickAuditStatus => boolean, // should the audited toggles be shown? <= DEFAULT FALSE
        siteTypes => array, // which site types should show up in the site selector, default all
     )
     */
    public function ShiftInformationForm($config, $options = array())
    {
        // set default/initial selected values for fields
        $defaultFields = array('sites_filters' => 'sites',
                               'patient_filters' => 'types',
                               'startDate' => 'startDate',
                               'endDate' => 'endDate',
                               'auditStatus' => 'auditStatus');
        
        foreach ($defaultFields as $config_key => $field) {
            // if a config is set, use those values for selected fields
            if (!empty($config)) {
                $options['selected'][$field] = $config[$config_key];
            } elseif (!isset($options['selected'][$field])) {
                // otherwise use the default defaults
                switch ($field) {
                    case "sites":
                        $options['selected'][$field] = array('0-Clinical', '0-Field');
                        break;
                    case "types":
                        $options['selected'][$field] = array(1);
                        break;
                    case "startDate":
                        // default startDate should be 6 months ago
                        $sixmonths = new DateTime('now');
                        $sixmonths->modify('-6 months');
                        //$options['selected'][$field] = $sixmonths->format('m/d/Y');
                        $options['selected'][$field] = null;
                        break;
                    case "endDate":
                        //$options['selected'][$field] = date('m/d/Y');
                        $options['selected'][$field] = null;
                        break;
                    case "auditStatus":
                        $options['selected'][$field] = 'all';
                        break;
                }
            } elseif ($field == 'startDate' || $field == 'endDate') {
                // selected dates get set specially
                $date =  new DateTime($options['selected'][$field]);
                $options['selected'][$field] = $date->format('m/d/Y');
            }
        }
        
        $this->user = \Fisdap\Entity\User::getLoggedInUser();
        
        // JS / CSS for the widget
        $this->view->headScript()->appendFile("/js/jquery.chosen.relative.js");
        $this->view->headLink()->appendStylesheet("/css/jquery.chosen.css");
        $this->view->headLink()->appendStylesheet('/css/library/Fisdap/View/Helper/shift-information-form.css');
        $this->view->headScript()->appendFile("/js/library/Fisdap/View/Helper/shift-information-form.js");
        
        // THE ACTUAL HTML
        if (!isset($options['pickSiteType']) || $options['pickSiteType']) {
            $types = (isset($options['siteTypes'])) ? $options['siteTypes'] : array("Clinical", "Field", "Lab");
            $sitesElement = "
			<div id='site-filter' class='grid_6 input-section'>
				<label for='sites_filters'>Location:</label>
				<div id='site_filters-element'>
				    <select name='sites_filters[]' id='sites_filters' multiple='multiple' class='chzn-select fancy-input' data-placeholder='All locations...'>".
                        $this->formatSiteOptions($this->getSiteOptions($types), $options['selected']['sites']) ."
					</select>
				</div>
			</div>
			";
        }
        
        if (!isset($options['pickDateRange']) || $options['pickDateRange']) {
            $dateElements = "
			<div id='date-range' class='grid_6 input-section'>
			    <div id='date_range-element'>
			        <label for='startDate'>From: </label>
					<input name='startDate' type='text' id='startDate' value='" . $options['selected']['startDate'] . "' class='selectDate fancy-input'>
					<label for='endDate'>Through: </label>
					<input name='endDate' type='text' id='endDate' value='" . $options['selected']['endDate'] . "' class='selectDate fancy-input'>
			    </div>
			</div>";
        }
        
        if (!isset($options['pickPatientType']) || $options['pickPatientType']) {
            $patientTypeElement = "
			<div id='patient-filter' class='grid_6 input-section'>
				<label for='patient_filters'>Patient type:</label>
				<div id='patient_filters-element'>
				    <select name='patient_filters[]' id='patient_filters' multiple='multiple' class='chzn-select fancy-input' data-placeholder='All patient types...'>".
                        $this->formatPatientTypeOptions($this->getPatientTypes(), $options['selected']['types']) . "
					</select>
				</div>
			</div>
			";
        }
        
        // NOTE: audit status is the ONLY element that defaults to off
        if (isset($options['pickAuditStatus']) && $options['pickAuditStatus']) {
            $checkAll = ($options['selected']['auditStatus'] == 'all') ? "checked='checked'" : "";
            $checkAudited = ($options['selected']['auditStatus'] == 'audited') ? "checked='checked'" : "";
            $auditStatusElement = "
			<div id='audit-status-filter' class='grid_6 input-section'>
				<label for='audit-status-filters'>Shift status:</label>
				<div class='audit-status-buttonset extra-small'>
					<input type='radio' id='audit-status_all' name='audit-status-filters' $checkAll><label for='audit-status_all'>All</label>
					<input type='radio' id='audit-status_audited' name='audit-status-filters' $checkAudited><label for='audit-status_audited'>Audited</label>
				</div>
				<input type='hidden' id='auditStatus' name='auditStatus' value='".$options['selected']['auditStatus']."'>
			</div>
			";
        }
        
        return 	$sitesElement .
                $dateElements .
                "<div class='clear'></div>".
                $patientTypeElement .
                $auditStatusElement;
    }
    
    private function getSiteOptions($types)
    {
        $active = null;
        
        $siteOptions = \Fisdap\EntityUtils::getRepository('SiteLegacy')->getFormOptionsByProgram($this->user->getProgramId(), null, "name", "DESC", $active);
        $actualOptions = array();
  
        foreach ($siteOptions as $type_name => $site_type) {
            foreach ($site_type as $site_id => $site_name) {
                if (!$actualOptions[$type_name]) {
                    $actualOptions[$type_name] = array();
                }
                $actualOptions[$type_name][$site_id] = $site_name;
            }
        }
        
        $siteOptions = $actualOptions;
        
        $sorted_options = array();
        
        foreach ($types as $type) {
            // if there are options of this type, add them
            if (is_array($siteOptions[$type])) {
                $key = "0" . "-" . $type;
                
                $sorted_options[$type] = array();
                $sorted_options[$type][$key] = "All " . $type ." Sites";
                
                foreach ($siteOptions[$type] as $id => $site) {
                    $sorted_options[$type][$id] = $site;
                }
            }
        }
        
        return $sorted_options;
    }
    /**
     * Format the array of optgroups => options
     */
    private function formatSiteOptions($options, $selectedSites)
    {
        if ($selectedSites == null) {
            $selectedSites = array();
        }
        
        $html = '';
        foreach ($options as $key => $value) {
            $html .= "<optgroup id='siteOptionsOptgroup-" . $key . "' label='" . $key . "'>\n";
            foreach ($value as $siteID => $label) {
                $selected = (in_array($siteID, $selectedSites)) ? "selected" : "";
                $html .= "<option value='" . $siteID . "' $selected>" . $label . "</option>\n";
            }
            $html .= "\n</optgroup>";
        }
        return $html;
    }
    
    private function getPatientTypes()
    {
        $patientTypes = \Fisdap\EntityUtils::getRepository('Subject')->findAll();
        
        $options = array();
        foreach ($patientTypes as $type) {
            $options[$type->id] = $type->name  . ' (' . $type->type . ')';
        }
        
        return $options;
    }
    private function formatPatientTypeOptions($options, $selectedTypes)
    {
        if ($selectedTypes == null) {
            $selectedTypes = array();
        }
        
        $html = '';
        foreach ($options as $key => $value) {
            $selected = (in_array($key, $selectedTypes)) ? "selected" : "";
            $html .= "<option value='" . $key . "' $selected>" . $value . "</option>\n";
        }
        
        return $html;
    }
    
    /**
     * This view helper optionally takes an array of options:
     * $options = array(
        selected => array(  // set values that should be initially selected on the form (defaults)
            sites => array
            types => array
            startDate => string
            endDate => string
            auditStatus => string
        ),
        pickSiteType => boolean, // should site type field be shown?
        pickDateRange => boolean, // should the date range fields be shown?
        pickPatientType => boolean, // should the patient type field be shown?
        pickAuditStatus => boolean, // should the audited toggles be shown? <= DEFAULT FALSE
     )
     */
    public function ShiftInformationFormSummary($options = array(), $config = array())
    {
        $summary = array();
        if (!isset($options['pickSiteType']) || $options['pickSiteType']) {
            $chosenSites = $config['sites_filters'];
            $summary["Location(s)"] = $this->formatSites($chosenSites);
        }
        
        if (!isset($options['pickPatientType']) || $options['pickPatientType']) {
            $chosenTypes = $config['patient_filters'];
            $summary["Patient type(s)"] = $this->formatTypes($chosenTypes);
        }
        
        if (!isset($options['pickDateRange']) || $options['pickDateRange']) {
            $summary["Date range"] = $this->formatDateRange($config['startDate'], $config['endDate']);
        }
        
        // NOTE: audit status is the ONLY element that defaults to off
        if (isset($options['pickAuditStatus']) && $options['pickAuditStatus']) {
            $summary["Shift status"] = ($config['auditStatus'] == 'all') ? "All shifts" : "Audited shifts only";
        }
        
        return $summary;
    }
    
    public function shiftInformationFormValidate($options, $config)
    {
        $errors = array();
        // validate date ranges
        $startDate = $config["startDate"];
        $endDate = $config["endDate"];
        if ($startDate && $endDate) {
            $start = strtotime($startDate);
            $end = strtotime($endDate);
            if ($start > $end) {
                $errors["startDate"][] = "Start date must be before end date.";
            }
        }
        
        if ($startDate && !\Util_String::isValidDate($startDate)) {
            $errors["startDate"][] = "Please enter a valid start date in mm/dd/yyyy format.";
        }
        
        if ($endDate && !\Util_String::isValidDate($endDate)) {
            $errors["endDate"][] = "Please enter a valid end date in mm/dd/yyyy format.";
        }
        
        return $errors;
    }
    
    /**
     * processes the chosen results from the sites filter to return a summary of chosen sites
     * @todo refactor into SiteService (see getTypeIds)
     */
    public function formatSites($chosen)
    {
        $site_ids = array();
        
        // no sites selected means give them all sites
        if (is_null($chosen)) {
            return "All locations";
        }
        
        // if we're here, something was selected, so loop through the selected stuff
        $locations = array();
        foreach ($chosen as $site_id) {
            // just add regular ids to the list
            if (is_numeric($site_id)) {
                $site_ids[] = $site_id;
            }
            
            // for the "all" options
            else {
                $option = explode("-", $site_id);
                $type = $option[1];
                $locations[$type] = "All $type sites";
            }
        }
        
        if (count($site_ids) == 1) {
            $locations['other'] = \Fisdap\EntityUtils::getEntity('SiteLegacy', $site_ids[0])->name;
        } elseif (count($site_ids) > 1) {
            $locations['other'] = count($site_ids)." sites";
        }
        ksort($locations);
        
        return ucfirst(implode(", ", $locations));
    }
    
    /**
     * processes the chosen results from the date pickers to return a date range summary
     */
    public function formatDateRange($start_date, $end_date)
    {
        // no dates selected means give them all dates
        if (empty($start_date) && empty($end_date)) {
            return "All dates";
        }
        
        // no end date selected means give them all dates from start date
        if (empty($end_date)) {
            return "From ".date('F j, Y', strtotime($start_date));
        }
        
        // no start date selected means give them all dates through end date
        if (empty($start_date)) {
            return "Through ".date('F j, Y', strtotime($end_date));
        }
        
        // both dates selected means give them the date range
        return date('F j, Y', strtotime($start_date)) . " through " . date('F j, Y', strtotime($end_date));
    }
    
    /**
     * processes the chosen results from the patient type picker to return a patient type summary
     * @todo refactor into SubjectService (see getTypeIds)
     */
    public function formatTypes($chosen_types)
    {
        // no types = all types
        if (is_null($chosen_types)) {
            return "All patient types";
        }
        
        $chosenOptions = array();
        $patientTypeOptions = \Fisdap\EntityUtils::getRepository('Subject')->findAll();
        foreach ($patientTypeOptions as $type) {
            if (in_array($type->id, $chosen_types)) {
                $chosenOptions[] = $type->name  . ' (' . $type->type . ')';
            }
        }
        
        return implode(", ", $chosenOptions);
    }
    
    /**
     * processes the chosen results from the patient types filter to return a clean array of type ids
     */
    public function getTypeIds($chosen)
    {
        $subjectRepository = \Fisdap\EntityUtils::getRepository('Subject');
        $subjectService = new \Fisdap\Service\CoreSubjectService();

        $type_ids = $subjectService->makeSubjectIdsArray($subjectRepository, $chosen);
        
        return $type_ids;
    }
}
