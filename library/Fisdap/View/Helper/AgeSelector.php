<?php

class Fisdap_View_Helper_AgeSelector extends Zend_View_Helper_Abstract
{
    public function ageSelector($config = array(), $options = array())
    {
        $user = \Fisdap\Entity\User::getLoggedInUser();
        // JS / CSS for the widget
        $this->view->headScript()->appendFile("/js/library/Fisdap/View/Helper/age-definition-selector.js");
        $this->view->headLink()->appendStylesheet("/css/library/Fisdap/View/Helper/als-definition-selector.css");

        // create the goal set selector
        $goalsetPicker = new Zend_Form_Element_Select("ageGoalset");
        $goalsetPicker->setLabel("")
            ->setDecorators(array("ViewHelper"))
            ->setAttribs(array("class" => "chzn-select",
                "data-placeholder" => "",
            ));
        $goalSets = \Fisdap\EntityUtils::getRepository('Goal')->getProgramGoalSets($user->getCurrentProgram()->id, true);
        $goalsetOptions = array();
        foreach ($goalSets as $goalset) {
            $goalsetOptions[$goalset->id] = $goalset->name;
        }
        $goalsetPicker->setMultiOptions($goalsetOptions);
        $selectedGoalsetId = ($config['goalset'] > 0) ? $config['goalset'] : 1;
        $selectedGoalset = \Fisdap\EntityUtils::getEntity('GoalSet', $selectedGoalsetId);
        $goalsetPicker->setValue($selectedGoalsetId);

        $html = "
			<div id='age-definition-selector' class='grid_6 definition-selector'>
				<h4 class='sub-heading'>Age definitions:</h4>".
            $goalsetPicker->render().
            "<div id='age-definitions' class='form-desc'>".
            $this->view->partial('age-definitions.phtml', array("goalset" => $selectedGoalset)).
            "</div>";
        if ($user->getCurrentRoleName() == "instructor" && $user->hasPermission("Edit Program Settings")) {
            $html .=
                "<div class='manage-link'>
					<a href='/reports/settings/manage-goalsets'>Manage goalsets</a>
				</div>";
        }

        $html .= "</div>";

        return 	$html;
    }

    /**
     * @param array $options
     * @param array $config
     * @return mixed
     */
    public function AgeSelectorSummary($options = array(), $config = array())
    {


        // get the Goal set upon which the definitions are based
        $selectedGoalset = \Fisdap\EntityUtils::getEntity('GoalSet', $config['goalset']);

        $summary["Team Lead/Ages defined by"] = $selectedGoalset->name;

        return $summary;
    }

    public function DefinitionsSelectorValidate($options = array(), $config = array())
    {
        $errors = array();
        // validate date ranges
        if ($config['als-type'] != 'fisdap' && $config['als-type'] != 'als_skill' && $config['als-type'] != 'california') {
            $errors['als-type'][] = 'Please select an ALS Skill definition type.';
        }

        return $errors;
    }
}
