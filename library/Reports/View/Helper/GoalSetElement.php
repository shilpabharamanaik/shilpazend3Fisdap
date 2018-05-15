<?php
/*	*	*	*	*	*	*	*	*
 *
 *	Copyright (C) 1996-2011.  This is an unpublished work of
 *			Headwaters Software, Inc.
 *				ALL RIGHTS RESERVED++++
 *	This program is a trade secret of Headwaters Software, Inc.
 *	and it is not to be copied, distributed, reproduced, published,
 *	or adapted without prior authorization
 *	of Headwaters Software, Inc.
 *
 *	*	*	*	*	*	*	*	*/

/**
 * This file contains a view helper to render an iv site prompt
 */

/**
 * @package SkillsTracker
 */
class Reports_View_Helper_GoalSetElement extends Zend_View_Helper_FormElement
{

    /**
     * @var string the HTML to be rendered
     */
    protected $html = "";

    /**
     * The function to render the html
     *
     * @return string the HTML rendering the age date element
     */
    public function goalSetElement($name, $value = null, $attribs = null, $options = null)
    {
        $attribs['class'] = 'goal-set-name';
        $loggedInUser = \Fisdap\Entity\User::getLoggedInUser();
        
        $this->html = "
			<div class='report-block'>
				<h3 class='section-header'>Choose a goal set</h3>
				<div class='report-body'>
					<div class='grid_12'>Goals:
					" . $this->view->formSelect($name, $value, $attribs, $value);
        if ($loggedInUser->getCurrentRoleName() == 'instructor') {
            $this->html .= "<a href=\"/reports/goal/customize\">edit</a>";
        }
        $this->html .= "</div></div></div>";
        
        return $this->html;
    }
}
