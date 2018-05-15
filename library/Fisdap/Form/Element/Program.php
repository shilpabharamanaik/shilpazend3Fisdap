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
 * Custom Zend_Form_Element_Select for displaying a list of US states
 */

/**
 * @package Fisdap
 */
class Fisdap_Form_Element_Program extends Zend_Form_Element_Select
{
    public function init()
    {
        $programs = \Fisdap\EntityUtils::getEntityManager()->createQuery("SELECT p.id, p.name FROM \Fisdap\Entity\ProgramLegacy p ORDER BY p.name")->getResult();
        foreach ($programs as $program) {
            $this->addMultiOption($program['id'], $program['name']);
        }
    }
}
