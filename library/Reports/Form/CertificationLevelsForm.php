<?php
/*	*	*	*	*	*	*	*	*
 *
 *	Copyright (C) 1996-2011.  This is an unpublished work of
 *			Headwaters Software, Inc.
 *				ALL RIGHTS RESERVED++++
 *	This program is a trade secret of Headwaters Software, Inc.
 *	and it is not to be copied, distributed, reproduced, published,
 *	or adapted without prior authorization
 *	of Headwaters Software, Inc.
 *
 *	*	*	*	*	*	*	*	*/

/**
 * Description of CertificationLevelsForm
 *
 * @author stape
 */

class Reports_Form_CertificationLevelsForm extends Fisdap_Form_Base
{
	/**
	 * @var array default values for the report form
	 */
	public $config;
	
	public function __construct($config = null, $options = null)
	{
		$this->config = $config;
		
		parent::__construct($spec);
	}
	
	public function init()
	{		
		// certification level
		$certLevel = new Fisdap_Form_Element_CertificationLevelChosen('certLevel');
		$certLevel->setDecorators(array(
			'ViewHelper',
			array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => 'grid_9')),
			array('Label', array('tag' => 'div', 'class' => 'grid_3', 'escape' => false)),
			array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt')),
		));
		$certLevel->addErrorMessage("Please select at least one certification level.");
		$this->addElement($certLevel);
		
		$this->setDecorators(array(
            'FormErrors',
			'PrepareElements',
			'FormElements',
			//array('ViewScript', array('viewScript' => "forms/lab-practice-goals-form.phtml")),
		));
		
		//Do we have existing form values to populate
		if ($this->config) {
			$this->setDefaults($this->config);
		}
	}
	
	/**
	 * Return an array containing the summary of what's on this report
	 *
	 */
	public function getReportSummary($config)
	{
		$summary = array();
		
		// get certification information
		$certifications = array();
		
		if (!empty($config['certLevel'])) {
				foreach($config['certLevel'] as $certification) {
				$certifications[] = \Fisdap\EntityUtils::getEntity("CertificationLevel", $certification)->description;
			}
			$summary["Certification"] = implode(", ", $certifications);
		} else {
			$summary["Certifications"] = implode(", ", \Fisdap\EntityUtils::getRepository('CertificationLevel')->getSortedFormOptions(\Fisdap\Entity\ProgramLegacy::getCurrentProgram()->profession->id));
		}
		
		
		return $summary;
	}
}

?>