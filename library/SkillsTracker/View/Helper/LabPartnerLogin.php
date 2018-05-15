<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted witdout prior autdorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * This view helper will render the lab partner login form
 */

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_LabPartnerLogin extends Zend_View_Helper_Abstract
{
    /**
     * @var string tde html to be rendered
     */
    protected $_html;
    
    public function labPartnerLogin()
    {
        $this->_html .= "<div class='grid_6'>";
        $this->_html .= "<h3 class='section-header'>Add Lab Partner</h3>";
        $this->_html .= "<h3 class='subheader'>Login</h3>";
        $this->_html .= "<div class='form-prompt'>"
                    . "<div class='grid_4'>Username:</div>"
                    . "<div class='grid_8'>" . $this->view->formText("username") . "</div>"
                    . "</div>";
                    
        $this->_html .= "<div class='clear'></div>";
                    
        $this->_html .= "<div class='form-prompt'>"
                    . "<div class='grid_4'>Password:</div>"
                    . "<div class='grid_8'>" . $this->view->formPassword("password") . "</div>"
                    . "</div>";

        $this->_html .= "<div class='clear'></div>";

                    
        $this->_html .= "<div class='prefix_4 grid_8'>"
                    . "<div class='extra-small blue-button ok-wrapper'><a href='#' id='ok-link'>Ok</a></div>"
                    . "<div class='extra-small gray-button cancel-wrapper'><a href='#' id='cancel-link'>Cancel</a></div>"
                    . "</div>"
                    . "<div class='clear'></div>";
                    
        $this->_html .= "</div>"
                     . "<div class='clear'></div>";

        return $this->_html;
    }
}
