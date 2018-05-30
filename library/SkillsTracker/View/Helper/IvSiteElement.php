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
 * This file contains a view helper to render an iv site prompt
 */

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_IvSiteElement extends Zend_View_Helper_FormElement
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
    public function ivSiteElement($name, $value = null, $attribs = null)
    {
        $ivSite = \Fisdap\EntityUtils::getEntity('IvSite', $value);

        $siteName = $ivSite->name;
        $siteSide = $ivSite->side;

        $nameOptions = \Fisdap\Entity\IvSite::getFormOptions();
        $sideOptions = array(
            "left" => "left",
            "right" => "right",
        );


        $this->html .= "<div style='float:left; margin-top:.5em;'>" . $this->view->formSelect($name . "[name]", $siteName, array("class" => "site-name"), $nameOptions) . "</div>";
        $this->html .= $this->view->formRadio($name . "[side]", $siteSide, array("class" => "site-side"), $sideOptions);

        return $this->html;
    }
}
