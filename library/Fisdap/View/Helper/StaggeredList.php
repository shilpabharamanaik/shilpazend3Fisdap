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
 * This helper takes a list of items and produces a "staggered" list.
 *
 * The overall pattern is somewhat described below (each number represents the
 * position in the list).
 *
 * 1
 * -
 * 1
 * 2
 * -
 * 1
 * 2
 * 3
 * -
 * 1	4
 * 2	5
 * 3	6
 * -
 * 1	4	7
 * 2	5	8
 * 3	6	9
 * -
 * 1	5	9
 * 2	6	10
 * 3	7
 * 4	8
 * -
 * 1	5	9
 * 2	6	10
 * 3	7	11
 * 4	8	12
 */

/**
 * @package SkillsTracker
 */
class Fisdap_View_Helper_StaggeredList extends Zend_View_Helper_Abstract
{
    /**
     * @var string the html to be rendered
     */
    protected $_html;
    
    /**
     * @param array $items Array of items to display in the list.
     *
     * @param integer $columnCount Number of columns to display.
     *
     * @return string the shift list rendered as an html table
     */
    public function staggeredList($items, $columnCount=3, $minPerColumn=3)
    {
        // First, split up the input into the various columns...
        $columns = $this->splitData($items, $columnCount, $minPerColumn);
        
        $this->_html = '';
        
        $this->_html .= '<div class="clear"></div>';
        
        $this->_html .= '<div class="grid_12 staggered-list" id="staggered-list-parent">';
        
        foreach ($columns as $columnNum => $column) {
            $columnGridWidth = (ceil(12/$columnCount));
            $this->_html .= '<div id="staggered-list-column-' . ($columnNum + 1) . '" class="grid_' . $columnGridWidth . '">';
            
            foreach ($column as $item) {
                $this->_html .= $item . "<br />";
            }
            
            $this->_html .= '</div>';
        }
        
        $this->_html .= '</div>';
        $this->_html .= '<div class="clear"></div>';
        return $this->_html;
    }
    
    private function splitData($items, $columnCount, $minPerColumn)
    {
        $columns = array_fill(0, $columnCount, array());
        
        $curColumn = 0;
        
        $normalizedColumnCount = max($minPerColumn, ceil(count($items)/$columnCount));
        
        foreach ($items as $item) {
            if (count($columns[$curColumn]) >= $normalizedColumnCount) {
                $curColumn++;
            }
            
            // Prevent the $curColumn from wrapping...
            if ($curColumn >= $columnCount) {
                $curColumn = 0;
            }
            
            $columns[$curColumn][] = $item;
        }
        
        return $columns;
    }
}
