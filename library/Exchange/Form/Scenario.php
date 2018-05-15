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
 * Preceptor Signoff Form
 */

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class Exchange_Form_Scenario extends Fisdap_Form_Base
{
    private $scenario;
    
    /**
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($scenarioId = null, $options = null)
    {
        $this->scenario = \Fisdap\EntityUtils::getEntity('Scenario', $scenarioId);
        
        parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();
        
        $this->setAttrib('id', 'main_scenario_form');
        
        // Hidden stuff to get stuff to work...
        $patientIdElement = new Zend_Form_Element_Hidden('patient_id');
        $patientIdElement->setValue($this->scenario->patient->id);
        
        $scenarioIdElement = new Zend_Form_Element_Hidden('scenario_id');
        $scenarioIdElement->setValue($this->scenario->id);
        
        $vitalIdElement = new Zend_Form_Element_Hidden('vital_id');
        $vitalIdElement->setValue($this->scenario->patient->vitals[0]->id);
        
        $this->addElements(array($patientIdElement, $scenarioIdElement, $vitalIdElement));
        
        
        // Scenario Details elements
        $scenarioTitle = new Zend_Form_Element_Text('scenario_title');
        $scenarioTitle->setAttrib('size', 40);
        $scenarioTitle->setValue($this->scenario->title);
        
        $scenarioComplaint = new Zend_Form_Element_Select('scenario_complaint');
        $scenarioComplaint->addMultiOptions(\Fisdap\Entity\Complaint::getFormOptions());
        $scenarioComplaint->setValue($this->scenario->patient->complaints[0]->id);
        
        $scenarioState = new Zend_Form_Element_Select('scenario_state');
        $scenarioState->addMultiOptions(\Fisdap\Entity\ScenarioState::getFormOptions());
        $scenarioState->setValue($this->scenario->state->id);
        
        $scenarioNotes = new Zend_Form_Element_Textarea('scenario_notes');
        $scenarioNotes->setAttrib('cols', 40);
        $scenarioNotes->setAttrib('rows', 3);
        $scenarioNotes->setValue($this->scenario->notes);
        
        // Get a list of objectives (stored as "assets") from the DB...
        $availableObjectives = \Fisdap\EntityUtils::getRepository('AssetLegacy')->getAllChildAssetsByParentName('National Education Standards 2009 (Modified)');
        $scenarioObjectives = new Fisdap_Form_Element_Accordion('scenario_objectives', $availableObjectives);
        $scenarioObjectives->setValue($this->scenario->getAssetIds());
        
        
        $this->addElements(array($scenarioTitle, $scenarioComplaint, $scenarioState, $scenarioNotes, $scenarioObjectives));
        
        
        // Patient Information elements
        $patientInformation = new Zend_Form_Element_Textarea('patient_information');
        $patientInformation->setAttrib('cols', 110);
        $patientInformation->setAttrib('rows', 3);
        $patientInformation->setValue($this->scenario->patient_information);
        
        $patientAge = new Zend_Form_Element_Text('patient_age');
        $patientAge->setAttrib('size', 4);
        $patientAge->setValue($this->scenario->patient->age);
        
        $patientGender = new Zend_Form_Element_Select('patient_gender');
        $patientGender->addMultiOptions(\Fisdap\Entity\Gender::getFormOptions());
        $patientGender->setValue($this->scenario->patient->gender->id);
        
        $patientEthnicity = new Zend_Form_Element_Select('patient_ethnicity');
        $patientEthnicity->addMultiOptions(\Fisdap\Entity\Ethnicity::getFormOptions());
        $patientEthnicity->setValue($this->scenario->patient->ethnicity->id);
        
        $patientWeight = new Zend_Form_Element_Text('patient_weight');
        $patientWeight->setAttrib('size', 4);
        $patientWeight->setValue($this->scenario->patient_weight);
        
        // Add an element to track the units of the weight... 2 options- lbs or kgs
        $patientWeightUnits = new Zend_Form_Element_Select('patient_weight_unit');
        $patientWeightUnits->addMultiOptions(\Fisdap\Entity\WeightUnit::getFormOptions());
        $patientWeightUnits->setValue($this->scenario->weight_unit->id);
        
        $this->addElements(array($patientInformation, $patientAge, $patientGender, $patientEthnicity, $patientWeight, $patientWeightUnits));
        
        $sampleSigns = new Zend_Form_Element_Textarea('sample_signs');
        $sampleSigns->setAttrib('cols', 25)->setAttrib('rows', 2);
        $sampleSigns->setValue($this->scenario->sample_signs);
        
        $sampleAllergies = new Zend_Form_Element_Textarea('sample_allergies');
        $sampleAllergies->setAttrib('cols', 25)->setAttrib('rows', 2);
        $sampleAllergies->setValue($this->scenario->sample_allergies);
        
        $sampleMedications = new Zend_Form_Element_Textarea('sample_medications');
        $sampleMedications->setAttrib('cols', 25)->setAttrib('rows', 2);
        $sampleMedications->setValue($this->scenario->sample_medications);
        
        $samplePriorHistory = new Zend_Form_Element_Textarea('sample_prior_history');
        $samplePriorHistory->setAttrib('cols', 25)->setAttrib('rows', 2);
        $samplePriorHistory->setValue($this->scenario->sample_prior_history);
        
        $sampleOral = new Zend_Form_Element_Textarea('sample_last_oral_intake');
        $sampleOral->setAttrib('cols', 25)->setAttrib('rows', 2);
        $sampleOral->setValue($this->scenario->sample_last_oral_intake);
        
        $sampleEvents = new Zend_Form_Element_Textarea('sample_events');
        $sampleEvents->setAttrib('cols', 25)->setAttrib('rows', 2);
        $sampleEvents->setValue($this->scenario->sample_events);
        
        $this->addElements(array($sampleSigns, $sampleAllergies, $sampleMedications, $samplePriorHistory, $sampleOral, $sampleEvents));
        
        $opqrstOnset = new Zend_Form_Element_Textarea('opqrst_onset');
        $opqrstOnset->setAttrib('cols', 25)->setAttrib('rows', 2);
        $opqrstOnset->setValue($this->scenario->opqrst_onset);
        
        $opqrstProvocation = new Zend_Form_Element_Textarea('opqrst_provocation');
        $opqrstProvocation->setAttrib('cols', 25)->setAttrib('rows', 2);
        $opqrstProvocation->setValue($this->scenario->opqrst_provocation);
        
        $opqrstQuality = new Zend_Form_Element_Textarea('opqrst_quality');
        $opqrstQuality->setAttrib('cols', 25)->setAttrib('rows', 2);
        $opqrstQuality->setValue($this->scenario->opqrst_quality);
        
        $opqrstRadiation = new Zend_Form_Element_Textarea('opqrst_radiation');
        $opqrstRadiation->setAttrib('cols', 25)->setAttrib('rows', 2);
        $opqrstRadiation->setValue($this->scenario->opqrst_radiation);
        
        $opqrstSeverity = new Zend_Form_Element_Textarea('opqrst_severity');
        $opqrstSeverity->setAttrib('cols', 25)->setAttrib('rows', 2);
        $opqrstSeverity->setValue($this->scenario->opqrst_severity);
        
        $opqrstTime = new Zend_Form_Element_Textarea('opqrst_time');
        $opqrstTime->setAttrib('cols', 25)->setAttrib('rows', 2);
        $opqrstTime->setValue($this->scenario->opqrst_time);

        $this->addElements(array($opqrstOnset, $opqrstProvocation, $opqrstQuality, $opqrstRadiation, $opqrstSeverity, $opqrstTime));
        
        
        // Vitals elements
        /*
        $vitalsEntity = $this->scenario->patient->vitals[0];

        $vitalsBloodPressureSystolic = new Zend_Form_Element_Text('vitals_blood_pressure_systolic');
        $vitalsBloodPressureSystolic->setAttrib('size', 4);
        $vitalsBloodPressureSystolic->setValue($vitalsEntity->systolic_bp);

        $vitalsBloodPressureDiastolic = new Zend_Form_Element_Text('vitals_blood_pressure_diastolic');
        $vitalsBloodPressureDiastolic->setAttrib('size', 4);
        $vitalsBloodPressureDiastolic->setValue($vitalsEntity->diastolic_bp);

        $vitalsPulse = new Zend_Form_Element_Text('vitals_pulse');
        $vitalsPulse->setValue($vitalsEntity->pulse_rate);

        $vitalsRespirationsRate = new Zend_Form_Element_Text('vitals_respirations_rate');
        $vitalsRespirationsRate->setAttrib('size', 4);
        $vitalsRespirationsRate->setValue($vitalsEntity->resp_rate);

        $vitalsRespirationsQuality = new Zend_Form_Element_Text('vitals_respirations_quality');
        $vitalsRespirationsQuality->setValue($vitalsEntity->resp_quality);

        $vitalsSpO2 = new Zend_Form_Element_Text('vitals_spo2');
        $vitalsSpO2->setValue($vitalsEntity->spo2);

        $vitalsSkin = new Zend_Form_Element_Text('vitals_skin');
        $vitalsSkin->setValue($vitalsEntity->skins[0]);

        $vitalsPupils = new Zend_Form_Element_Text('vitals_pupils');
        //$vitalsPulse->setValue($vitalsEntity->);

        $vitalsLungSounds = new Zend_Form_Element_Text('vitals_lung_sounds');
        $vitalsLungSounds->setValue($vitalsEntity->lung_sounds[0]);

        $vitalsBloodGlucose = new Zend_Form_Element_Text('vitals_blood_glucose');
        $vitalsBloodGlucose->setValue($vitalsEntity->lood_glucose);

        $vitalsAPGAR = new Zend_Form_Element_Text('vitals_apgar');
        $vitalsAPGAR->setValue($vitalsEntity->apgar);

        $vitalsGCS = new Zend_Form_Element_Text('vitals_gcs');
        $vitalsGCS->setValue($vitalsEntity->gcs);

        $this->addElements(array($vitalsBloodPressureSystolic, $vitalsBloodPressureDiastolic, $vitalsPulse, $vitalsRespirationsRate, $vitalsRespirationsQuality, $vitalsSpO2, $vitalsSkin, $vitalsPupils, $vitalsLungSounds, $vitalsBloodGlucose, $vitalsAPGAR, $vitalsGCS));
        */
        
        $vitalsCurveball = new Zend_Form_Element_Textarea('vitals_curveball');
        $vitalsCurveball->setAttrib('cols', 108);
        $vitalsCurveball->setAttrib('rows', 3);
        $vitalsCurveball->setValue($this->scenario->curveball);
        
        $vitalsCriticalFailures = new Zend_Form_Element_Textarea('vitals_critical_failures');
        $vitalsCriticalFailures->setAttrib('cols', 108);
        $vitalsCriticalFailures->setAttrib('rows', 3);
        $vitalsCriticalFailures->setValue($this->scenario->critical_failures);
        
        $this->addElements(array($vitalsCurveball, $vitalsCriticalFailures));
        
        
        $physicalHEENT = new Zend_Form_Element_Text('physical_heent');
        $physicalHEENT->setValue($this->scenario->physical_heent);

        $physicalNeck = new Zend_Form_Element_Text('physical_neck');
        $physicalNeck->setValue($this->scenario->physical_neck);
        
        $physicalChest = new Zend_Form_Element_Text('physical_chest');
        $physicalChest->setValue($this->scenario->physical_chest);
        
        $physicalAbdomen = new Zend_Form_Element_Text('physical_abdomen');
        $physicalAbdomen->setValue($this->scenario->physical_abdomen);
        
        $physicalPelvis = new Zend_Form_Element_Text('physical_pelvis');
        $physicalPelvis->setValue($this->scenario->physical_pelvis);
        
        $physicalLowerExtremities = new Zend_Form_Element_Text('physical_lower_extremities');
        $physicalLowerExtremities->setValue($this->scenario->physical_lower_extremities);
        
        $physicalUpperExtremities = new Zend_Form_Element_Text('physical_upper_extremities');
        $physicalUpperExtremities->setValue($this->scenario->physical_upper_extremities);
        
        $physicalPosterior = new Zend_Form_Element_Text('physical_posterior');
        $physicalPosterior->setValue($this->scenario->physical_posterior);
        
        $this->addElements(array($physicalHEENT, $physicalNeck, $physicalChest, $physicalAbdomen, $physicalPelvis, $physicalLowerExtremities, $physicalUpperExtremities, $physicalPosterior));
        
        
        // Assessment elements
        $assessmentPrimary = new Zend_Form_Element_Select('assessment_primary');
        $assessmentPrimary->addMultiOptions(\Fisdap\Entity\Impression::getFormOptions(true));
        $assessmentPrimary->setValue($this->scenario->patient->primary_impression->id);
        
        $assessmentSecondary = new Zend_Form_Element_Select('assessment_secondary');
        $assessmentSecondary->addMultiOptions(\Fisdap\Entity\Impression::getFormOptions(true));
        $assessmentSecondary->setValue($this->scenario->patient->secondary_impression->id);
        
        $assessmentSpecialConsideration = new Zend_Form_Element_Textarea('assessment_special_consideration');
        $assessmentSpecialConsideration->setAttrib('cols', 45);
        $assessmentSpecialConsideration->setAttrib('rows', 3);
        $assessmentSpecialConsideration->setValue($this->scenario->assessment_special_consideration);
        
        $this->addElements(array($assessmentPrimary, $assessmentSecondary, $assessmentSpecialConsideration));
        
        // Interventions elements
        $interventionsDangerousActions = new Zend_Form_Element_Textarea('interventions_dangerous_actions');
        $interventionsDangerousActions->setAttrib('cols', 75);
        $interventionsDangerousActions->setAttrib('rows', 3);
        $interventionsDangerousActions->setValue($this->scenario->dangerous_actions);
        
        $this->addElements(array($interventionsDangerousActions));
        
        // Decoration and whatnot.
        
        $this->setElementDecorators(array('ViewHelper'), null, false);
        
        $vitalsModal = new SkillsTracker_Form_VitalModal($vitalIdElement->getValue());
        
        $user = \Fisdap\Entity\User::getLoggedInUser();
        
        $this->setDecorators(array(
                'PrepareElements',
                array('ViewScript', array('viewScript' => "scenario/scenario_form.phtml", 'scenario' => $this->scenario, 'vitalsModal' => $vitalsModal, 'isStaff' => $user->isStaff())),
                'Form',
        ));
    }
    
    public function process($data)
    {
        
        // Scenario Details elements
        $this->scenario->title = $data['scenario_title'];
        $this->scenario->notes = $data['scenario_notes'];
        
        $this->scenario->patient->setComplaintIds($data['scenario_complaint']);
        
        $objectives = array();
        
        if (trim($data['scenario_objectives']) != '') {
            $objectives = explode(',', $data['scenario_objectives']);
        }
        
        $this->scenario->setAssetIds($objectives);
        
        // Patient Information elements
        $this->scenario->patient_information = $data['patient_information'];
        $this->scenario->patient->age = $data['patient_age'];
        $this->scenario->patient->gender = $data['patient_gender'];
        $this->scenario->patient->ethnicity = $data['patient_ethnicity'];
        $this->scenario->patient_weight = $data['patient_weight'];
        $this->scenario->weight_unit = \Fisdap\EntityUtils::getEntity('WeightUnit', $data['patient_weight_unit']);
        
        $this->scenario->sample_signs = $data['sample_signs'];
        $this->scenario->sample_allergies = $data['sample_allergies'];
        $this->scenario->sample_medications = $data['sample_medications'];
        $this->scenario->sample_prior_history = $data['sample_prior_history'];
        $this->scenario->sample_last_oral_intake = $data['sample_last_oral_intake'];
        $this->scenario->sample_events = $data['sample_events'];
        
        $this->scenario->opqrst_onset = $data['opqrst_onset'];
        $this->scenario->opqrst_provocation = $data['opqrst_provocation'];
        $this->scenario->opqrst_quality = $data['opqrst_quality'];
        $this->scenario->opqrst_radiation = $data['opqrst_radiation'];
        $this->scenario->opqrst_severity = $data['opqrst_severity'];
        $this->scenario->opqrst_time = $data['opqrst_time'];
        
        // Physical Exam elements
        $this->scenario->physical_heent = $data['physical_heent'];
        $this->scenario->physical_neck = $data['physical_neck'];
        $this->scenario->physical_chest = $data['physical_chest'];
        $this->scenario->physical_abdomen = $data['physical_abdomen'];
        $this->scenario->physical_pelvis = $data['physical_pelvis'];
        $this->scenario->physical_lower_extremities = $data['physical_lower_extremities'];
        $this->scenario->physical_upper_extremities = $data['physical_upper_extremities'];
        $this->scenario->physical_posterior = $data['physical_posterior'];
        
        $this->scenario->curveball = $data['vitals_curveball'];
        $this->scenario->critical_failures = $data['vitals_critical_failures'];
        
        $this->scenario->dangerous_actions = $data['interventions_dangerous_actions'];
        
        // Assessment elements
        $this->scenario->patient->primary_impression = $data['assessment_primary'];
        $this->scenario->patient->secondary_impression = $data['assessment_secondary'];
        
        $this->scenario->assessment_special_consideration = $data['assessment_special_consideration'];
        
        $user = \Fisdap\Entity\User::getLoggedInUser();
        
        if ($user->isStaff() && array_key_exists('scenario_state', $data)) {
            $this->scenario->state = \Fisdap\EntityUtils::getEntity('ScenarioState', $data['scenario_state']);
        }
        
        $this->scenario->save();
    }
}
