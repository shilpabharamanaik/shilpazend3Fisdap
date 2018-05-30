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
 * Form for processing an activation code
 */

/**
 * @package    Account
 */
class Account_Form_TransferAccounts extends Fisdap_Form_Base
{
    /**
     * @var array the decorators for the form
     */
    protected static $_formDecorators = array(
        'FormErrors',
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/transferAccountsForm.phtml")),
        array('Form', array('class' => 'transfer-account-form')),
    );

    /**
     * @var array decorators for products
     */
    public static $gridDecorators = array(
        'ViewHelper',
        array('Label', array('class' => 'grid_2')),
        array(array('prompt' => 'HtmlTag'), array('tag' => 'div', 'class' => 'grid_12')),
    );

    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();

        $this->setDecorators(self::$_formDecorators);

        $this->addJsFile("/js/library/Account/Form/transferAccounts.js");
        $this->addCssFile("/css/library/Account/Form/transferAccounts.css");

        $user = new Zend_Form_Element_Text("userId");
        $user->setRequired(true)
            ->setLabel("Username or ID:");
        $this->addElement($user);

        $program = new Zend_Form_Element_Select("program");
        $programOptions = \Fisdap\Entity\ProgramLegacy::getFormOptions();
        $program->setMultiOptions($programOptions)
            ->setRequired(true)
            ->setLabel("Transfer to:");
        $this->addElement($program);


        $continue = new \Fisdap_Form_Element_SaveButton("save");
        $continue->setLabel("Transfer");
        $this->addElement($continue);

        $this->setElementDecorators(self::$elementDecorators);
        $this->setElementDecorators(self::$gridDecorators, array('userId', 'program'), true);
    }

    public function process($post)
    {
        $values = $post;

        $user = \Fisdap\EntityUtils::getEntity("User", $values['userId']);

        if (!$user) {
            // it probably wasn't a user id, try getting the user based on a username instead
            $user = \Fisdap\EntityUtils::getEntityManager()->getRepository('\Fisdap\Entity\User')->getUserByUsername($values['userId']);
        }

        $oldProgram = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $user->getProgramId());
        $newProgram = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $values['program']);

        if ($user && $oldProgram) {
            // Step 1 update the student/instructor entity
            $instructorStudent = $user->getCurrentRoleData();
            $instructorStudent->program = $newProgram;

            // Step 2 update the UserContext entity
            $instructorStudent->user_context->program = $newProgram;

            // Step 3 because legacy does it, we update the serial number for this user
            $sn = $instructorStudent->getUserContext()->getPrimarySerialNumber();

            // if this user is an instructor, there is a good chance they won't have a serial number
            if ($sn) {
                $sn->program = $newProgram;
            }

            // Step 4 make sure a note is added to the from program's notes
            $date = new DateTime();

            //step 5 make sure instructorStudent is removed from student groups

            $instructorStudent->remove_groups();

            $noteMessage = ucfirst($user->getCurrentRoleName()) . ": " . $user->first_name . " " . $user->last_name . " ("
                . $user->username . ") was transferred from " . $oldProgram->name . " to " . $newProgram->name . " on " . $date->format('F j, Y') . ".<br /><br />";
            $noteMessage .= "<!-- Please do not delete original author name -->" . \Fisdap\Entity\User::getLoggedInUser()->username . "<br />";
            \Fisdap\EntityUtils::getEntityManager()->getRepository('\Fisdap\Entity\ProgramLegacy')->addTransferNote($noteMessage, $oldProgram->id, $date);

            $instructorStudent->save();
            if ($sn) {
                $sn->save();
            }
            return true;
        }

        return false;
    }
}
