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
 * This helper will display a modal with a eureka graph
 */

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_EurekaModal extends Zend_View_Helper_Abstract
{
    
    /**
     * @var string the html to be rendered
     */
    protected $_html;
    
    // will create an empty modal
    public function eurekaModal($addScripts = false)
    {
        // gross collection of javascript files we need for the graphing plugin
        if ($addScripts) {
            $this->view->headScript()->appendFile("/js/jquery.eurekaGraph.js");
            $this->view->headScript()->appendFile("/js/library/SkillsTracker/View/Helper/eureka-modal.js");
            $this->view->headScript()->appendFile("/js/jquery.jqplot.min.js");
            $this->view->headScript()->appendFile("/js/syntaxhighlighter/scripts/shCore.min.js");
            $this->view->headScript()->appendFile("/js/syntaxhighlighter/scripts/shBrushJScript.min.js");
            $this->view->headScript()->appendFile("/js/syntaxhighlighter/scripts/shBrushXml.min.js");
            
            // gross collection of stylesheet we need for the graphing plugin
            $this->view->headLink()->appendStylesheet("/css/jquery.jqplot.min.css");
            $this->view->headLink()->appendStylesheet("/css/jquery.eurekaGraph.css");
            $this->view->headLink()->appendStylesheet("/js/syntaxhighlighter/styles/shCoreDefault.min.css");
            $this->view->headLink()->appendStylesheet("/js/syntaxhighlighter/styles/shThemejqPlot.min.css");
        }

        // set up our modal (eureka-modal.js will handle the initialization)
        $this->_html =  "<div id='eureka-modal'>";
        $this->_html .= 	"<img src='/images/icons/delete.png' id='close-eureka-modal'>";
        $this->_html .= 	"<div id='eureka-modal-content'></div>";
        $this->_html .= "</div>";
        
        return $this->_html;
    }
    
    // generates a div that will eventually be called a "eurekaGraph()"
    public function generateEurekaList($items, $defId, $studentId)
    {
        $def = \Fisdap\EntityUtils::getEntity("PracticeDefinition", $defId);
        $user = \Fisdap\Entity\User::getLoggedInUser();
        $student = \Fisdap\EntityUtils::getEntity("StudentLegacy", $studentId);
        
        $eurekaGoal = $def->eureka_goal;
        $eurekaWindow = $def->eureka_window;
    
        // set up some values we'll use for the key
        $successRate = round((($eurekaGoal/$eurekaWindow) * 100), 1, PHP_ROUND_HALF_UP);
        $lowSuccessRate = round(($successRate * .75), 1, PHP_ROUND_HALF_UP);
        $yellowLine = $successRate-1;
        $halfWindow = ceil($eurekaWindow / 2);
        $yMaxMin = $this->getYMaxMin($items);
        
        $returnContent = "<div class='eureka-wrapper'>";
        $returnContent .= 	"<div class='eureka-headers'>";
        $returnContent .= 		"<h2 class='eureka-header'>";
        $returnContent .=			$def->name . " attempts for ";
        
        if (($user->isInstructor() && $user->hasPermission('View Reports')) || ($student->user->id == $user->id)) {
            $returnContent .= 			($items[0]) ? $items[0]->student->user->getName() : $student->user->getName();
        } else {
            $returnContent .= 			"Anonymous";
        }
        
        $returnContent .=		"</h2>";
        $returnContent .= 		"<h3 class='eureka-subheader'>Lab Practice</h3>";
        $returnContent .= 	"</div>";
        
        // the success-list div needs some custom attributes
        $returnContent .= 	"<div class='success-list' data-ymax='" . $yMaxMin['max'] . "' data-ymin='" . $yMaxMin['min'] . "'";
        $returnContent .=							  "data-goal='" . $eurekaGoal . "' data-window='" . $eurekaWindow . "' >";
        
        // dump out a comma separted list of 0s and 1s
        $count = 0;
        foreach ($items as $item) {
            if (($item->evaluator_type->name == "Instructor" && $item->confirmed) || ($item->evaluator_type->name == "Student")) {
                $passed = ($item->passed) ? 1: 0;
                $returnContent .= $passed . ",";
                $count++;
            }
        }
        
        $returnContent .= 	"</div>";
        $returnContent .= 	"<div class='date-list'>";
        
        // dump out a comma separted list of dates
        foreach ($items as $item) {
            $returnContent .= $item->shift->start_datetime->format("n/j/Y") . ",";
        }
        
        $returnContent .= 	"</div>";
        
        // add in some helpful details/graph analysis
        $returnContent .= 	"<div class='details-wrapper'>";
        $returnContent .= 		"<div class='details'>";
        $returnContent .= 			"Number of attempts: " . $count . "<br />";
        $returnContent .= 			"Eureka Goal: " . $eurekaGoal . "/" . $eurekaWindow . " (" . $successRate . "% success rate)<br />";
        $returnContent .= 		"</div>";
        $returnContent .= 	"</div>";
        
        // finally add the key
        $returnContent .= 	"<div class='key-wrapper'>";
        $returnContent .= 		"<div class='key'>";
        $returnContent .= 			"<span class='left-justified'><div class='key-line red'></div> =</span>";
        $returnContent .=			"Success rate is less than " . $lowSuccessRate . "% in the last " . $halfWindow . " attempts<br />";
        $returnContent .=			"<div class='spacer'></div>";
        
        $returnContent .= 			"<span class='left-justified'><div class='key-line yellow'></div> =</span>";
        $returnContent .=			"Success rate is between " . $lowSuccessRate . "-" . $yellowLine . "% in the last " . $halfWindow . " attempts<br />";
        $returnContent .=			"<div class='spacer'></div>";
        
        $returnContent .= 			"<span class='left-justified'><div class='key-line green'></div> =</span>";
        $returnContent .= 			"Success rate is above " . $successRate . "% in the last " . $halfWindow . " attempts<br />";
        $returnContent .= 			"<div class='spacer'></div>";
        
        $returnContent .= 			"<span class='left-justified'><div class='key-line gray'></div> =</span>";
        $returnContent .= 			"Continued " . $successRate . "% success rate from eureka point<br />";
        $returnContent .= 			"<div class='spacer'></div>";
        
        $returnContent .= 			"<span class='left-justified'><div class='key-green-point'></div> =</span>";
        $returnContent .= 			"Moment of skill competence (" . $successRate . "% success rate over " . $eurekaWindow . " attempts)<br />";
        $returnContent .= 		"</div>";
        $returnContent .= 	"</div>";
        
        // close the eureka wrapper
        $returnContent .= "</div>";
        
        return $returnContent;
    }
    
    // determines the min/max of the y axis
    private function getYMaxMin($items)
    {
        $curSum = $curMax = $curMin = 0;

        foreach ($items as $item) {
            if (($item->evaluator_type->name == "Instructor" && $item->confirmed) || ($item->evaluator_type->name == "Student")) {
                $curSum += ($item->passed ? 1 : -1);
                if ($curSum > $curMax) {
                    $curMax = $curSum;
                }
                if ($curSum < $curMin) {
                    $curMin = $curSum;
                }
            }
        }
        
        return array('max' => $curMax, 'min' => $curMin);
    }
}
