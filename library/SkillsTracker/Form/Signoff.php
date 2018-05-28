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
class SkillsTracker_Form_Signoff extends Fisdap_Form_Base
{
    /**
     * @var Fisdap/Entity/PreceptorSignoff
     */
    protected $signoff;
	
	/**
	 * @var Fisdap/Entity/Run
	 */
	public $run;

    /**
     * @var Fisdap/Entity/Patient
     */
    public $patient;
	
	/**
	 * @var Fisdap/Entity/ShiftLegacy
	 */
	public $shift;

	/**
	 * @var Fisdap/Entity/Verification
	 */
	public $verification;
	
	/**
	 * @var array contains an associative array of rater types
	 */
	public $raterTypes;
	
	/**
	 * @var array contains an associative array of rate types
	 */
	public $types;

	/**
	 * @var boolean either the educator can be rated or not
	 */
	public $allow_educator_evaluations;

	/**
	 * @var string the destination media for the form. Options include 'screen' and 'pdf'
	 */
	public $media;
	
	/**
	 * @var string a unique string identifying this form (useful when multiple forms per page), either run or shift ID
	 */
	public $uniqueIdKey;

    /**
     * @var \Fisdap\Api\Client\Shifts\Attachments\Gateway\ShiftAttachmentsGateway
     */
    public $shiftAttachmentsGateway;

    /**
     * @var int The count of sign off types currently enabled in the program
     */
    public $signoffTypeCount;

    /*
	 * Constructs the signoff form
	 * @param integer $signoffId the ID of the PreceptorSignoff entity
	 * @param integer $runId the ID of the run (if applicable)
	 * @param integer $shiftId the ID of teh shift (if applicable)
	 * @param Zend_Config $options the Zend options for the form
	 * @param string $media The destination media for the form. Options include 'screen' and 'pdf'
	 */
    public function __construct($signoffId = null, $runId = null, $shiftId = null, $shiftAttachmentsGateway = null, $options = null, $media = 'screen')
	{
		$this->signoff = \Fisdap\EntityUtils::getEntity('PreceptorSignoff', $signoffId);
		$this->run = \Fisdap\EntityUtils::getEntity('Run', $runId);
		$this->shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);
        if ($this->run) {
            $this->patient = \Fisdap\EntityUtils::getRepository('Patient')->findOneByRun($runId);
        }
		
		$mediaAvailable = array('screen', 'pdf');
		if (in_array($media, $mediaAvailable)) {
			$this->media = $media;
		}
		
		// set unique key for this form
		if ($this->run->id) {
			$this->uniqueIdKey = $this->run->id;
		} else {
			$this->uniqueIdKey = $this->shift->id;
		}
		
		if ($this->run->id) {
			$this->verification = $this->run->verification;
		}else if($this->shift->id){
			$this->verification = $this->shift->verification;
		}
		
		$this->raterTypes = \Fisdap\Entity\PreceptorRatingRaterType::getFormOptions(false, false);
		$this->types = \Fisdap\Entity\PreceptorRatingType::getFormOptions(false, false);

		$program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', \Fisdap\Entity\User::getLoggedInUser()->getProgramId());
		$this->allow_educator_evaluations = $program->program_settings->allow_educator_evaluations;

        $program->program_settings;

        $this->signoffTypeCount = count(array_filter(array(
            $program->program_settings->allow_educator_signoff_login,
            $program->program_settings->allow_educator_signoff_signature,
            $program->program_settings->allow_educator_signoff_email,
            $program->program_settings->allow_educator_signoff_attachment
        )));

        if ($shiftAttachmentsGateway) {
            $this->shiftAttachmentsGateway = $shiftAttachmentsGateway;
        }

