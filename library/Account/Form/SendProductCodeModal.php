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
 * This produces a modal form for sending a product code
 */

use Fisdap\Service\ProductService;

/**
 * @package    Account
 * @subpackage Forms
 */
class Account_Form_SendProductCodeModal extends SkillsTracker_Form_Modal
{
    public function init()
    {
        parent::init();

        $this->addCssFile("/css/library/Account/Form/send-product-code-modal.css");
        $this->addJsFile("/js/library/Account/Form/send-product-code-modal.js");

        $emails = new Zend_Form_Element_Textarea("emails");
        $emails->setLabel("Comma separated list of students:")
            ->setAttrib("class", "emails")
            ->setAttrib("rows", 10);
        $this->addElement($emails);

        $message = new Zend_Form_Element_Textarea("message");
        $message->setLabel("Message for students: (optional)")
            ->setAttrib("class", "message")
            ->setAttrib("rows", 10);
        $this->addElement($message);

        $copySelf = new Zend_Form_Element_Checkbox("ccSelf");
        $copySelf->setLabel("Send a copy to myself");
        $this->addElement($copySelf);

        $productCode = new Zend_Form_Element_Hidden("productCode");
        $this->addElement($productCode);


        $this->setElementDecorators(self::$elementDecorators);
        $this->setElementDecorators(self::$hiddenElementDecorators, array('productCode'), true);
        $this->setElementDecorators(self::$checkboxDecorators, array('ccSelf'), true);

        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "forms/sendProductCodeModal.phtml")),
            'Form',
            array('DialogContainer', array(
                'id' => 'sendEmailsDialog',
                'class' => 'sendEmailsDialog',
                'jQueryParams' => array(
                    'tabPosition' => 'top',
                    'modal' => true,
                    'autoOpen' => false,
                    'resizable' => false,
                    'width' => 800,
                    'title' => 'Email Product Codes',
                    'open' => new Zend_Json_Expr("function(event, ui) { $('button').css('color', '#000000'); $('#message').val(''); $('#emails').val(''); $('#ccSelf').attr('checked', ''); $('#preview-email-container').hide(); }"),
                    'buttons' => array(array("text" => "Cancel", "className" => "gray-button", "click" => new Zend_Json_Expr("function() { $(this).dialog('close'); }")), array("text" => "Send Emails", "id" => "save-btn", "class" => "gray-button small", "click" => new Zend_Json_Expr(
                        "function() {
							$.post('/account/orders/send-product-code', $('#sendEmailsDialog form').serialize(), function(response) {
								if (response === true) {
									successMsg = $('<div class=\"success grid_12\">Product code has been successfully emailed.</div>');
									$('h1:first-of-type').after(successMsg);
									successMsg.delay(5000).slideUp();
								}
							}, 'json');
                            
                            $(this).dialog('close');
						}"))),
                ),
            )),
        ));
    }

    /**
     * Send out emails with product codes
     *
     * @param array $data the POSTed data
     * @return boolean
     */
    public function process($data)
    {
        if ($this->isValid($data)) {
            $values = $this->getValues();

            $productCode = \Fisdap\Entity\ProductCode::getByProductCode($values['productCode']);

            $emails = preg_split("/[;,]/", $values['emails']);
            $emailValidator = new \Zend_Validate_EmailAddress();

            foreach ($emails as $email) {
                //Get rid of possible whitespace
                $email = trim($email);

                //Only send email if we have a valid address
                if ($emailValidator->isValid($email)) {
                    $mail = new \Fisdap_TemplateMailer();

                    if ($values['ccSelf']) {
                        $mail->addTo($productCode->order_configuration->order->user->email);
                    }

                    $configuration = $productCode->order_configuration->configuration;
                    $productService = new ProductService();
                    $upgradeable = !($configuration == $productService::PRECEPTOR_TRAINING_CONFIG ||
                        $configuration == $productService::PARAMEDIC_TRANSITION_CONFIG ||
                        $configuration == $productService::EMTB_TRANSITION_CONFIG ||
                        $configuration == $productService::AEMT_TRANSITION_CONFIG );
                    $mail->addTo($email)
                        ->setSubject("Invitation to purchase a Fisdap account")
                        ->setViewParam('productCode', $productCode)
                        ->setViewParam('orderer', $productCode->order_configuration->order->user)
                        ->setViewParam('urlRoot', Util_HandyServerUtils::getCurrentServerRoot())
                        ->setViewParam('message', $values['message'])
                        ->setViewParam('upgradeable', $upgradeable)
                        ->sendHtmlTemplate('purchase-account-invitation.phtml');
                }
            }

            return true;
        }

        return $this->getMessages();
    }
}