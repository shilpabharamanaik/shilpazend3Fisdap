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
 * This file contains a view helper to render an attachment
 */

/**
 * @package Fisdap
 *
 * @return string html
 */
class Fisdap_View_Helper_ViewAttachment extends Zend_View_Helper_Abstract
{
    protected $_html;
    
    public function viewAttachment($attachment, $associatedEntityId, $attachmentType, $type = null)
    {
        //var_dump($attachment);
        $attachment->categoryString = is_array($attachment->categories) ? implode(", ", $attachment->categories) : "";
        $attachment->date = new \DateTime($attachment->created);
        $downloadUrl = "/download/download-attachment/attachment/$attachment->id/attachmentType/$attachmentType/associatedEntityId/$associatedEntityId";

        // first provide some info about the attachment
        $this->_html = "<div class='view-attachment'>
                            <div class='name-column'>
                                <h4 class='$type'>";
        $this->_html .=             $attachment->nickname ? $attachment->nickname : $attachment->fileName;
        $this->_html .=         "</h4>
                                <div>".$attachment->categoryString."</div>
                            </div>
                            <div class='date-column'>
                                <div class='date'>".$attachment->date->format('M j, Y | H:i:s')."</div>
                            </div>
                            <div class='download-column small gray-button'>
                                <a href='$downloadUrl' target='_blank' class='button icon-button'>
                                    <img src='/images/icons/download.svg' class='icon'>
                                    <div class='text'>Download</div>
                                </a>
                            </div>
                        </div>";

        // then the image itself, or the no preview message if there is no image available
        if ($attachment->variationUrls->medium) {
            $this->_html .= "<div class='image'><img src='".$attachment->variationUrls->medium."'></div>";
        } elseif (!$attachment->processed && $attachment->preview['type'] == "image") {
            $this->_html .= "<div class='image'><img src='".$attachment->preview['src']."'></div>";
        } else {
            $this->_html .= "
                <div class='no-preview light-gray-bg'>
                    <img src='".$attachment->preview['src']."'>
                    <div class='no-preview-msg'>No preview available</div>
                </div>";
        }

        // and finally the notes
        $this->_html .= "<div class='form-desc'>".$attachment->notes."</div>";

        $this->_html .= "</div>";
        
        return $this->_html;
    }
}
