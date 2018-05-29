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
 * This file contains a view helper to render a form element in a modal
 */

/**
 * @package Fisdap
 */
class Fisdap_View_Helper_ModalFormElement extends Zend_View_Helper_FormElement
{
    /**
     * @var string the HTML to be rendered
     */
    protected $html = "";
    
    /**
     * The function to render the html
     *
     * @return string the HTML rendering the element
     */
    public function modalFormElement($element)
    {

        $this->html  = '<div class="form-prompt">';
        $this->html .= '<label class="label" for="'.$element->getName().'">'.
                            $element->getLabel();

        if ($element->getDescription()) {
            $this->html .= '<span class="form-desc">'.$element->getDescription().'</span>';
        }

        $this->html .= '</label>';


        $this->html .= '<div class="input">'.$element.'</div>';
        $this->html .= '</div>';
        
        return $this->html;
    }
}