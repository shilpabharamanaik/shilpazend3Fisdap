<?php

/****************************************************************************
 *
 *         Copyright (C) 1996-2011.  This is an unpublished work of
 *                          Headwaters Software, Inc.
 *                             ALL RIGHTS RESERVED
 *         This program is a trade secret of Headwaters Software, Inc.
 *         and it is not to be copied, distributed, reproduced, published,
 *         or adapted without prior authorization
 *         of Headwaters Software, Inc.
 *
 ****************************************************************************/

/**
 * A form element which allows the user to select an attachment
 *
 * @author astevenson
 */
class Fisdap_Form_Element_Attachment extends Zend_Form_Element_Text
{
    public $attachment = null;

    public function init()
    {

    }

    public function render()
    {
        // TODO, Print out attachments once we get them passed down to my render method
        // $attachments = $this->getAttachments();
        $hash = str_replace(".", "", microtime(true));

        $formContents = <<<FORM
			<div class="attachment-selector-switch" id="attachment-selector-switch-$hash" style="text-align:center;">
    			<input type="radio" id="attachment-selector-switch-$hash-existing" name="attachment-selector-switch-$hash" value="existing" checked="checked"><label for="attachment-selector-switch-$hash-existing">Existing</label>
	    		<input type="radio" id="attachment-selector-switch-$hash-new" name="attachment-selector-switch-$hash" value="new"><label for="attachment-selector-switch-$hash-new">New</label>
            </div>
            <div class="attachment-selector-panel attachment-selector-panel-existing" id="attachment-selector-panel-$hash-existing">
                <div style="border: 2px solid #ccc;min-height:70px;width:300px;">
                    <div id="attachment-selector-table-$hash" class="attachment-selector-table fisdap-table-scrolling-container">
                        <table fisdap-table scrollable">
                            <tbody>
                                <tr>
                                    <td>Thumb</td><td>IMG_2014_09_09.png</td>
                                </tr>
                                <tr>
                                    <td>Thumb</td><td>dailyevaluationform.png</td>
                                </tr>
                                <tr>
                                    <td>Thumb</td><td>ekg for patient number 4.png</td>
                                </tr>
                                <tr>
                                    <td>Thumb</td><td>IMG_2014_09_09.png</td>
                                </tr>
                                <tr>
                                    <td>Thumb</td><td>dailyevaluationform.png</td>
                                </tr>
                                <tr>
                                    <td>Thumb</td><td>ekg for patient number 4.png</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="attachment-selector-panel attachment-selector-panel-new" id="attachment-selector-panel-$hash-new" style="display:none;">
                <label for="attachment-selector-panel-$hash-new-file">Attach a new file:</label><input type="file" id="attachment-selector-panel-$hash-new-file" style="width:200px" />

            </div>
            <script>
              $(function(){
                $("#attachment-selector-switch-$hash").buttonset();
                $("#attachment-selector-switch-$hash").change(function(){
                    $("#attachment-selector-panel-$hash-existing").toggle();
                    $("#attachment-selector-panel-$hash-new").toggle();
                });
              });
            </script>

FORM;

        return $formContents;
    }
}
