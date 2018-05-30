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

/**
 * This file contains a view helper to render the basic info about an attachment
 */

/**
 * @package Fisdap
 *
 * @return string html
 */
class Fisdap_View_Helper_AttachmentInfo extends Zend_View_Helper_Abstract
{
    protected $_html;

    public function attachmentInfo($attachment, $titleClass)
    {
        $title = $attachment->nickname ? $attachment->nickname : $attachment->fileName;
        $categoryString = is_array($attachment->categories) ? implode(", ", $attachment->categories) : "";
        $notes = strlen($attachment->notes) < 80 ?  $attachment->notes : substr($attachment->notes, 0, 79) . "...";

        $html = "<h4 class='$titleClass'>$title</h4>".
                "<div class='form-subtitle'>$categoryString</div>".
                "<div class='form-desc'>$notes</div>";

        return $html;
    }

    public function attachmentDownloadPreviewLink($attachment, $associatedEntityId, $imageClass)
    {
        $downloadLink = "/download/download-attachment/attachment/".$attachment->id."/attachmentType/shift/associatedEntityId/".$associatedEntityId;
        $html = "<div class='download-preview $imageClass' title='download attachment'>".
                "<a href='$downloadLink' target='_blank'>".
                "<img src='".$attachment->preview['src']."' alt='download attachment' class='$imageClass'>";

        // add the download hover image, if supported
        if (\Util_Browser::supportsSVG()) {
            $html .= "<img src='/images/icons/download.svg' class='download-icon'>";
        }

        $html .= "</a></div>";

        return $html;
    }
}
