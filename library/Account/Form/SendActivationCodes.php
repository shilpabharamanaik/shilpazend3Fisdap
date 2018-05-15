<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * /


/**
 * Form for sending activation codes
 */

/**
 * NOTE: There's some minor weirdness here in that we're not displaying
 * the regular decorator for form errors, we're instead outputing only
 * custom messages in the viewscript: forms/sendActivationCodesForm.phtml
 *
 * @package    Account
 */
class Account_Form_SendActivationCodes extends Fisdap_Form_Base
{
    /**
     * @var array the decorators for the form
     */
    protected static $_formDecorators = array(
        //'FormErrors',
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/sendActivationCodesForm.phtml")),
        array('Form', array('class' => 'send-activation-codes-form')),
    );

    /**
     * @var array decorators for products
     */
    public static $productDecorators = array(
        'ViewHelper',
        array('Label', array('placement' => 'APPEND')),
        array(array('prompt' => 'HtmlTag'), array('tag' => 'div', 'class' => 'product-prompt')),
    );

    /**
     * @var \Fisdap\Entity\Order
     */
    public $order;

    /**
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($orderId = null, $options = null)
    {
        $this->order = \Fisdap\EntityUtils::getEntity('Order', $orderId);

        parent::__construct($options);
    }

    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();

        $this->addCssFile("/css/library/Account/Form/send-activation-codes.css");
        $this->addJsFile("/js/library/Account/Form/send-activation-codes.js");

        $this->setDecorators(self::$_formDecorators);

        foreach ($this->order->order_configurations as $config) {
            $subform = new \Account_Form_ActivationCodesSubForm($config->id);
            $this->addSubForm($subform, "orderConfiguration_" . $config->id);
        }

        $orderId = new Zend_Form_Element_Hidden("orderId");
        $orderId->setDecorators(array('ViewHelper'));
        $this->addElement($orderId);

        $sendButton = new Fisdap_Form_Element_SaveButton("sendButton");
        $sendButton->setLabel("Send Email")
            ->setDecorators(self::$buttonDecorators);
        $this->addElement($sendButton);

        $this->addErrorMessage("Please enter only valid email addresses.");

        if ($this->order->id) {
            $this->setDefaults(array(
                'orderId' => $this->order->id,
            ));
        }
    }

    /**
     * Process the submitted POST values and do whatever you need to do
     *
     * @param array $post the POSTed values from the user
     * @return mixed either the values or the form w/errors
     */
    public function process($post)
    {
        if ($this->isValid($post)) {
            $values = $this->getValues();

            $order = \Fisdap\EntityUtils::getEntity("Order", $values['orderId']);

            foreach ($this->getSubForms() as $subform) {
                $subValues = $subform->getValues(true);

                foreach ($subform->serials as $serial) {
                    if ($subValues["serial_" . $serial->id]) {
                        $serial->distribution_date = new \DateTime();
                        $serial->distribution_email = $subValues["serial_" . $serial->id];
                        $serial->save(false);

                        $mail = new \Fisdap_TemplateMailer();
                        $upgradeable = !($serial->hasTransitionCourse() || $serial->isInstructorAccount());
                        $mail->addTo($subValues["serial_" . $serial->id])
                            ->setSubject("Invitation to create a Fisdap account")
                            ->setViewParam('serial', $serial)
                            ->setViewParam('orderer', $order->user)
                            ->setViewParam('urlRoot', Util_HandyServerUtils::getCurrentServerRoot())
                            ->setViewParam('message', $subValues['message'])
                            ->setViewParam('upgradeable', $upgradeable)
                            ->sendHtmlTemplate('create-account-invitation.phtml');
                    }
                }
            }

            \Fisdap\EntityUtils::getEntityManager()->flush();
            return true;
        }
    }
}
