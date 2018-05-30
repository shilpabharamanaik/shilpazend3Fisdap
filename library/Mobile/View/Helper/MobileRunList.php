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
 * This helper will display a list of the user's runs for a particular shift
 */

/**
 * @package Mobile
 */
class Mobile_View_Helper_MobileRunList extends Zend_View_Helper_Abstract
{
    /**
     * @var string the html to be rendered
     */
    protected $_html;
    
    /**
     * @param array $runs array of arrays containing each run to be
     * rendered in a view for a mobile device
     *
     * @return string the run list rendered as an html table
     */
    public function mobileRunList($shiftId, $isInstructor = false)
    {
        $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);
        $runs = array();
        
        foreach ($shift->runs as $run) {
            $runs[] = array('run' => $run);
        }
        
        $this->_html .= $this->view->formHidden('shiftId', $shift->id);
        $this->_html .= "<div id='run-list-container'>";
        $this->_html .= $this->view->partialLoop('runContainer.phtml', $runs);
        $this->_html .= "</div>";
        

        //if ($isInstructor) {
        //	$this->_html .= $this->view->partialLoop('runCellsInstructor.phtml', $runs);
        //} else {
        //	$this->_html .= $this->view->partialLoop('runCells.phtml', $runs);
        //}
        
        return $this->_html;
    }
}
