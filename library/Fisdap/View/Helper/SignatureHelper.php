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
 * This file contains a view helper to render a page title
 *
 * Example usage:
 * In your controller
 * 		$this->view->pageTitle = "Page Title!!!"
 * 		$this->view->pageTitleLinks = array("This is a link" => "www.example.com");
 */

/**
 * @package Fisdap
 *
 * @param integer $signatureID The ID of the signature entity
 * @param integer $width The width, in pixels, of the desired signature canvas/image
 * @param integer $height The height, in pixels, of the desired signature canvas/image
 * @param string $method The method used to generate the signature. Either 'js' for create a CANVAS element, or 'php' to create an IMG element
 */
class Zend_View_Helper_SignatureHelper extends Zend_View_Helper_Abstract
{
    public function signatureHelper($signatureID = 0, $width = 300, $height = 55, $method = 'js')
    {
        if ($method == 'js') {
            // Generate something unique so that we can show more than one signature
            // per page.
            $hash = str_replace(".", "", microtime(true));

            $this->view->headScript()->appendFile("/js/signaturePad/assets/json2.min.js");
            $this->view->headScript()->appendFile("/js/signaturePad/assets/jquery.signaturepad.js");
            $this->view->headLink()->appendStylesheet("/js/signaturePad/assets/jquery.signaturepad.css");
            $this->view->headScript()->appendFile("/js/signaturePad/assets/flashcanvas.js", 'text/javascript', array('conditional' => 'lt IE 9'));
            
            $signature = \Fisdap\EntityUtils::getEntity('Signature', $signatureID);

            $width = $width + 2;
            $html = <<<HTML
				<div class="$hash-sigPad" style="width: {$width}px">
					<div class="sig sigWrapper">
						<canvas id="$hash-canvas" class="pad" width="$width" height="$height"></canvas>
					</div>
				</div>
				<script>
					$(document).ready(function() {
					    $('.$hash-sigPad').signaturePad({displayOnly:true, autoscale:true}).regenerate({$signature->signature_string});
					});
				</script>
HTML;
        } elseif ($method == 'php') {
            // Generate an image element where SRC points to a programatically-derived signature image
            // useful for PDF generation because wkhtmltopdf doesn't handle the canvas elements or inline IMG SRC='data:..' elements.
            $signature = \Fisdap\EntityUtils::getEntity('Signature', $signatureID);
            $html = '<div style="width: ' . ($width + 2) . 'px"><div class="sig-image">';
            $html .= '<img src="/pdf/sig-image/signatureId/' . $signature->id . '/width/' . $width . '/height/' . $height . '" />';
            $html .= '</div></div>';
        }

        return $html;
    }
}
