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
 * This helper will display a practice category
 */

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_PracticeCategoryTable extends Zend_View_Helper_Abstract
{

    /**
     * @var string the html to return
     */
    protected $returnContent;

    public function practiceCategoryTable($practiceCategory = null, $nameElement = null, $subFormElements = array())
    {
        $defRowViewHelper = new SkillsTracker_View_Helper_PracticeDefinitionRow();
        $formName = 'category' . $practiceCategory->id . '_cat_name';

        if (!$nameElement) {
            $nameElement = "<input type='text' name='" . $formName . "' id='" . $formName . "' value='New Category'>";
        }

        $returnContent = '<h3 class="bottom-rounded-corners">';
        $returnContent .= '<button id="' . $practiceCategory->id . '-button">';
        $returnContent .= '<span class="category-name">';
        $returnContent .= "<span class='category-name-static'>" . $practiceCategory->name . "</span>";
        $returnContent .= $nameElement;
        $returnContent .= '<div class="tooltip">';
        $returnContent .= '<div class="tooltip-text">click to change category name</div>';
        $returnContent .= '</div>';
        $returnContent .= '</span>';
        $returnContent .= '</button>';
        $returnContent .= '</h3>';

        $returnContent .= '<div class="category-content bottom-rounded-corners" id="' . $practiceCategory->id . '-content">';
        $returnContent .= '<div class="category-table-thead">';
        $returnContent .= "<div class='active-col'>Visibility</div>";
        $returnContent .= "<div class='def-name-col'>Item Name</div>";
        $returnContent .= "<div class='skillsheet-col'>Skillsheet</div>";
        $returnContent .= "<div class='skill-col'>Skill</div>";
        $returnContent .= "<div class='peer-col'>Peer<img src='/images/icons/lab-skills-peer-icon.png'></div>";
        $returnContent .= "<div class='instructor-col'>Instructor<img src='/images/icons/lab-skills-instructor-icon.svg'></div>";
        $returnContent .= "<div class='eureka-col'>Eureka<img src='/images/icons/lab-skills-eureka-icon-inverted.png'></div>";
        $returnContent .= "<div class='clear'></div>";
        $returnContent .= "</div>";
        $returnContent .= "<div class='clear'></div>";

        $returnContent .= "<div class='category-table-scrollable-content'>";
        $returnContent .= '<table class="fisdap-table category-table">';

        $returnContent .= "<tbody>";

        if (count($practiceCategory->practice_definitions) != 0) {
            foreach ($practiceCategory->practice_definitions as $practiceDef) {
                $returnContent .= $defRowViewHelper->practiceDefinitionRow($practiceCategory, $practiceDef, $subFormElements[$practiceDef->id]);
            }
        } else {
            $returnContent .= "<span class='no-practice-items'>To add practice items to this category, click the button below to get started.</span>";
        }

        $returnContent .= "</tbody>";
        $returnContent .= "</table>";
        $returnContent .= "</div>";

        $returnContent .= '<div class="add-item-wrapper"><img class="add-lpi-throbber" src="/images/throbber_small.gif"><a class="add-lab-practice-item" href="#"></a></div>';
        $returnContent .= '<div class="clear"></div>';

        $returnContent .= '</div>';

        return $returnContent;
    }
}
