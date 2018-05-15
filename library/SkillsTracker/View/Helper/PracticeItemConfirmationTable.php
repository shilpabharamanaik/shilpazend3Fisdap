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
 * This helper will display a table for confirm/unconfirming practice items
 */
use Fisdap\Entity\StudentLegacy;

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_PracticeItemConfirmationTable extends Zend_View_Helper_Abstract
{
    /**
     * @var string the html to be rendered
     */
    protected $_html;
    
    public function practiceItemConfirmationTable(StudentLegacy $student, array $items, array $shiftTypes)
    {
        //$this->view->headScript()->appendFile('/js/library/SkillsTracker/View/Helper/add-preceptor-widget.js');

        $confirmButtonset = new Fisdap_Form_Element_jQueryUIButtonset('confirmed-filter');
        $confirmButtonset->setOptions([0 => 'Unconfirmed items', 1 => 'Confirmed items'])
            ->setDecorators(['ViewHelper'])
            ->setValue(0)
            ->setUiTheme("")
            ->setUiSize("extra-small");

        $viewParams = [
            'student' => $student,
            'confirmButtonset' => $confirmButtonset,
            'items' => $items,
            'shiftTypes' => $shiftTypes
        ];
        
        return $this->view->partial('practiceItemConfirmationTable.phtml', $viewParams);
    }
}
