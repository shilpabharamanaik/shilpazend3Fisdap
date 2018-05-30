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
 * View Helper to display a table requirements grouped by category
 */

/**
 * @package Scheduler
 */
class Scheduler_View_Helper_ManageRequirementsTable extends Zend_View_Helper_Abstract
{
    protected $_html;
    
    public function manageRequirementsTable($program_id, $filters = array(), $pending = array())
    {
        //Check to see if we were given any filters before adding default ones.
        $noFilters = empty($filters);
        
        //Set default filters if none exist
        if ($noFilters) {
            if (!array_key_exists("active", $filters)) {
                $filters['active'] = false;
            }
            
            if (!array_key_exists("siteRequirements", $filters)) {
                $filters['siteRequirements'] = 1;
            }
            
            if (!array_key_exists("programRequirements", $filters)) {
                $filters['programRequirements'] = 1;
            }
        }
        
        $include_program_level = $filters['programRequirements'];
        $include_site_level = $filters['siteRequirements'];
        
        $requirements = \Fisdap\EntityUtils::getRepository("Requirement")->getRequirements($program_id, $include_program_level, $include_site_level, false, false, $filters);
    
        // get the categories
        $groupedReqs = array();
        $category_options = \Fisdap\EntityUtils::getRepository('RequirementCategory')->getFormOptions();
        foreach ($category_options as $category) {
            $groupedReqs[$category] = array();
        }
        
        // count and sort the reqs
        $req_count = 0;
        if ($filters['siteRequirements']) {
            foreach ($requirements['site_level'] as $req) {
                $groupedReqs[$req->category->name][] = $req;
                $req_count++;
            }
        }
        
        if ($filters['programRequirements']) {
            foreach ($requirements['program_level'] as $req) {
                $groupedReqs[$req->category->name][] = $req;
                $req_count++;
            }
        }
        if ($req_count > 0) {
            foreach ($groupedReqs as $category => $reqs) {
                if (count($reqs)) {
                    $this->_html .= "<div class='requirement-category'>";
                    $this->_html .= "<h4 class='dark-gray withTopMargin'>$category</h3>";
                    $this->_html .= "<div class='category-controls'><a href='#' class='expand-all'>expand all</a> | <a href='#' class='collapse-all'>collapse all</a></div>";
                    $this->_html .= "<div class='requirement-category-section'>";
                    
                    @usort($reqs, array('self', 'sortReqsByTitle'));
                    foreach ($reqs as $req) {
                        $attachmentInfo = \Fisdap\EntityUtils::getRepository("Requirement")->getAttachmentSummariesByRequirement($req, $program_id);
                        $pendingEdits = in_array($req->id, $pending);

                        $this->_html .= $this->view->partial(
                            "manageRequirementRow.phtml",
                            array("requirement" => $req,
                                "attachmentInfo" => $attachmentInfo,
                                "pendingEdits" => $pendingEdits
                            )
                        );
                    }
                    $this->_html .= "</div></div>";
                }
            }
        } elseif ($noFilters) {
            $this->_html .= "<div id='no-reqs'>Your program has not set up any requirements yet.</div>";
        } else {
            $this->_html .= "<div class='error'>No requirements were found with the given search criteria.</div>";
        }
        
        $this->_html .= "<div class='null-search notice' style='display: none;'></div>";
        
        return $this->_html;
    }
    
    public static function sortReqsByTitle($a, $b)
    {
        return strcasecmp($a->name, $b->name);
    }
}
