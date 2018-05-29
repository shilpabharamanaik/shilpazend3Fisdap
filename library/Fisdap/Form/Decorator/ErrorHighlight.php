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
 * Custom decorator for adding an error class to elements that failed
 */


/**
 * @package Fisdap
 */
class Fisdap_Form_Decorator_ErrorHighlight extends Zend_Form_Decorator_Abstract
{

	/**
     * Decorate content and/or element to include an error class
     *
     * @param  string $content
     * @return string
     */
	public function render($content)
	{
		$element = $this->getElement();
		if ($element->hasErrors()) {
			$label = $element->getDecorator('label');
			$labelDesc = $element->getDecorator('LabelDescription');
			if ($label) {
				$class = $label->getOption('class');
				$label->setOption('class', $class . ' form-element-error');
			} else if ($labelDesc) {
				$class = $labelDesc->getOption('class');
				$labelDesc->setOption('class', $class . ' form-element-error');
			} else {
				$element->setAttrib('class', ' form-element-error');
			}
		}
		return $content;
	}
}
