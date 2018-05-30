<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2013.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * View Helper to display a table of shared requirements grouped by category
 */

/**
 * @package Scheduler
 */
class Scheduler_View_Helper_SharedRequirementsTable extends Zend_View_Helper_Abstract
{
    protected $_html;
    
    public function sharedRequirementsTable($program_id)
    {
        // this gets all the requirements that are shared BY OTHER PROGRAMS
        // at sites in this program's sharing network
        $site_ids = \Fisdap\EntityUtils::getRepository('SiteLegacy')->getSharedSites($program_id);
        $requirements = \Fisdap\EntityUtils::getRepository("Requirement")->getSharedRequirementsByProgram($program_id, $site_ids);

        // get the categories
        $groupedReqs = array();
        $category_options = \Fisdap\EntityUtils::getRepository('RequirementCategory')->getFormOptions();
        foreach ($category_options as $category) {
            $groupedReqs[$category] = array();
        }
        
        // count and sort the reqs
        $req_count = 0;
        foreach ($requirements as $requirement) {
            $groupedReqs[$requirement->category->name][] = $requirement;
            $req_count++;
        }
                
        $networkOnly = true;
        if ($req_count > 0) {
            foreach ($groupedReqs as $category => $reqs) {
                if (count($reqs)) {
                    $this->_html .= "<div class='requirement-category'>";
                    $this->_html .= "<h4 class='dark-gray withTopMargin'>$category</h3>";
                    $this->_html .= "<div class='category-controls'><a href='#' class='expand-all'>expand all</a> | <a href='#' class='collapse-all'>collapse all</a></div>";
                    $this->_html .= "<div class='requirement-category-section'>";
                    
                    @usort($reqs, array('self', 'sortReqsByTitle'));
                    foreach ($reqs as $req) {
                        $attachmentInfo = \Fisdap\EntityUtils::getRepository("Requirement")->getAttachmentSummariesByRequirement($req, $program_id, $networkOnly);
                        
                        $this->_html .= $this->view->partial("sharedRequirementRow.phtml", array("requirement" => $req, "attachmentInfo" => $attachmentInfo));
                    }
                    $this->_html .= "</div></div>";
                }
            }
        } else {
            $this->_html .= "<div id='no-shared-reqs'>Your sharing network has not set up any requirements yet.</div>";
        }
        
        $this->_html .= "<div class='null-search notice' style='display: none;'></div>";
        
        return $this->_html;
    }
    
    public static function sortReqsByTitle($a, $b)
    {
        return strcasecmp($a->name, $b->name);
    }
}
