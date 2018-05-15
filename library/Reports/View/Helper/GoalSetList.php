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
 * This file contains a view helper to render a goal set table
 */

/**
 * @package Reports
 */
class Reports_View_Helper_GoalSetList extends Zend_View_Helper_Abstract
{
    protected $_html;
    
    public function goalSetList($goalSets)
    {
        $this->_html = "<table id='goal-set-table' class='fisdap-table'>";
        $this->_html .= "<thead>
                            <tr>
                                <th class='name-col'></th>
                                <th class='cert-col'>Certification Level</th>
                                <th class='actions-col'></th>
                            </tr>
                        </thead>";
        $this->_html .= "<tbody>";
        foreach ($goalSets as $goalSet) {
            $this->_html .= $this->view->partial('goal/goalSetCell.phtml', array('goalSet' => $goalSet));
            //$this->_html .= "<tr id='{$goalSet->id}'><td class='name-col'>" . $goalSet->name . "</td><td class='cert-col'>" . $goalSet->getCertificationLevel() . "</td><td class='actions-col'><a href='#' class='delete-goals' goalId='{$goalSet->id}'>X</a></td></tr>";
        }
        $this->_html .= "</tbody>";
        $this->_html .= "</table>";
        
        return $this->_html;
    }
}
