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
 * This file contains a view helper to render an icon with a badge
 */

/**
 * @package Fisdap
 */
class Fisdap_View_Helper_BadgeIcon extends Zend_View_Helper_Abstract
{
    /**
     * Since this helper is sometimes explicitly instantiated as an object, we may need to pass it the view
     * @param null $view
     */
    public function __construct($view = null)
    {
        if ($view) {
            $this->view = $view;
        }
    }

    /**
     * The function to render the basic html
     *
     * @return string the HTML rendering the icon with a badge
     */
    public function badgeIcon($image, $title = null, $class = null, $href = "#", $badgeText = null, $badgeClass = null, $dataId = null)
    {
        $this->view->headScript()->appendFile("/js/library/Fisdap/View/Helper/badge-icon.js");

        // if this browser supports SVG and there's text to show, show the badge
        $browserSupportsSVG = \Util_Browser::supportsSVG();
        $isSVG = (substr($image, strlen($image)-4) == ".svg");
        $showBadge = ($badgeText && ($browserSupportsSVG || !$isSVG));

        // add appropriate alt text and styling for browsers that don't support svg
        $altText = ($badgeText) ? "$title ($badgeText)" : $title;
        $imageClass = "small-icon";
        $imageClass .= $isSVG ? " svg square" : "";

        $html = "<div class='has-badge'>
                    <a title='$title' class='$class' href='$href' dataId='$dataId'>
                        <img class='$imageClass' src='$image' alt='$altText'>";
        if ($showBadge) {
            $html .= "<span class='badge $badgeClass'>$badgeText</span>";
        }
        $html .=    "</a>
                </div>";
        
        return $html;
    }

    /**
     * The function to render the html for a shift attachment icon
     *
     * @return string the HTML rendering the icon with a badge
     */
    public function shiftAttachmentIcon($shiftId, $count = null)
    {
        $image = "/images/icons/attachment.svg";
        $title = "attachments";
        $class = null;
        $href = "/skills-tracker/shifts/my-shift/shiftId/$shiftId#attachments";
        $badgeText = $count;
        return $this->badgeIcon($image, $title, $class, $href, $badgeText);
    }

    /**
     * The function to render the html for a shift comment icon
     *
     * @return string the HTML rendering the icon with a badge
     */
    public function shiftCommentIcon($shiftId, $count = null)
    {
        $image = "/images/icons/comment.svg";
        $title = "comments";
        $class = "display-comment";
        $href = "/skills-tracker/shifts/comments/id/".$shiftId;
        $badgeText = $count;
        return $this->badgeIcon($image, $title, $class, $href, $badgeText);
    }

    /**
     * The function to render the html for a lock shift icon
     *
     * @return string the HTML rendering the icon with a badge
     */
    public function lockShiftIcon($shiftId, $locked, $lateData)
    {
        $image = $locked ? "/images/icons/locked.svg" : "/images/icons/unlocked.svg";
        $title = $locked ? "unlock shift" : "lock shift";
        $title .= $lateData ? " (late data)" : "";
        $class = "lock-shift-btn";
        $href = "#";
        $badgeText = $lateData ? "!" : null;
        $badgeClass = "red";
        return $this->badgeIcon($image, $title, $class, $href, $badgeText, $badgeClass, $shiftId);
    }

    /**
     * The function to render the html for a lock status icon (which just shows the status, doesn't do anything on click)
     *
     * @return string the HTML rendering the icon with a badge
     */
    public function lockStatusIcon($shiftId, $locked, $lateData)
    {
        $image = $locked ? "/images/icons/locked.svg" : "/images/icons/unlocked.svg";
        $title = $locked ? "locked shift" : "unlocked shift";
        $title .= $lateData ? " (late data)" : "";
        $class = "no-click";
        $href = "#";
        $badgeText = $lateData ? "!" : null;
        $badgeClass = "red";
        return $this->badgeIcon($image, $title, $class, $href, $badgeText, $badgeClass, $shiftId);
    }
}
