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
 * Helper to create a link to an eval.
 */

/**
 * @package Fisdap
 */
class Zend_View_Helper_ShiftEvalHookHelper extends Zend_View_Helper_Abstract
{

	/**
	 * @param string $context A context specific eval.  Basically serves as a 
	 * lookup in the eval_hooks arrays.  Not the cleanest thing, but functional.
	 * @param object $shift The shift that is getting the eval
	 * @param Boolean $useSmileyIcons Flag to determine whether or not to use
	 * the smiley icons.  Defaults to false, needs to be explicitely turned on.
	 * 
	 * @return string the html to render
	 */
	public function shiftEvalHookHelper($context, $shift, $useSmileyIcons=false, $includeOnclick=true)
	{
		// set up hook map based on shift type
		$shift_type = $shift->type;
		$eval_hooks = array();
		
		switch ($shift_type) {
			case 'field':
				$eval_hooks['primary_impression'] = array('hook_id' => 23); 
				$eval_hooks['secondary_impression'] = array('hook_id' => 24);
				$eval_hooks['team_lead'] = array('hook_id' => 1); 
				$eval_hooks['preceptor'] = array('hook_id' => 2);
				break;
			case 'clinical':
				$eval_hooks['primary_impression'] = array('hook_id' => 19);
				$eval_hooks['secondary_impression'] = array('hook_id' => 40);
				break;
			case 'lab':
				$eval_hooks['primary_impression'] = array('hook_id' => 60);
				$eval_hooks['secondary_impression'] = array('hook_id' => 61);
				$eval_hooks['team_lead'] = array('hook_id' => 60); // Need to create this hook
				$eval_hooks['preceptor'] = array('hook_id' => 61); // Need to create this hook
				break;
		}
		
		$hook_id = $eval_hooks[$context]['hook_id'];
		
		// if there is a hook mapped to this skill type...
		if ($hook_id > 0) {
			$program_id = $shift->student->program->id;
			$default_evals = \Fisdap\EntityUtils::getRepository("EvalHookDefaultsLegacy")->findByHook($hook_id);
			$program_evals = \Fisdap\EntityUtils::getRepository("EvalProgramHooksLegacy")->findBy(array('hook' => $hook_id, 'program' => $program_id));

			// if there are eval associated with this hook (default OR program), show the link
			if (count($default_evals) > 0 || count($program_evals) > 0) {
				$shift_id = $shift->id;

				// Always default to use just the standard eval icon...
				$evalIcon = "eval_icon";

				$filledOutEvals = \Fisdap\Entity\EvalSessionLegacy::getUsersCompletedEvals($shift_id, $hook_id);

				if (count($filledOutEvals) > 0) {
					if ($useSmileyIcons) {
						$passedSomething = false;
						$failedSomething = true;

						foreach ($filledOutEvals as $e) {
							if ($e->passed == 1) {
								$passedSomething = true;
							} else {
								$failedSomething = true;
							}
						}

						// If they've passed something and not failed something...
						if ($passedSomething && !$failedSomething) {
							$evalIcon = "eval_icon_happy";
							// If they've not passed something but failed something...
						} elseif (!$passedSomething && $failedSomething) {
							$evalIcon = "eval_icon_sad";
							// If they've done none of the above...
						} else {
							$evalIcon = "eval_icon_meh";
						}
					} else {
						$evalIcon = "eval_icon_gray";
					}
				}

				$evalURL = '/oldfisdap/eval-hook/hid/' . $hook_id . '/sid/' . $shift_id;
				
				$html = '<a target="_blank" href="' . $evalURL . '" ';
				if($includeOnclick){
					$html .= 'onclick="window.open(\'' . $evalURL . '\', \'_blank\', \'width=1020,height=700\'); return false"';
				}
				$html .= '>' .
						'<img src="/images/icons/' . $evalIcon . '.png" alt="Eval"/>' .
						'</a>';
			} else {
				$html = "";
			}
		} else {
			$html = "";
		}

		return $html;
	}

}