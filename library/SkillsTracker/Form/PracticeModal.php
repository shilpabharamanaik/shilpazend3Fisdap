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
 * This produces a modal form for adding/editing Meds
 */

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class SkillsTracker_Form_PracticeModal extends SkillsTracker_Form_Modal
{

    /**
     * @var \Fisdap\Entity\PracticeItem
     */
    public $item;
    
    /**
     * @var \Fisdap\Entity\PracticeDefinition
     */
    public $definition;
    
    /**
     * @var \Fisdap\Entity\ShiftLegacy
     */
    public $shift;
    
    /**
     * @var boolean can the logged in user confirm evals
     */
    public $canConfirmEvals = false;
    
    /**
     * @var \Zend_Session_Namespace
     */
    public $session;
    
    /**
     * @param int $medId the id of the Med to edit
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($practiceItemId = null, $practiceDefinitionId = null, $shiftId = null, $options = null)
    {
        $this->item = \Fisdap\EntityUtils::getEntity('PracticeItem', $practiceItemId);
        if ($this->item) {
            $this->definition = $this->item->practice_definition;
            $this->shift = $this->item->shift;
        } else {
            $this->definition = \Fisdap\EntityUtils::getEntity('PracticeDefinition', $practiceDefinitionId);
            $this->shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);
        }

        $user = \Fisdap\Entity\User::getLoggedInUser();
        if ($user) {
            $this->canConfirmEvals = $user->isInstructor() && $user->hasPermission("Edit Evals");
        }
        
        $this->session = new \Zend_Session_Namespace("PracticeModal");
        
        parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();
        
        $this->addJsFile("/js/library/SkillsTracker/Form/practice-modal.js");
        //$this->addJsFile("/js/jquery.chosen.js");
        $this->addCssFile("/css/library/SkillsTracker/Form/practice-modal.css");
        
        //Time
        $time = new Zend_Form_Element_Text("time");
        $time->setLabel("Time")
             ->setRequired()
             ->setAttrib('maxlength', '4')
             ->addValidator('regex', false, array('/^\d{4,5}/'))
             ->addValidator("Between", true, array("min" => 0, "max" => 2399))
             ->addFilter("Digits")
             ->addErrorMessage("Please enter time in 24 hour format (Between 0000 and 2399)");
        $this->addElement($time);
        
        //Pass or Fail
        $passed = new Fisdap_Form_Element_jQueryUIButtonset('passed');
        $passed->setOptions(array(1 => "Passed", 0 => "Failed"));
        $this->addElement($passed);
        
        //Evaluator type
        $evaluatorType = new Fisdap_Form_Element_jQueryUIButtonset('evaluatorType');
        $evaluatorType->setLabel("Evaluator type")
               ->setOptions(\Fisdap\Entity\EvaluatorType::getFormOptions());
        $this->addElement($evaluatorType);
        
        //Evaluator ID
        $evaluator = new Zend_Form_Element_Select('evaluatorId');
        $evaluator->setLabel("Evaluator")
                  ->addValidator('NotEmpty', true, array('type' => 'zero'))
                  ->setRegisterInArrayValidator(false)
                  ->addErrorMessage("Please choose an evaluator");
        $this->addElement($evaluator);
        
        //Set multioptions for this element if a practice item exists, if it doesn't, we'll set the defaults for this item based on the session below
        if ($this->item->id) {
            $this->setDefaultEvaluator($this->item->evaluator_type->id, $this->item->evaluator_id);
        }
        
        //Fields for Confirming evals
        //Username
        $username = new Zend_Form_Element_Text('usernameConfirm');
        $username->setLabel("Username");
        $username->autocomplete = 'off';
        $this->addElement($username);
        
        //Password
        $password = new Zend_Form_Element_Password('passwordConfirm');
        $password->setLabel("Password");
        $password->autocomplete = 'off';
        $this->addElement($password);

        //Stupid Firefox -- Firefox auto completes our user / pass inputs even if they aren't shown, leading to displaying error messages when the user never saw the inputs to begin with
        //This value tracks whether the student chose to display those user / pass inputs at all, defaults to false to match form behavior, corresponding JS toggles it when the fields are shown / hidden
        $confirmToggle = new Zend_Form_Element_Hidden('confirmToggle');
        $confirmToggle->setValue(0);
        $this->addElement($confirmToggle);

        //Shift ID
        $shiftId = new Zend_Form_Element_Hidden('shiftId');
        $this->addElement($shiftId);
        
        //Practice Definition ID
        $practiceDefinitionId = new Zend_Form_Element_Hidden('practiceDefinitionId');
        $this->addElement($practiceDefinitionId);
        
        //Practice Item ID
        $practiceItemId = new Zend_Form_Element_Hidden('practiceItemId');
        $this->addElement($practiceItemId);
        
        //Confirmed
        $confirmed = new Zend_Form_Element_Hidden("confirmed");
        $this->addElement($confirmed);
        
        //Confirmed
        $canConfirmEvals = new Zend_Form_Element_Hidden("canConfirmEvalsElement");
        $canConfirmEvals->setValue($this->canConfirmEvals);
        $this->addElement($canConfirmEvals);
        
        //Set default values for the form
        if ($this->item->id) {
            $this->setDefaults(array(
                "time" => $this->item->time->format("Hi"),
                "passed" => (int)$this->item->passed,
                "evaluatorType" => $this->item->evaluator_type->id,
                "evaluatorId" => $this->item->evaluator_id,
                "practiceDefinitionId" => $this->item->practice_definition->id,
                "shiftId" => $this->item->shift->id,
                "practiceItemId" => $this->item->id,
                "confirmed" => $this->item->confirmed,
            ));
        } else {
            $this->setDefaults(array(
                "time" => date("Hi"),
                "passed" => 1,
                "shiftId" => $this->shift->id,
                "practiceDefinitionId" => $this->definition->id,
                "confirmed" => 0,
            ));
            
            //Set new defaults from the session
            if ($this->session->evaluatorType && $this->session->evaluatorId) {
                $this->setDefaultEvaluator($this->session->evaluatorType, $this->session->evaluatorId);
            } else {
                $this->setDefaultEvaluator(1, null);
            }
        }
        
        
        
        $this->setElementDecorators(self::$gridElementDecorators);
        $this->setElementDecorators(self::$hiddenElementDecorators, array('practiceDefinitionId', 'shiftId', 'practiceItemId', 'confirmed', 'canConfirmEvalsElement'), true);
        
        $formName = "practiceDialog";
        
        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "practiceModal.phtml", 'viewModule' => 'skills-tracker')),
            'Form',
            array('DialogContainer', array(
                'id'          => $formName,
                'class'          => $formName,
                'jQueryParams' => array(
                    'tabPosition' => 'top',
                    'modal' => true,
                    'autoOpen' => false,
                    'resizable' => false,
                    'draggable' => false,
                    'width' => 500,
                    'title' => $this->definition->name,
                ),
            )),
        ));
    }
    
    /**
     * Deals with setting the default options for evaluator type and then
     * sets the default values for both evaluator type and evaluator ID.
     *
     * @param integer $type the type of the default evaluator
     * @param integer $id the ID of the default evaluator
     * @return void
     */
    private function setDefaultEvaluator($type, $id)
    {
        $users = array();
        if ($type == 1) {
            $users = \Fisdap\EntityUtils::getRepository("User")->getAllInstructorsByProgram($this->shift->student->program->id);
        } elseif ($type == 2) {
            $users = \Fisdap\EntityUtils::getRepository("User")->getAllStudentsByProgram($this->shift->student->program->id, array("graduationStatus" => array(1)));
        }
        
        $evaluator = $this->getElement("evaluatorId");
        $evaluator->addMultiOption(0, "Evaluator");
        foreach ($users as $user) {
            $evaluator->addMultiOption($user['id'], $user['last_name'] . ", " . $user['first_name']);
        }
        
        $this->setDefault("evaluatorType", $type);
        $this->setDefault("evaluatorId", $id);
        
        return;
    }
    
    public function isValid($post)
    {
        $isValid = parent::isValid($post);
        
        if (($post['usernameConfirm'] || $post['passwordConfirm']) && $post['confirmToggle'] == 1) {
            if (!\Fisdap\Entity\User::authenticate_password($post['usernameConfirm'], $post['passwordConfirm'])) {
                $this->getElement("usernameConfirm")->addError("Username/password does not match.");
                $isValid = false;
            }

            if (!$this->isValidConfirmationUser($user = \Fisdap\Entity\User::getByUsername($post['usernameConfirm']), $post['shiftId'])) {
                if (!$user->isInstructor()) {
                    $errorMessage = "Only instructors can confirm practice items.";
                } elseif (!$user->hasPermission("Edit Evals")) {
                    $errorMessage = "Only instructors with permission to edit evals can confirm practice items.";
                } else {
                    $errorMessage = "You can only confirm practice items for students in your program";
                }

                $this->getElement("usernameConfirm")->addError($errorMessage);
                $isValid = false;
            }
        }

        if ($this->item->confirmed) {
            $this->getElement("usernameConfirm")->addError("You cannot save a practice item that is already confirmed.");
            $isValid = false;
        }

        return $isValid;
    }

    private function isValidConfirmationUser($user, $shiftId)
    {
        $shift = \Fisdap\EntityUtils::getEntity("ShiftLegacy", $shiftId);

        if (!$user->isInstructor()) {
            return false;
        }

        if ($shift->student->program->id != $user->getCurrentProgram()->id) {
            return false;
        }

        if (!$user->getCurrentRoleData()->hasPermission("Edit Evals")) {
            return false;
        }

        return true;
    }

    /**
     * Validate the form, if valid, save the Practice Item, if not, return the error msgs
     *
     * @param array $data the POSTed data
     * @return mixed either boolean true, or an array of error messages
     */
    public function process($data)
    {
        if ($this->isValid($data)) {
            $values = $this->getValues($data);

            if ($values['practiceItemId']) {
                $item = \Fisdap\EntityUtils::getEntity('PracticeItem', $values['practiceItemId']);
            } else {
                $item = \Fisdap\EntityUtils::getEntity('PracticeItem');
            }

            $item->practice_definition = $values['practiceDefinitionId'];
            $item->evaluator_type = $values['evaluatorType'];
            $item->evaluator_id = $values['evaluatorId'];
            $item->time = $values['time'];

            //Save values to the session
            $this->session->evaluatorType = $values['evaluatorType'];
            $this->session->evaluatorId = $values['evaluatorId'];

            $shift = \Fisdap\EntityUtils::getEntity("ShiftLegacy", $values['shiftId']);
            $shift->addPracticeItem($item);

            // Only try to confirm a practice item if the evaluator was an instructor
            if ($item->evaluator_type->id == 1) {
                if ($this->isValidConfirmationUser(\Fisdap\Entity\User::getLoggedInUser(), $values['shiftId'])) {
                    $item->confirmed = true;
                    $item->confirmAttachSkills(true, $values['passed']);
                } elseif ($values['usernameConfirm'] && $values['passwordConfirm'] && $values['confirmToggle'] == 1) {
                    $item->confirmed = true;
                    $item->confirmAttachSkills(true, $values['passed']);
                }
            } else {
                // since it isn't an instructor, we can go ahead and give them skills/airway success
                // tell the function if it should attach skills (has passed changed?)
                $changed = $item->passed != $values['passed'];
                $item->confirmAttachSkills($changed, $values['passed']);
            }
            
            if (!$item->patient_type) {
                $item->set_patient_type(5);
            }
            
            //We have to set pass/fail after the item has been attached to a shift
            $item->passed = $values['passed'];
            
            $shift->save();

            return true;
        }
        
        return $this->getMessages();
    }
}
