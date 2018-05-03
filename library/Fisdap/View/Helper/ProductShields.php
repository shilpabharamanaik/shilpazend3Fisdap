<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2014.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

use Fisdap\Entity\User;
use Fisdap\Service\ProductService;

/**
 * View Helper to display a pretty list of product shields, with links!
 */

/**
 * @package Fisdap
 */
class Fisdap_View_Helper_ProductShields extends Zend_View_Helper_Abstract
{
    protected $_html;

    public $view;

    public function __construct($view = null)
    {
        if ($view) {
            $this->view = $view;
        }
    }

    public function productShields($configuration, $student) {
        $user = User::getLoggedInUser();
        $productService = new ProductService();

        $icons = $productService->getProductIcons($configuration);
        $titles = $productService->getProductTitles($configuration);

        $this->_html = "<div class='product-icons-container'>";
        foreach($icons as $product => $icon) {
            switch($product) {
                case "skills_tracker":
                    $link = ($user->hasPermission("View All Data")) ? "/skills-tracker/shifts/index/studentId/" . $student->id : "";
                    break;

                case "scheduler":
                    $link = ($user->hasPermission("View Schedules")) ? "/scheduler?userRoleId=" . $student->user_role->id : "";
                    break;

                case "testing":
                    $link = ($user->hasPermission("Admin Exams")) ? "/learning-center/index/retrieve/studentId/" . $student->id : "";
                    break;

                case "study_tools":
                case "transition_course":
                case "entrance_exam":
                    $link = "/learning-center/index/retrieve/studentId/" . $student->id;
                    break;

                default:
                    $link = "";
                    break;
            }

            $this->_html .= $this->getLinkShield($icon, $titles[$product], $link);
        }
        $this->_html .= "</div>";

        return $this->_html;
    }

    public function getLinkShield($icon, $title, $link = "") {
        $shieldText = "";

        if ($link) { $shieldText .= "<a title='$title' href='$link'>"; }

        $shieldText .= "<img class='product-shield' alt='$title' src='$icon'>";

        if ($link) { $shieldText .= "</a>"; }

        return $shieldText;
    }
}