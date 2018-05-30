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
 * Helper to render a table of Moodle Group Mappings
 */

/**
 * @package Fisdap
 */
class Fisdap_View_Helper_MoodleGroupList extends Zend_View_Helper_Abstract
{
    protected $_html = "";
    
    public function moodleGroupList()
    {
        $this->_html = "<table class='fisdap-table'>";
        $this->_html .= "<thead><tr><td>Program</td><td>Product</td><td>Moodle Group ID</td></tr></thead>";
        $this->_html .= "<tbody>";
        
        $moodleGroups = \Fisdap\EntityUtils::getRepository("MoodleGroup")->findAll();
        
        foreach ($moodleGroups as $group) {
            $this->_html .= "<tr><td>" . $group->program->name . "</td><td>" . $group->product->name . "</td><td>" . $group->moodle_group_id . "</td></tr>";
        }
        
        $this->_html .= "</tbody></table>";
    
        return $this->_html;
    }
}
