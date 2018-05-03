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
 * This file contains an attachment picker
 */

/**
 * @package Fisdap
 *
 * @param integer $attachmentID
 */
class Zend_View_Helper_AttachmentPicker extends Zend_View_Helper_Abstract
{
    public function attachmentPicker($attachmentID = 0, $width = 300, $height = 55) {
        // Generate something unique so that we can show more than one signature
        // per page.
        $hash = str_replace(".", "", microtime(true));

        $signature = \Fisdap\EntityUtils::getEntity('Attachment', $attachmentID);

        $html = '
            <div class="attachment-picker">
                <div class="attachment-picker-existing">
                    <label>Please choose an existing attachment:</label>
                    <div class="attachment-picker-list">
                        <div class="picker-row">1</div>
                        <div class="picker-row">2</div>
                        <div class="picker-row">3</div>
                    </div>
                </div>
                <div class="attachment-picker-new">
                    <label>Attach a new file:</label>
                    <input type="file" />
                </div>
            </div>

        ';

        return $html;
    }
}