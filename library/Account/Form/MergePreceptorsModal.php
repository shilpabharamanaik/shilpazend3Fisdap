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
 * This produces a modal form for adding/editing Airways
 */

/**
 * @package    Account
 * @subpackage Forms
 */
class Account_Form_MergePreceptorsModal extends Fisdap_Form_BaseJQuery
{

	public static $gridElementDecorators = array(
		'ViewHelper',
		array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => 'grid_3 mostInputs')),
        array('Label', array('tag' => 'div', 'class' => 'grid_2 leftLabels', 'escape' => false)),
        
	);
	
	public $site_id;
	
	public function __construct($siteId = null, $options = null)
	{
		$this->site_id = $siteId;
		parent::__construct($options);
	}
	
	public function init()
	{

        parent::init();
		$this->setAttrib('id', 'baseForm');
			
		
		$siteId = new Zend_Form_Element_Hidden('siteId');
		$siteId->setValue($this->site_id);


		
		$this->addElements(array(
			$name,
			$siteId,
		));

		//$this->setElementDecorators(self::$gridElementDecorators, array('base_name'), true);
		
		$viewscript = mergePreceptorsModal.phtml;

		$this->setDecorators(array(
			'PrepareElements',
			array('ViewScript', array('viewScript' => "mergePreceptorsModal.phtml")),
			'Form',
			array('DialogContainer', array(
					'id'          	=> 'mergePreceptorsDialog',
					'class'         => 'mergePreceptorsDialog',
					'jQueryParams' 	=> array(
					'tabPosition' 	=> 'top',
					'modal' 		=> true,
					'autoOpen' 		=> false,
					'resizable' 	=> false,
					'width' 		=> 475,
					'title'	 		=> 'Merge Preceptors',			),
			)),
		));
		
	}
	
	/**
	 * Validate the form, if valid, save the Airway, if not, return the error msgs
	 *
	 * @param array $data the POSTed data
	 * @return mixed either boolean true, or an array of error messages
	 */
	public function process($data)
	{
		if($data['selectedPreceptor'] == ""){
			return "Please select a preceptor";
		}
		
		else {
			$user = \Fisdap\Entity\User::getLoggedInUser();
			$programId = $user->getProgramId();
			$program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $programId);
			
			$site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $data['site']);
			
			// first get the preceptor we will merge into
			$preceptorToKeep = \Fisdap\EntityUtils::getEntity("PreceptorLegacy", $data['selectedPreceptor']);
	
			$otherPreceptors = $data['notSelectedPreceptors'];
	
			
			// for all other preceptors
			foreach($otherPreceptors as $preceptor){
				
				$preceptor = \Fisdap\EntityUtils::getEntity("PreceptorLegacy", $preceptor);
				
				// all runs associated with this preceptor
				$patients = $preceptor->getPatientsByPreceptor($preceptor->id);
				
				// for each of those runs, set the preceptor to the new preceptor
				foreach($patients as $patient){
					$patient->set_preceptor($preceptorToKeep->id);
					$patient->save();
				}
	
				// now handle events with this preceptor
				$preceptor->setEventPreceptors($preceptorToKeep->id, $preceptor->id);
				
				// finally delete this preceptor
				$preceptor->delete();
				
			}
			
			return $preceptorToKeep->id;
		}
		
	
	}
	
}
