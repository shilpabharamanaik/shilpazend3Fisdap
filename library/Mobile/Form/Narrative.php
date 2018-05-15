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
 * Narrative Form
 */

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class Mobile_Form_Narrative extends SkillsTracker_Form_Narrative
{
    public function init()
    {
        parent::init();
        
        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "mobileNarrative.phtml")),
            'Form',
        ));
    }
}
