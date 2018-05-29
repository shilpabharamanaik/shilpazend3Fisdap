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
class Zend_View_Helper_SkillEvalHookHelper extends Zend_View_Helper_Abstract
{

	/**
	 * @param string $context What kind of hook this is
	 * @param object $skill The skill that has been performed
	 * @param Boolean $useSmileyIcons Flag to determine whether or not to use
	 * the smiley icons.  Defaults to false, needs to be explicitely turned on.
	 * 
	 * @return string the html to render
	 */
	public function skillEvalHookHelper($context, $skill, $useSmileyIcons=false)
	{
		// set up hook map based on shift type
		// NOTE: we may want to move this map elsewhere, like in a EvalHook entity
		// (which doesn't currently exist), especially since there
		// is a very similar map in the EvalJsHookHelper, but for now it's here
		$shift_type = $skill->shift->type;
		$eval_hooks = array();
		switch ($shift_type) {
			case 'field':
				$eval_hooks['other41'] = array('hook_id' => 5); // long board
				$eval_hooks['other31'] = array('hook_id' => 6); // traction splint
				$eval_hooks['other39'] = array('hook_id' => 7); // joint immobilization
				$eval_hooks['other42'] = array('hook_id' => 12); // MD consult
				$eval_hooks['vital'] = array('hook_id' => 13); // vitals
				$eval_hooks['other40'] = array('hook_id' => 15); // bandaging
				$eval_hooks['other33'] = array('hook_id' => 16); // c-spine
				$eval_hooks['other38'] = array('hook_id' => 18); // long bone
				$eval_hooks['airway13'] = array('hook_id' => 20); // suction
				$eval_hooks['iv1'] = array('hook_id' => 26); // IV
				$eval_hooks['iv3'] = array('hook_id' => 27); // blood draw
				$eval_hooks['cardiac'] = array('hook_id' => 38); // general cardiac
				$eval_hooks['cardiac1'] = array('hook_id' => 106); // chest compressions
				break;
			case 'clinical':
				$eval_hooks['other41'] = array('hook_id' => 43); // long board
				$eval_hooks['other31'] = array('hook_id' => 44); // traction splint
				$eval_hooks['other39'] = array('hook_id' => 45); // joint immobilization
				$eval_hooks['cardiac'] = array('hook_id' => 46); // general cardiac
				$eval_hooks['other42'] = array('hook_id' => 48); // MD consult
				$eval_hooks['vital'] = array('hook_id' => 49); // vitals
				$eval_hooks['other40'] = array('hook_id' => 51); // bandaging
				$eval_hooks['other33'] = array('hook_id' => 52); // c-spine
				$eval_hooks['other38'] = array('hook_id' => 53); // long bone
				$eval_hooks['airway13'] = array('hook_id' => 54); // suction
				$eval_hooks['iv1'] = array('hook_id' => 56); // IV
				$eval_hooks['iv3'] = array('hook_id' => 57); // blood draw
				$eval_hooks['cardiac1'] = array('hook_id' => 99); // chest compressions
				break;
			case 'lab':
				$eval_hooks['other41'] = array('hook_id' => 66); // long board
				$eval_hooks['other31'] = array('hook_id' => 67); // traction splint
				$eval_hooks['other39'] = array('hook_id' => 68); // joint immobilization
				$eval_hooks['other42'] = array('hook_id' => 70); // MD consult
				$eval_hooks['vital'] = array('hook_id' => 71); // vitals
				$eval_hooks['other40'] = array('hook_id' => 73); // bandaging
				$eval_hooks['other33'] = array('hook_id' => 74); // c-spine
				$eval_hooks['other38'] = array('hook_id' => 75); // long bone
				$eval_hooks['airway13'] = array('hook_id' => 76); // suction
				$eval_hooks['cardiac'] = array('hook_id' => 78); // general cardiac
				$eval_hooks['iv1'] = array('hook_id' => 79); // IV
				$eval_hooks['iv3'] = array('hook_id' => 80); // blood draw
                $eval_hooks['airway10'] = array('hook_id' => 82); // orotracheal intubation
                $eval_hooks['airway5'] = array('hook_id' => 82); // nasotracheal intubation
				$eval_hooks['cardiac1'] = array('hook_id' => 100); // chest compressions
                $eval_hooks['lab1'] = array('hook_id' => 121); // medical assessment
                $eval_hooks['lab2'] = array('hook_id' => 122); // trauma assessment
				break;
		}

		// figure out the actual skill type
		switch ($context) {
			case 'med':
				$skill_type = $context . $skill->route->id;
				break;
			case 'vital':
				$skill_type = $context;
				break;
			case 'cardiac':
				$skill_type = $context;
				$procedure = $skill->procedure->id;
				if ($procedure == 1) {
					$skill_type .= $procedure;
				}
				break;
			default:
				$skill_type = $context . $skill->procedure->id;
				break;
		}

		// use the map to get the right hook_id for the skill type
		$hook_id = $eval_hooks[$skill_type]['hook_id'];

		// if there is a hook mapped to this skill type...
		if ($hook_id > 0) {
			$program_id = $skill->student->program->id;
			$default_evals = \Fisdap\EntityUtils::getRepository("EvalHookDefaultsLegacy")->findByHook($hook_id);
			$program_evals = \Fisdap\EntityUtils::getRepository("EvalProgramHooksLegacy")->findBy(array('hook' => $hook_id, 'program' => $program_id));

			// if there are eval associated with this hook (default OR program), show the link
			if (count($default_evals) > 0 || count($program_evals) > 0) {
				$shift_id = $skill->shift->id;

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
				
				$html = '<a target="_blank" href="' . $evalURL . '" onclick="window.open(\'' . $evalURL . '\', \'_blank\', \'width=1020,height=700\'); return false">' .
						'<img title="do an evaluation!" src="/images/icons/' . $evalIcon . '.png" alt="Eval"/>' .
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