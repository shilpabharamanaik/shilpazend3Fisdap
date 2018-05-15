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
 * Description of Signature
 *
 * @author astevenson
 */
class Fisdap_Form_Element_Signature extends Zend_Form_Element_Text
{
    public $signature = null;

    public function init()
    {
        $this->getView()->headScript()->appendFile("/js/signaturePad/assets/jquery.signaturepad.min.js");
        $this->getView()->headScript()->appendFile("/js/signaturePad/assets/json2.min.js");
        $this->getView()->headLink()->appendStylesheet("/js/signaturePad/assets/jquery.signaturepad.css");

        $this->getView()->headScript()->appendFile("/js/signaturePad/assets/flashcanvas.js", 'text/javascript', array('conditional' => 'lt IE 9'));
    }

    public function render()
    {
        $formContents = "";

        $name = $this->getName();

        $formContents = <<<FORM
			<div class="sigPad" style="width: 352px">
				<ul class="sigNav">
					<li class="clearButton"><a id='clear-sig-link' href="#clear">Clear</a></li>
				</ul>
				<div class="sig sigWrapper">
					<canvas class="pad" width="350" height="55"></canvas>
					<input type="hidden" id="signature" name="$name" class="output">
				</div>
			</div>
			<script>
				$(document).ready(function() {
					$('.sigPad').signaturePad({drawOnly: true, validateFields: false});
				});
			</script>
FORM;

        return $formContents;
    }
}
