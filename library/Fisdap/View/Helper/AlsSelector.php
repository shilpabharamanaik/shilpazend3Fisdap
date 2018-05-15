<?php

/* ***************************************************************************
 *
 *         Copyright (C) 1996-2014.  This is an unpublished work of
 *                          Headwaters Software, Inc.
 *                             ALL RIGHTS RESERVED
 *         This program is a trade secret of Headwaters Software, Inc.
 *         and it is not to be copied, distributed, reproduced, published,
 *         or adapted without prior authorization
 *         of Headwaters Software, Inc.
 *
 * ************************************************************************** */

/**
 * table to select a goal set and link to editing goalsets
 * @package Fisdap
 * @author khanson
 */
class Fisdap_View_Helper_AlsSelector extends Zend_View_Helper_Abstract
{
    public function alsSelector($config = array(), $options = array())
    {
        $user = \Fisdap\Entity\User::getLoggedInUser();
        // JS / CSS for the widget
        $this->view->headScript()->appendFile("/js/library/Fisdap/View/Helper/als-definition-selector.js");
        $this->view->headLink()->appendStylesheet("/css/library/Fisdap/View/Helper/als-definition-selector.css");

        // check which option is checked based on the configuration, default to "fisdap" definition
        if ($config['als-type'] == "als_skill") {
            $checkFisdap = "";
            $checkALS = "checked='checked'";
            $checkCA = "";
        } elseif ($config['als-type'] == "california") {
            $checkFisdap = "";
            $checkALS = "";
            $checkCA = "checked='checked'";
        } else {
            $checkFisdap = "checked='checked'";
            $checkALS = "";
            $checkCA = "";
        }

        $html = "
			<div id='als-definition-selector' class='grid_6 definition-selector'>
				<h4 class='sub-heading'>ALS definition:</h4>
				<div id='als-def-radio' class='extra-small'>
					<input type='radio' id='als-type-fisdap' name='als-type' $checkFisdap value='fisdap'><label for='als-type-fisdap'>Fisdap</label>
					<input type='radio' id='als-type-als' name='als-type' $checkALS value='als_skill'><label for='als-type-als'>ALS Skill</label>
					<input type='radio' id='als-type-ca' name='als-type' $checkCA value='california'><label for='als-type-ca'>CA ALS</label>
				</div>
				<div id='fisdap-definition-description' class='form-desc'>
					Using Fisdap's definition, a call will be considered an ALS call if either:<br>
					<ol>
						<li>A medication other than oxygen is administered (by anyone on the team)</li>
						or
						<li>An ECG monitor and an IV (attempt) are performed together (by anyone on the team).</li>
					</ol>
				</div>
				<div id='als-skill-definition-description' class='form-desc'>
					Using the ALS Skill definition, a call will be considered an ALS call if any ALS skill is performed (by anyone on the team).
				</div>
				<div id='california-definition-description' class='form-desc'>
					Using the California definition, a call will be considered an ALS call if any ALS skill other than 12-lead EKG and Blood Glucose is performed (by the student)
				</div>
			</div>";

        return 	$html;
    }

    /**
     * @param array $options
     * @param array $config
     * @return mixed
     */
    public function ALSSelectorSummary($options = array(), $config = array())
    {
        // get the ALS definition
        $type = $config['als-type'];
        $friendlyName = "";
        switch ($type) {
            case 'fisdap':
                $friendlyName = 'Fisdap';
                break;
            case 'als_skill':
                $friendlyName = 'ALS Skill';
                break;
        }
        $summary["ALS Definition"] = $friendlyName;

        return $summary;
    }

    public function ALSSelectorValidate($options = array(), $config = array())
    {
        $errors = array();
        // validate date ranges
        if ($config['als-type'] != 'fisdap' && $config['als-type'] != 'als_skill' && $config['als-type'] != 'california') {
            $errors['als-type'][] = 'Please select an ALS Skill definition type.';
        }

        return $errors;
    }
}
