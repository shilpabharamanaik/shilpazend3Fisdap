<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2013.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * This helper will display a modal prompting the user to send an email about compliance
 */

/**
 * @package Portfolio
 */
class Portfolio_View_Helper_ComplianceEmailModal extends Zend_View_Helper_Abstract
{
    
    /**
     * @var string the html to be rendered
     */
    protected $_html;
    
    // will create an empty modal
    public function complianceEmailModal()
    {
        $this->_html =  "<div id='email-modal'>";
        $this->_html .= 	"<div id='email-modal-content'></div>";
        $this->_html .= "</div>";
        
        return $this->_html;
    }
    
    // generates the content for the modal
    public function generateComplianceEmail($mail, $userContextId)
    {
        $returnContent = "<div id='preview-msg'>";
        $returnContent .= "<div>You will be sending the following email:</div>";
        
        $returnContent .= "<div id='emailWrapper'>";
        $returnContent .= $mail->getTemplateBody("compliance-email.phtml");
        $returnContent .= "</div>";
        $returnContent .= "</div>";
        
        $returnContent .= "<div id='success-msg' class='success'>";
        $returnContent .= "Your email has been sent.";
        $returnContent .= "</div>";
        
        $returnContent .= "<div id='emailModalButtonWrapper'>";
        $returnContent .= "<div class='small action-button gray-button'>";
        $returnContent .= "<button id='emailCloseButton'>Cancel</button>";
        $returnContent .= "</div>";
        $returnContent .= "<div class='small action-button green-buttons'>";
        $returnContent .= "<button id='emailSendButton'  data-usercontextid='$userContextId'>Send email</button>";
        $returnContent .= "</div>";
        $returnContent .= "</div>";
        
        return $returnContent;
    }
}