		parent::__construct($options);
	}
	
	public function init()
	{
        parent::init();
		
		// for PDF media, we need a more-unique ID (there may be multiple signoffs per display)
		if ($this->media == 'screen') {
			$this->setAttrib('id', 'signoffForm');
		} else {
			$this->setAttrib('id', 'signoffForm-' . $this->uniqueIdKey);
			$this->setAttrib('class', 'signoffForm');
		}
		
		$this->addCssFile('/css/library/SkillsTracker/Form/signoff.css');
        $this->addCssFile('/css/library/SkillsTracker/View/Helper/attachment-list.css');

        $this->addJSFile('/js/library/SkillsTracker/View/Helper/attachment-list.js');
		$this->addJSFile('/js/library/SkillsTracker/Form/signoff.js');
        $this->addJSFile('/js/jquery.scrollTo-1.4.2-min.js');

        $summary = new Fisdap_Form_Element_TextareaHipaa('summary');
        
        $plan = new Fisdap_Form_Element_TextareaHipaa('plan');
        $plan->setLabel('Plan of action for next patient');
		
		foreach ($this->raterTypes as $raterId => $raterName) {
			foreach ($this->types as $typeId => $typeName) {
				if ($this->media == 'pdf') {
					// need unique element names when multiople forms on a page, othewrise jquery.ui pretty func fails
					$ratingElemName = $raterName . "_" . $typeId . "_" . $this->uniqueIdKey;
				} else {
					$ratingElemName = $raterName . "_" . $typeId;
				}
				$element = new SkillsTracker_Form_Element_Rating($ratingElemName);
				$element->setLabel($typeName);
				$element->setDecorators(array('ViewHelper'));
				$this->addElement($element);
			}
		}
        
		$save = new Fisdap_Form_Element_SaveButton('save');
		$save->setLabel('Sign off');
		$cancel = new Fisdap_Form_Element_CancelButton('cancel');
		
		//Create hidden form elements at set their decorators
		$signoffId = new Zend_Form_Element_Hidden('signoffId');
		$runId = new Zend_Form_Element_Hidden('runId');
		$shiftId = new Zend_Form_Element_Hidden('shiftId');
		$formName = new Zend_Form_Element_Hidden('formName');
		$formName->setValue('Signoff');
		
		// Hidden element with the unique form key
		$formKey = new Zend_Form_Element_Hidden('formKey');
		$formKey->setValue($this->uniqueIdKey);
		
		
        $this->addElements(array($summary, $plan, $signoffId, $runId, $shiftId, $runTypeString, $formName));
		$this->setElementDecorators(array('ViewHelper'), array('signoffId', 'runId', 'shiftId', 'formName', 'summary', 'plan'), true);
		if ($this->media == 'screen') {
			$this->addElements(array($save, $cancel));
			$this->setElementDecorators(array('ViewHelper'), array('save'), true);
		}

		// Set a hidden element with the unique form key
		if ($this->media == 'pdf') {
			$this->addElements(array($formKey));
			$this->setElementDecorators(array('ViewHelper'), array('formKey'), true);
		}

		$this->addSubForm(new SkillsTracker_Form_VerificationSubForm($this->verification->id, $this->run->id, $this->shift->id, $this->shiftAttachmentsGateway), 'verificationForm');
		
		//Set the decorators for this form
		//We use a different template file based on the media
		if ($this->media == 'screen') {
			$this->setDecorators(array(
				'FormErrors',
				'PrepareElements',
				array('ViewScript', array('viewScript' => "signoffForm.phtml")),
				'Form',
			));
		} else if ($this->media == 'pdf') {
			$this->setDecorators(array(
				'PrepareElements',
				array('ViewScript', array('viewScript' => "signoffFormPdf.phtml")),
				'Form',
			));
		}
		
		$typeString = '';
		switch($this->run->shift->type){
			case 'lab':
				$typeString = 'scenario';
				break;
			case 'clinical':
				$typeString = 'assessment';
				break;
			case 'field':
				$typeString = 'run';
				break;
		}
		
		//Set the defaults for this form
        if ($this->signoff->id) {
			$defaults = array(
				'signoffId' => $this->signoff->id,
				'summary' => $this->signoff->summary,
				'plan' => $this->signoff->plan,
				'runId' => $this->run->id,
				'shiftId' => $this->shift->id
			);
			
			foreach ($this->signoff->ratings as $rating) {
				if ($this->media == 'pdf') {
					// need unique element names when multiple forms on a page, othewrise jquery.ui pretty func fails
					$elementName = $rating->rater_type->name . "_" . $rating->type->id . "_" . $this->uniqueIdKey;
				} else {
					$elementName = $rating->rater_type->name . "_" . $rating->type->id;
				}
				$defaults[$elementName] = $rating->value;
			}
			
            $this->setDefaults($defaults);
        } else {
            $this->setDefaults(array(
				'runId' => $this->run->id,
				'shiftId' => $this->shift->id
            ));
        }
		
		// When media is set to PDF, omit some fields if they are empty
		if ($this->media == 'pdf') {
			if ($this->getValue('plan') == '') { // hide plan if empty
				$this->removeElement('plan');
			}
			if ($this->getValue('summary') == '') { // hide summary if empty
				$this->removeElement('summary');
			}
		}
    }
    
    public function process($data)
    {
		$isAutosave = $data['autosave'];

		// if we have a run id, this is a patient-level sign off
		$patientSignoff = ($data['runId']) ? true : false;
		
		// If we're not autosaving, then we're signing off, so we need to add some validation.
		// This should never happen when signing off on a shift - no ratings for that signoff.
		// This also requires that the program settings allow educator evaluations, otherwise nothing to validate
		if (!$isAutosave && $patientSignoff && $this->allow_educator_evaluations){
			foreach ($this->raterTypes as $raterId => $raterName) {
				foreach ($this->types as $typeId => $typeName) {
					$element = $this->getElement($raterName . "_" . $typeId);
					$element->setRequired(true);
					$element->addErrorMessage("The ratings must be completed.  Choose not applicable (N/A) if necessary.");
				}
			}
		}
		
        if ($this->isValid($data)) {
			$values = $this->getValues($data);

			if ($values['signoffId']) {
				$signoff = \Fisdap\EntityUtils::getEntity('PreceptorSignoff', $values['signoffId']);
			} else {
				$signoff = new \Fisdap\Entity\PreceptorSignoff();
			}

			$signoff->summary = $values['summary'];
			$signoff->plan = ($values['plan']) ? $values['plan'] : "";

			$signoffableEntity = null;

			if ($patientSignoff) {
				$signoffableEntity = \Fisdap\EntityUtils::getEntity('Run', $values['runId']);
                $signoff->setPatient($this->patient);
			} else {
				$signoffableEntity = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $values['shiftId']);
			}
			
			if (!$isAutosave) {
				$verificationForm = $this->getSubForm('verificationForm');
				$verification = $verificationForm->process($values['verificationForm']);

				//$verficiation is either the verification or an array of error messages.
				if ($verification instanceof \Fisdap\Entity\Verification) {
					// if the verification was saved successfully, go ahead and save it and
					// lock the entity, if needed
					$signoffableEntity->verification = $verification;
					if ($values['verificationForm']['lock']) {
						$signoffableEntity->locked = true;
                        if ($this->patient) {
                            $this->patient->setLocked(true);
                        }
					} else {
                        $signoffableEntity->locked = false;
                        if ($this->patient) {
                            $this->patient->setLocked(false);
                        }
                    }

                    if ($this->patient) {
                        $this->patient->setVerification($verification);
                    }
				} else {
					//the error messages from trying to save the verification
					$this->addErrors($verification);
					return $verification;
				}
			}
			
			if ($values['runId']) {
				$signoffableEntity->signoff = $signoff;
			} else {
				$signoffableEntity->signoff = $signoff;
				$signoffableEntity->signoff->shift = $signoffableEntity;
			}

            $signoffableEntity->save();

            // set ratings if educator evaluations (ratings) are allowed
            //This needs to be in a separate flush for some reason (Doctrine is not recognized other changes when this flushed)
            if($values['runId'] && $this->allow_educator_evaluations){
                $signoff->set_ratings($values);
            }
            $signoffableEntity->save();

			return $signoffableEntity->signoff->id;
		}
		
        return array_unique($this->getMessages());
    }
}