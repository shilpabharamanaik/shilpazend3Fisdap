<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
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
 * Custom Zend_Form_Element_Select for displaying bases
 */

/**
 * @package Fisdap
 */
class Fisdap_Form_Element_Procedures extends Zend_Form_Element_Select
{
	public function init()
	{
		$optGroups[0] = "";
		$optGroups['Airway'] = $this->getOptGroup('AirwayProcedure');
		$optGroups['Venous Access'] = $this->getOptGroup('IvProcedure');
		$optGroups['Cardiac'] = $this->getOptGroup('CardiacProcedure');
		$optGroups['Other'] = $this->getOptGroup('OtherProcedure');
        $optGroups['Lab Assessments'] = $this->getOptGroup('LabAssessment');
		
		$this->setDecorators(array('ViewHelper'));
		
		$this->setMultiOptions($optGroups);
	}
	
	private function getOptGroup($entity)
	{
		// Get the list of procedures.
		// Should move this into a different class/location at some point too, just not sure quite where yet...
		$options = array();
		
		$className = "\Fisdap\Entity\\$entity";
		$procs = $className::getFormOptions();
		
		foreach($procs as $id => $proc){
			$options[$entity . '_' . $id] = $proc;
		}
		
		return $options;
	}
}