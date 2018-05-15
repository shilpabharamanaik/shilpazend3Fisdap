<?php

class Mobile_View_Helper_MobileShiftList extends Zend_View_Helper_Abstract
{
    protected $_html;
    
    /**
     * @param array $shifts array of arrays containing each shift to be
     * rendered in a view
     *
     * @param array $messages an array of messages to be put into the shift list
     * header
     *
     * @return string the shift list rendered as an html table
     */
    public function mobileShiftList($studentId, $filter = 'all', $isInstructor = false)
    {
        $em = \Fisdap\EntityUtils::getEntityManager();

        $rawShifts = $em->getRepository('Fisdap\Entity\ShiftLegacy')->getShiftEntitiesByStudent($studentId, $filter);
        $shiftPartials = array();
        
        foreach ($rawShifts as $shift) {
            $shiftPartials[] = array('shift' => $shift);
        }
        
        $shifts = $shiftPartials;
        
        //var_dump(count($shifts));
        
        $this->_html .= "<div id='shift-list-container'>";
        if (count($shifts)) {
            $this->_html .= $this->view->partialLoop('shiftContainer.phtml', $shifts);
        } else {
            $this->_html .= "There are no shifts to display";
        }
        $this->_html .= "</div>";
        
        return $this->_html;
    }
}
