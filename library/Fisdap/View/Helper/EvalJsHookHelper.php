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
 * helper to the javascript array that helps map skills to eval hooks.
 */

/**
 * @package Fisdap
 */
class Zend_View_Helper_EvalJsHookHelper extends Zend_View_Helper_Abstract
{

    /**
     * @param integer $hookId ID of the hook to generate a link for.
     * @param integer $sessionId ID of the session that the eval should be tied
     * to.
     * @return string the html to render
     */
    public function evalJsHookHelper($program_id, $shift_type)
    {
        // figure out which hooks we're looking at by type
        $eval_hooks = array();
        switch ($shift_type) {
            case 'field':
                $eval_hooks['OtherProcedure_41'] = array('hook_id' => 5); // long board
                $eval_hooks['OtherProcedure_31'] = array('hook_id' => 6); // traction splint
                $eval_hooks['OtherProcedure_39'] = array('hook_id' => 7); // joint immobilization
                $eval_hooks['OtherProcedure_42'] = array('hook_id' => 12); // MD consult
                $eval_hooks['OtherProcedure_40'] = array('hook_id' => 15); // bandaging
                $eval_hooks['OtherProcedure_38'] = array('hook_id' => 18); // long bone
                $eval_hooks['AirwayProcedure_13'] = array('hook_id' => 20); // suction
                break;
            case 'clinical':
                break;
            case 'lab':
                $eval_hooks['OtherProcedure_41'] = array('hook_id' => 66); // long board
                $eval_hooks['OtherProcedure_31'] = array('hook_id' => 67); // traction splint
                $eval_hooks['OtherProcedure_39'] = array('hook_id' => 68); // joint immobilization
                $eval_hooks['OtherProcedure_42'] = array('hook_id' => 70); // MD consult
                $eval_hooks['OtherProcedure_40'] = array('hook_id' => 73); // bandaging
                $eval_hooks['OtherProcedure_33'] = array('hook_id' => 74); // c-spine
                $eval_hooks['OtherProcedure_38'] = array('hook_id' => 75); // long bone
                $eval_hooks['AirwayProcedure_10'] = array('hook_id' => 82); // orotracheal intubation
                $eval_hooks['AirwayProcedure_13'] = array('hook_id' => 76); // suction
                $eval_hooks['IvProcedure_1'] = array('hook_id' => 79); // IV
                $eval_hooks['IvProcedure_3'] = array('hook_id' => 80); // blood draw
                $eval_hooks['CardiacProcedure_1'] = array('hook_id' => 100); // chest compressions
                $eval_hooks['LabAssessment_1'] = array('hook_id' => 121);
                $eval_hooks['LabAssessment_2'] = array('hook_id' => 122);
                break;
        }

        // set up the corresponding javascript objects
        $js = "<script type='text/javascript'>\n";
        $js .= "var EvalHooks = new Object();\n";
        // loop through each hook and add a js object for it, if necessary
        foreach ($eval_hooks as $context => $hook_info) {
            $tmp_hook_id = $hook_info['hook_id'];
            $default_evals = \Fisdap\EntityUtils::getRepository("EvalHookDefaultsLegacy")->findByHook($tmp_hook_id);
            $program_evals = \Fisdap\EntityUtils::getRepository("EvalProgramHooksLegacy")->findBy(array('hook' => $tmp_hook_id, 'program' => $program_id));

            // if there are eval associated with this hook (default OR program), show the link
            if (count($default_evals) > 0 || count($program_evals) > 0) {
                $js .= "EvalHooks['" . $context . "'] = new Object();\n";
                $js .= "EvalHooks['" . $context . "'].hookid = " . $hook_info['hook_id'] . ";\n";
            }
        }

        $js .= "var evalLinkMarkup = \"<a target='_blank' ><img src='/images/icons/eval_icon.png' style='height: 25px; width: 17px;' alt='Eval'></a>\";\n";
        $js .= "var evalLinkURLPath = \"/oldfisdap/eval-hook\";\n";
        $js .= "</script>\n";

        return $js;
    }
}
