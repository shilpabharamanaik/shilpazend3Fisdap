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
 * This is a pretty simple helper that just takes in a value, tests to see if it
 * is false/null/empty, then either prints the incoming value (if not null) or
 * the specified text.
 */

/**
 * @package SkillsTracker
 */
class Fisdap_View_Helper_IfNullOutput extends Zend_View_Helper_Abstract
{
    /**
     * @var string the html to be rendered
     */
    protected $_html;
    
    /**
     * @param String $toTest Incoming piece to test for null/emptyness.
     *
     * @param String $falseOutput Output to be returned if $toTest is empty.
     *
     * @return string Either $falseOutput if $toTest is empty, or $toTest if
     * it is not.
     */
    public function ifNullOutput($toTest, $falseOutput)
    {
        if (strlen(trim($toTest)) > 0) {
            return $toTest;
        } else {
            return $falseOutput;
        }
    }
}
