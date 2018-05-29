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
 * This file contains a view helper to render a groups prompt
 */

/**
 * @package Fisdap
 */
class Fisdap_View_Helper_GroupsElement extends Zend_View_Helper_FormElement
{
    /**
     * @var string the HTML to be rendered
     */
    protected $html = "";
    
    /**
     * The function to render the html
     *
     * @return string the HTML rendering the graduation date element
     */
    public function groupsElement($name, $value = null, $attribs = null)
    {
        //get data from values
        $id = isset($value['id']) ? $value['id'] : 0;
        $year = isset($value['year']) ? $value['year'] : 0;
        
        //Get the class section stuff
        $program = \Fisdap\Entity\ProgramLegacy::getCurrentProgram();
        $classSectionRepository = \Fisdap\EntityUtils::getRepository('ClassSectionLegacy');
        $classSectionYears = $classSectionRepository->getUniqueYears($program->id);
		$classSections = $classSectionRepository->getNamesByProgram($program->id, $year);
        
        $this->html .= $this->view->formSelect($name . "[year]", $year, array(), $classSectionYears);
        $this->html .= $this->view->formSelect($name . "[id]", $id, array(), $classSections);
        
        return $this->html;
    }
}