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
 * This helper will render HTML that can be used with the eurekaGraph() jQuery plugin
 *
 *
 * @author Hammer
 */

class Fisdap_View_Helper_EurekaGraph extends Zend_View_Helper_Abstract
{
    /**
     * @var string the html to be rendered
     */
    protected $_html;

    public function eurekaGraph($attempts = null, $attempt_dates = null, $goal = 16, $window = 20, $unique_id = "eureka_home", $include_legend = true, $custom_details_msg = "")
    {
        // set up some values we'll use for the key
        $success_rate = round((($goal/$window) * 100), 1, PHP_ROUND_HALF_UP);
        $low_success_rate = round(($success_rate * .75), 1, PHP_ROUND_HALF_UP);
        $yellow_line = $success_rate-1;
        $half_window = ceil($window / 2);
        $yMaxMin = $this->getYMaxMin($attempts);
        
        $empty_eureka_home_class = (count($attempts) > 0) ? "" : "empty_eureka_home";
        
        $this->_html .=  "<div class='graph_wrapper' data-nonlabpractice='1'>";
        $this->_html .= 	"<div class='eureka_graph_content'>";
        $this->_html .= 		"<div class='eureka-wrapper'>";
        $this->_html .= 			"<div class='eureka-headers'>";
        $this->_html .= 				"<h2 class='eureka-header'>";
        $this->_html .=				"</h2>";
        $this->_html .= 				"<h3 class='eureka-subheader'></h3>";
        $this->_html .= 			"</div>";
        $this->_html .=		"</div>";
        
        $this->_html .=		$this->buildSuccessListElement($yMaxMin, $goal, $window, $attempts);
        $this->_html .=		$this->buildDatesElement($attempt_dates);
        $this->_html .=		$this->buildDetailsWrapper(count($attempts), $goal, $window, $success_rate, $custom_details_msg);
        
        $this->_html .=		"<div class='eureka_home " . $empty_eureka_home_class . "' id='" . $unique_id . "'></div>";
        $this->_html .=		($include_legend) ? $this->buildKey($low_success_rate, $half_window, $yellow_line, $window, $success_rate) : "";
        
        $this->_html .=  "</div>";
        $this->_html .= "</div>";
        
        return $this->_html;
    }
    
    public function buildKey($low_success_rate, $half_window, $yellow_line, $window, $success_rate)
    {
        // finally add the key
        $returnContent = "";
        $returnContent .= 	"<div class='key-wrapper'>";
        $returnContent .= 		"<div class='key'>";
        $returnContent .= 			"<span class='left-justified'><div class='key-line red'></div></span>";
        $returnContent .=			"Success rate is less than " . $low_success_rate . "% in the last " . $half_window . " attempts";
        $returnContent .=			"<div class='spacer'></div><div class='clear'></div>";
        
        $returnContent .= 			"<span class='left-justified'><div class='key-line yellow'></div></span>";
        $returnContent .=			"Success rate is between " . $low_success_rate . "-" . $yellow_line . "% in the last " . $half_window . " attempts";
        $returnContent .=			"<div class='spacer'></div><div class='clear'></div>";
        
        $returnContent .= 			"<span class='left-justified'><div class='key-line green'></div></span>";
        $returnContent .= 			"Success rate is " . $success_rate . "% or above in the last " . $half_window . " attempts";
        $returnContent .= 			"<div class='spacer'></div><div class='clear'></div>";
        
        $returnContent .= 			"<span class='left-justified'><div class='key-line gray'></div></span>";
        $returnContent .= 			"Continued " . $success_rate . "% success rate from eureka point";
        $returnContent .= 			"<div class='spacer'></div><div class='clear'></div>";
        
        $returnContent .= 			"<span class='left-justified'><div class='key-green-point'></div></span>";
        $returnContent .= 			"Moment of skill competence (" . $success_rate . "% success rate over " . $window . " attempts)";
        $returnContent .= 		"</div>";
        $returnContent .= 	"</div>";
        
        return $returnContent;
    }
    
    public function buildDetailsWrapper($count, $goal, $window, $success_rate, $custom_details_msg)
    {
        // add in some helpful details/graph analysis
        $returnContent = "";
        $returnContent .= 	"<div class='details-wrapper'>";
        $returnContent .=		($custom_details_msg) ? "<div class='eureka_custom_details_msg'>" . $custom_details_msg . "</div>" : "";
        $returnContent .= 		"<div class='details'>";
        $returnContent .= 			"Number of attempts: " . $count . "<br />";
        $returnContent .= 			"Eureka Goal: " . $goal . "/" . $window . " (" . $success_rate . "% success rate)<br />";
        $returnContent .= 		"</div>";
        $returnContent .= 	"</div>";
        
        return $returnContent;
    }
    
    public function buildDatesElement($attempt_dates)
    {
        $html  = "";
        $html .= "<div class='date-list'>";
        
        // dump out a comma separted list of dates
        if ($attempt_dates) {
            foreach ($attempt_dates as $attempt_date) {
                $html .= $attempt_date->format("n/j/Y") . ",";
            }
        }
        
        $html .= 	"</div>";
        
        return $html;
    }
    
    public function buildSuccessListElement($yMaxMin, $goal, $window, $attempts)
    {
        // the success-list div needs some custom attributes
        $html  = "";
        
        $html .= 	"<div class='success-list' data-ymax='" . $yMaxMin['max'] . "' data-ymin='" . $yMaxMin['min'] . "'";
        $html .=							  "data-goal='" . $goal . "' data-window='" . $window . "' >";
        $html .=		($attempts) ? implode(",", $attempts) : "";
        $html .=	"</div>";
        
        return $html;
    }
    
    // determines the min/max of the y axis
    private function getYMaxMin($attempts)
    {
        $curSum = $curMax = $curMin = 0;
        
        if ($attempts) {
            foreach ($attempts as $attempt) {
                $curSum += ($attempt ? 1 : -1);
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
