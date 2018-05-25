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
 * This produces a modal form for adding/editing Meds
 */

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class SkillsTracker_Form_MedModal extends SkillsTracker_Form_Modal
{

	/**
	 * @var \Fisdap\Entity\Med
	 */
	protected $med;

	/**
	 * @param int $medId the id of the Med to edit
	 * @param $options mixed additional Zend_Form options
	 */
	public function __construct($medId = null, $options = null)
	{
		$this->med = \Fisdap\EntityUtils::getEntity('Med', $medId);
		parent::__construct($options);
	}
	
	public function init()
	{
        parent::init();
		
		$this->setAttrib('id', 'medDialog');
		
		$this->addJsFile("/js/jquery.combobox.js");        
		$this->addJsFile("/js/library/SkillsTracker/Form/med-modal.js");

        $performed = new Zend_Form_Element_Checkbox('medPerformed');
        $performed->setLabel("I performed this treatment");
        
        $medication = new Zend_Form_Element_Select('medication');
        $medication->setLabel('Medication:' . self::NSC_DIAMOND)
                  ->setMultiOptions(\Fisdap\Entity\MedType::getFormOptions(true))
				  ->setDescription('(required)')
				  ->addValidator('NotEmpty', true, array('type' => 'zero'))
				  ->addErrorMessage("Please choose a medication.");
        
        $dose = new Zend_Form_Element_Text('dose');
        $dose->setLabel('Dose:')
			 ->setDescription('(required)')
			 ->setRequired(true)
			 ->addErrorMessage("Please enter a dose.");
        
        $route = new Zend_Form_Element_Select('route');
        $route->setLabel('Route:')
			  ->setDescription('(required)')
              ->setMultiOptions(\Fisdap\Entity\MedRoute::getFormOptions(true))
			  ->addValidator('NotEmpty', true, array('type' => 'zero'))
			  ->addErrorMessage("Please choose a route.");

		$medId = new Zend_Form_Element_Hidden('medId');
		$patientId = new Zend_Form_Element_Hidden('patientId');
		$shiftId = new Zend_Form_Element_Hidden('shiftId');
		
		$this->addElements(array($performed, $medication, $dose, $route, $medId, $patientId, $shiftId));
		
		$this->setElementDecorators(self::$elementDecorators, array('medPerformed', 'medId', 'patientId', 'shiftId'), false);
		$this->setElementDecorators(self::$checkboxDecorators, array('medPerformed'), true);
		$this->setElementDecorators(self::$hiddenElementDecorators, array('medId', 'patientId', 'shiftId'), true);
		$save_btn_wrapper = '<span class="green-buttons"></span>';

		$this->setDecorators(array(
			'PrepareElements',
			array('ViewScript', array('viewScript' => "medModal.phtml", 'viewModule' => 'skills-tracker')),
			'Form'
		));
		
		if ($this->med->id) {
			$this->setDefaults(array(
				'medPerformed' => $this->med->performed_by,
				'medication' => $this->med->medication->id,
				'dose' => $this->med->dose,
				'route' => $this->med->route->id,
				'patientId' => $this->med->patient->id,
				'shiftId' => $this->med->shift->id,
				'medId' => $this->med->id,
			));
		} else {
			$this->setDefaults(array(
				'route' => 0,
				'medication' => 0,
			));
		}
	}
	
	/**
	 * Validate the form, if valid, save the Med, if not, return the error msgs
	 *
	 * @param array $data the POSTed data
	 * @return mixed either boolean true, or an array of error messages
	 */
	public function process($data)
	{
		if ($this->isValid($data)) {
			$values = $this->getValues($data);
			
			if ($values['medId']) {
				$med = \Fisdap\EntityUtils::getEntity('Med', $values['medId']);				
			} else {
				$med = \Fisdap\EntityUtils::getEntity('Med');
			}
			
			$med->performed_by = $values['medPerformed'];
			$med->medication = $values['medication'];
			$med->dose = $values['dose'];
			$med->route = $values['route'];

			if ($values['patientId']) {
				$patient = \Fisdap\EntityUtils::getEntity('Patient', $values['patientId']);
				$patient->addMed($med);
				$patient->save();				
			} else if ($values['shiftId']) {
				$shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $values['shiftId']);
				$shift->addMed($med);
				$shift->save();
			}

			return "Med_" . $med->id;
		}
		
		return $this->getMessages();
	}
}
