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
 * This file contains a view helper to render an age prompt
 */

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_RatingElement extends Zend_View_Helper_FormElement
{
    /**
     * @var string the HTML to be rendered
     */
    protected $html = "";

    /**
     * The function to render the html
     *
     * @param      $name
     * @param null $value
     * @param null $attribs
     *
     * @return string the HTML rendering the age date element
     */
    public function ratingElement($name, $value = null, $attribs = null)
    {
        $this->view->jQuery()->addOnLoad("$('#rating-set-$name').buttonset();");
        $this->view->jQuery()->addOnLoad(new Zend_Json_Expr(
            "var buttons = $('#$name-disabled').click(function() {
				var buttonset = $('#rating-set-$name');
				if ($(this).is(':checked')) {
					buttonset.buttonset('option', 'disabled', true );
					//buttonset.children(':radio').val([]);
				} else {
					buttonset.buttonset('option', 'disabled', false );
				}
			});
			if (buttons.is(':checked')) {
				var buttonset = $('#rating-set-$name');
				buttonset.buttonset('option', 'disabled', true );
			}"
        ));

        //get data from values
        $rating = isset($value) ? $value : null;
        $options = array(
            0 => 0,
            1 => 1,
            2 => 2,
        );
        if (isset($attribs['disabled'])) {
            $disabledAttribs['disabled'] = $attribs['disabled'];
        } else {
            $disabledAttribs = array();
        }

        $this->html = "<span id='rating-set-$name' class='cupertino'>";
        $this->html .= $this->view->fisdapFormRadio($name . "[rating]", $rating, $disabledAttribs, $options, "");
        $this->html .= "</span>";

        //Add the c
        $disabledAttribs['checked'] = ($rating == -1);
        $this->html .= $this->view->formCheckbox($name . "[disabled]", null, $disabledAttribs) . $this->view->formLabel($name . "[disabled]", "N/A");

        
        
        return $this->html;
    }
}
