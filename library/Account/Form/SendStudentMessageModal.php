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
 * This produces a modal form for sending a message to a given group of students
 */

/**
 * @package    Account
 * @subpackage Forms
 */
class Account_Form_SendStudentMessageModal extends SkillsTracker_Form_Modal
{
	public function init()
	{
        parent::init();
		
		$this->addCssFile("/css/library/Account/Form/send-student-message-modal.css");
		$this->addJsFile("/js/library/Account/Form/send-student-message-modal.js");
		
		$emails = new Zend_Form_Element_Hidden("emails");
		$this->addElement($emails);
		
		$user_ids = new Zend_Form_Element_Hidden("user_ids");
		$this->addElement($user_ids);
		
		$type = new Zend_Form_Element_Hidden("msg_type");
		$this->addElement($type);
		
		$subject = new Zend_Form_Element_Text("subject");
		$this->addElement($subject);
		
		$message = new Zend_Form_Element_Textarea("message");
		$this->addElement($message);
		
		$copySelf = new Zend_Form_Element_Checkbox("ccSelf");
		$this->addElement($copySelf);
		
		$this->setDecorators(array(
			'PrepareElements',
			array('ViewScript', array('viewScript' => "forms/send-student-message-modal.phtml")),
			'Form'
		));
	}

	public function process($data)
	{
		$current_user = \Fisdap\Entity\User::getLoggedInUser();
		$current_user_email = \Fisdap\Entity\User::getLoggedInUser()->email;
		
		$values = $data;
		
		if($values['msg_type'] == "email" || $values['msg_type'] == "both"){
			
			$emails = preg_split("/[;,]/", $values['emails']);
			$emailValidator = new \Zend_Validate_EmailAddress();
			
			foreach ($emails as $email) {
				// Get rid of possible whitespace
				$email = trim($email);
				
				// Only send email if we have a valid address
				if ($emailValidator->isValid($email)) {
					$mail = new \Fisdap_TemplateMailer();
					$mail->addTo($email)
						 ->setSubject($values['subject'])
						 ->setViewParam('message', $values['message'])
						 ->setViewParam('sender_name', $current_user->first_name . " " . $current_user->last_name)
						 ->sendHtmlTemplate('send-student-message.phtml');
				}
			}
			
			if ($values['cc_self']) {
				$mail = new \Fisdap_TemplateMailer();
				$mail->addTo($current_user_email)
					 ->setSubject($values['subject'])
					 ->setViewParam('message', $values['message'])
					 ->setViewParam('sender_name', $current_user->first_name . " " . $current_user->last_name)
					 ->sendHtmlTemplate('send-student-message.phtml');
			}
		}
		
		
		if($values['msg_type'] == "inbox" || $values['msg_type'] == "both"){
			
			// send a MyFisdap message instead
			$user_ids = preg_split("/[;,]/", $values['user_ids']);
			
			if ($values['cc_self']) {
				$user_ids[] = $current_user->id;
			}
			
			$body = $values['message'];
			
			// Create a myFisdap message
			$message = new \Fisdap\Entity\Message();
			$message->set_title($values['subject']);
			$message->set_author_type(3);
			$message->set_author($current_user);
			$message->set_body($body);

			$successfulRecipients = $message->deliver($user_ids);
			
		}
		
		return true;
	}
}