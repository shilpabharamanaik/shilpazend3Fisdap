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
 * This helper will display a button to link to the eval landing page if this hook has evals
 */

/**
 * @package Mobile
 */
class Mobile_View_Helper_MobileEvalButton extends Zend_View_Helper_Abstract
{
    /**
     * @var string the html to be rendered
     */
    protected $_html;
    
    /**
     * @param mixed integer or array of integers$runs array of arrays containing each run to be
     * rendered in a view for a mobile device
     *
     * @return string the run list rendered as an html table
     */
    public function mobileEvalButton($hook_ids, $shiftId)
    {
        if (is_null($hook_ids)) {
            $hook_ids = array();
        } elseif (!is_array($hook_ids)) {
            $hook_ids = array($hook_ids);
        }
        
        $user = \Fisdap\Entity\User::getLoggedInUser();
        $hasEvals = \Fisdap\EntityUtils::getRepository('EvalProgramHooksLegacy')->hasEvalsForHook($hook_ids, $user->getProgramId());
        
        if ($hasEvals) {
            $hook_str = implode(",", $hook_ids);
            return "<a href='/mobile/index/evals/hid/$hook_str/sid/$shiftId'>Evaluate</a>";
        }
    }
}
