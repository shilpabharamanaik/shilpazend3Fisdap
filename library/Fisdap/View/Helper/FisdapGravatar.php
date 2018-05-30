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
 * View helper to work with gravatar helper
 */

/**
 * @package Fisdap
 *
 * @return Profile picture
 */
class Zend_View_Helper_FisdapGravatar extends Zend_View_Helper_Abstract
{
    protected $_html;
    
    public function fisdapGravatar($email)
    {
        $user = \Fisdap\Entity\User::getLoggedInUser();
        
        $this->_html  = "<div class='gravatar-container'>";
        $this->_html .= $this->view->gravatar($email, array('imgSize' => 195, 'secure' => true));
        
        if (!$user || $email == $user->email) {
            if ($this->hasGravatar($email)) {
                $this->_html .= "<br /><a target='_blank' href='https://en.gravatar.com/" . md5($email) . "'>Edit image</a>";
            } else {
                $this->_html .= "<br /><a target='_blank' href='https://en.gravatar.com/connect/?source=_signup&email=$email'>Add a photo!</a>";
            }
        }
        $this->_html .= "</div>";
        return $this->_html;
    }
    
    public function hasGravatar($email)
    {
        // Craft a potential url and test its headers
        $hash = md5(strtolower(trim($email)));
        $uri = 'http://www.gravatar.com/avatar/' . $hash . '?d=404';
        $headers = @get_headers($uri);
        
        if (!preg_match("|200|", $headers[0])) {
            $has_valid_avatar = false;
        } else {
            $has_valid_avatar = true;
        }
        
        return $has_valid_avatar;
    }
}
