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

/** Adds test around element. APPENDS by default.
 * Options available:
 * 'text'	- text to output
 * 'class'	- default is: 'add_text_decorator'
 * 'tag'	- default is: 'div'
 * 		if 'tag' => '', tag won't be added
 * 		Only adds exactly what is given in 'text' option
 *  standard: 'placement' => 'APPEND' / 'PREPEND' is supported
*/
class Zend_Form_Decorator_AddText extends Zend_Form_Decorator_Abstract
{
    public function render($content)
    {
        $placement = $this->getPlacement();
        $text = $this->getOption('text');
        $class = $this->getOption('class');
        $tag = $this->getOption('tag');
        //$notags = $this->getOption('no_tags');

        if (!$class) {
            $class = 'add_text';
        }
        if (is_null($tag)) {
            $tag = 'div';
        }
        
        //$element = $this->getElement();
        if ($tag) {
            $output = "<$tag class =\"$class\">$text</$tag>";
        } else {
            $output = $text;
        }
        switch ($placement) {
            case 'PREPEND':
                return $output . $content;
            case 'APPEND':
            default:
                return $content . $output;
        }
    }
}
