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
 * Form for searching for a Fisdap account
 */

/**
 * @package	   Fisdap
 * @subpackage Admin
 */
class Fisdap_Form_CommentContacts extends Zend_Form_SubForm
{
	
	public $instructorElements;
	public $otherElements;
	
	public function init()
	{
		//jquery setup
        if (!$this->_view) {
            $this->_view = $this->getView();
        }
		
		//$this->_view->addScriptPath('/application/views/scripts');
		$this->_view->addScriptPath(APPLICATION_PATH . '/views/scripts');
		
        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "commentContacts.phtml")),
		    array('HtmlTag', array('tag'=>'div', 'class'=>'form-prompt')),
		));
		
		// Comment textarea
		$comment = new Fisdap_Form_Element_TextareaHipaa('comment');
		$comment->setOptions(array(
			'rows' => 5,
			'cols' => 76,
			'label' => 'Add a comment',
		));

		$comment->setDecorators(array(
			array('Label', array('placement' => 'prepend', 'class' => 'comment-label')),
			array(array('open' => 'HtmlTag'), array('tag' => 'br', 'openOnly' => true, 'placement' => 'append')),
			'ViewHelper',
			array(array('div' => 'HtmlTag'), array('class' => 'comment-textarea')),	// new-comment-element
		));
		$comment->setOptions(array(
			'id' => 'comment-textarea',
		));
		$this->addElement($comment);
		 
		// Instuctor visible only checkbox
		$instructorOnly = new Zend_Form_Element_Checkbox('instructor_only');
		$instructorOnly->setOptions(array(
			'id' => 'comment-instructor-only',
			'value' => 0,
			'label' => 'Only instructors can see this comment',
			'decorators' => array(
				'ViewHelper',
				array('Label', array('placement'=>'append')), //, 'class' => 'instructor-only'
				'HtmlTag'
				),
			)
		);

		$this->addElement($instructorOnly);
		
		
		// cancel and save buttons
		$cancelButton = new Zend_Form_Element_Button('cancel');
		$cancelButton->setOptions(array(
			'label' => 'Cancel',
			'id' => 'comment-cancel-button',
			'class' => 'gray-button',
			'decorators' => array(
				'Viewhelper',
				//array('HtmlTag', array('tag'=>'div', 'class'=>'comment-cancel-button')),
			)
		));
		
		$saveButton = new Zend_Form_Element_Button('save');
		$saveButton->setOptions(array(
			'label' => 'Save',
			'id' => 'comment-save-button',
			'class' => 'gray-button',
			'decorators' => array(
				'Viewhelper',
				//array('HtmlTag', array('tag'=>'div', 'class'=>'comment-save-button')),				
			)
		));
		
		$this->addElements(array($saveButton, $cancelButton));
		
	}
	
	/**
	* Create and add all the elements to the form
	*/
	public function customInit($shiftId=null, $studentId=null)
	{
		// First, figure out who the logged in user is.  That will drive who
		// will appear in the contact list.
		$user = \Fisdap\Entity\User::getLoggedInUser();

		if(is_null($user)) {
			return;
		}

		// Get a list of educators from the users program...
		$instructors = \Fisdap\EntityUtils::getRepository('ProgramLegacy')->getInstructors($user->getProgramId());

		$cleanInstructors = array();
		
		foreach($instructors as $r){
			$cleanInstructors[] = $r->user;
		}
		
		$this->instructorElements = array();
		$this->otherElements = array();
		
		foreach($cleanInstructors as $instructor){
			if(trim($instructor->email) != ''){
				$instructorElement = new Zend_Form_Element_Checkbox('instructor_' . $instructor->id);
				$instructorElement->setValue($instructor->id);
				$instructorElement->setLabel($instructor->first_name . " " . $instructor->last_name);
				$instructorElement->setDecorators(array("ViewHelper"));

				$this->addElement($instructorElement);

				$this->instructorElements[] = $instructorElement;
			}
		}
		
		
		// If the logged in person is a student, set them up in the list...
		if($shiftId != null){
			$shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);
			
			if($user->getCurrentRoleName() != 'student'){
				$studentElement = new Zend_Form_Element_Checkbox('student_' . $shift->student->user->id);
				$studentElement->setValue($shift->student->user->id);
				$studentElement->setLabel($shift->student->user->first_name . " " . $shift->student->user->last_name . " (student)");
				$studentElement->setDecorators(array("ViewHelper"));

				$this->otherElements[] = $studentElement;
			}
			
			foreach($shift->runs as $run){
				foreach($run->patients as $patient){
					if(trim($patient->preceptor->email) != ''){
						$preceptorElement = new Zend_Form_Element_Checkbox('preceptor_' . $patient->preceptor->id);
						$preceptorElement->setValue($patient->preceptor->id);
						$preceptorElement->setLabel($patient->preceptor->first_name . " " . $patient->preceptor->last_name . " (preceptor)");
						$preceptorElement->setDecorators(array("ViewHelper"));

						$this->addElement($preceptorElement);
						
						// ghetto uniquify...
						$this->otherElements[$patient->preceptor->id] = $preceptorElement;
					}
				}
			}
		}
	}
	
	/**
	 * Function to process form input
	 *
	 * @param array the POSTed information from the form
	 * @return array containing the email addresses of the people to contact.
	 */
	public function process($post)
	{
		$contactArray = array();
		
		foreach($post as $key => $value){
			// Ignore the 0's, they aren't getting emails...
			if($value == 1){
				$splitKey = explode('_', $key);
				
				switch($splitKey[0]){
					case "instructor":
					case "student":
						$user = \Fisdap\EntityUtils::getEntity('User', $splitKet[1]);
						$contactArray[] = $user->email;
						break;
					case "preceptor":
						$preceptor = \Fisdap\EntityUtils::getEntity('PreceptorLegacy', $splitKey[1]);
						$contactArray[] = $preceptor->email;
						break;
				}
			}
		}
		
		return $contactArray;
	}
}