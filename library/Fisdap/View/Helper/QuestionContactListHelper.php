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
 * helper that provides mailto list to the user so that questions may be asked
 */

/**
 * @package Fisdap
 */
class Zend_View_Helper_QuestionContactListHelper extends Zend_View_Helper_Abstract
{
		/**
		* @return array of mailto array 
		*/
    public function questionContactListHelper()
    {

			$questionMailTos = array();
			$questionMailTos[] = array("who"=>"Mike Bowen",  "emailAddress"=>"mbowen@fisdap.net");
			//$questionMailTos[] = array("who"=>" or ", "");
		//	$questionMailTos[] = array("who"=>"Gabe Romero", "emailAddress"=>"gabe@fisdap.net");
			$questionMailTos = $questionMailTos;

			$result = "";
			foreach($questionMailTos as $questionMailTosArray) 
			{
				
				if($questionMailTosArray["emailAddress"] != "") {
					$result .= "<a href='mailto:".$questionMailTosArray["emailAddress"]."'>".$questionMailTosArray["who"]."</a>";
				} else {
					$result .= $questionMailTosArray["who"];					
				}

			}

			return $result;
			
    }
}