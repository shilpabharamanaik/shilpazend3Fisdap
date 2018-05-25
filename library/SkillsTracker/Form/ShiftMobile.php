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
 * This produces a mobile friendly modal for editing shifts
 */

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class SkillsTracker_Form_ShiftMobile extends SkillsTracker_Form_Shift
{
	public function __construct($shiftId = null, $studentId = null, $programId = null, $types = null, $options = null)
	{
		parent::__construct($shiftId, $studentId, $programId, $types, $options);
	}

	/**
	 * Returns class and style attributes for either standard or mobile interfaces
	 *
	 * @return array With class and style keys, or empty for mobile
	 */
	public function getClassAndStyleAttribs()
	{
		return array('class'=>'select-mobile');
	} 
}