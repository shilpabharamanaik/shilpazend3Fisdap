<?php

use Fisdap\Entity\CertificationLevel;
use Fisdap\Entity\ProgramLegacy;
use Fisdap\Entity\SerialNumberLegacy;
use Fisdap\Entity\StudentLegacy;
use Fisdap\Entity\User;
use Fisdap\Entity\UserContext;
use Fisdap\EntityUtils;

/**
 * @package    Account
 */
class Account_Form_MergeAccounts extends Fisdap_Form_Base
{
    /**
     * @var array the decorators for the form
     */
    protected static $_formDecorators = array(
        'FormErrors',
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/mergeAccountsForm.phtml")),
        array('Form', array('class' => 'merge-account-form')),
    );

    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();

        $this->setDecorators(self::$_formDecorators);

        $this->addJsFile("/js/library/Account/Form/mergeAccounts.js");
        $this->addCssFile("/css/library/Account/Form/mergeAccounts.css");

        $studentA = new Zend_Form_Element_Text("studentIdA");
        $studentA->setRequired(true)
            ->setLabel("Student ID A:");
        $this->addElement($studentA);

        $studentB = new Zend_Form_Element_Text("studentIdB");
        $studentB->setRequired(true)
            ->setLabel("Student ID B:");
        $this->addElement($studentB);

        $mergeButton = new \Fisdap_Form_Element_SaveButton("save");
        $mergeButton->setLabel("Merge");
        $this->addElement($mergeButton);

        $this->setElementDecorators(self::$elementDecorators);
    }

    public function process($post)
    {
        $logger = Zend_Registry::get('logger');

        $retVal = array(
            'code' => 200
        );

        // Step 1: Validate input
        // Edit: This isn't really needed because I'm doing it all in jQuery.
        $studentId = $post['usernameGroup'];
        if ($studentId == null) {
            $retVal['code'] = 500;
        }

        $programId = $post['programGroup'];
        if ($programId == null) {
            $retVal['code'] = 500;
        }

        $name = $post['userGroup'];
        if ($name == null) {
            $retVal['code'] = 500;
        }

        $email = $post['emailGroup'];
        if ($email == null) {
            $retVal['code'] = 500;
        }

        $cert = $post['certGroup'];
        if ($cert == null) {
            $retVal['code'] = 500;
        }

        $grad = $post['gradGroup'];
        if ($grad == null) {
            $retVal['code'] = 500;
        }

        // We still good? Move on...
        if ($retVal['code'] == 200) {
            // Step 2: Update context elements (program and grad date).
            /** @var StudentLegacy $student */
            $student = EntityUtils::getEntity("StudentLegacy", $studentId);
            /** @var ProgramLegacy $program */
            $program = EntityUtils::getEntity("ProgramLegacy", $programId);
            /** @var UserContext $context */
            $context = $student->getUserContext();

            $context->setProgram($program);
            $context->setEndDate(date_create_from_format('m/Y', $grad));

            // Step 3: Update cert level
            $em = \Fisdap\EntityUtils::getEntityManager();
            /** @var CertificationLevel $certLvls */
            $certLvls = $em->getRepository('Fisdap\Entity\CertificationLevel')->findBy(['description' => $cert]);
            if ($certLvls[0]) {
                $context->setCertificationLevel($certLvls[0]);
            }

            // Step 4: Merge the serial numbers and apply to the selected context.
            /** @var SerialNumberLegacy $snA */
            $snA = $em->getRepository('Fisdap\Entity\SerialNumberLegacy')->findOneByNumber($post['snA']);
            /** @var SerialNumberLegacy $snB */
            $snB = $em->getRepository('Fisdap\Entity\SerialNumberLegacy')->findOneByNumber($post['snB']);

            $newConfig = $snA->getConfiguration() | $snB->getConfiguration();
            $context->getPrimarySerialNumber()->setConfiguration($newConfig);

            // Step 5: Update the user record (email, name).
            /** @var User $user */
            $user = $context->getUser();
            $user->setEmail($email);

            $nameParts = explode(" ", $name);
            $user->setFirstName($nameParts[0]);
            $user->setLastName($nameParts[1]);

            // Step 6: Save it all.
            $context->getPrimarySerialNumber()->save();
            $context->save();
            $user->save();

            // Everything saved? Sweet, time to nuke the other stuff.

            // Step 7: Figure out which student record is no longer needed.
            /** @var StudentLegacy $delStudent */
            $delStudent = null;
            if ($studentId == $post['studentIdA']) {
                $delStudent = EntityUtils::getEntity("StudentLegacy", $post['studentIdB']);
            } elseif ($studentId == $post['studentIdB']) {
                $delStudent = EntityUtils::getEntity("StudentLegacy", $post['studentIdA']);
            }

            // Step 8: Grab the context that needs to be deleted.
            /** @var UserContext $delContext */
            $delContext = $delStudent->getUserContext();

            // Step 9: Figure out which serial needs to be deleted.
            /** @var SerialNumberLegacy $delSerial */
            $delSerial = null;
            if ($delContext->getPrimarySerialNumber()->getNumber() == $snA->getNumber()) {
                $delSerial = $snA;
            } elseif ($delContext->getPrimarySerialNumber()->getNumber() == $snB->getNumber()) {
                $delSerial = $snB;
            }

            // Step 10: Check to see if we're merging within the same account.
            // IF NOT, then we delete the other account.
            /** @var User $userA */
            $userA = EntityUtils::getEntity("StudentLegacy", $post['studentIdA'])->getUserContext()->getUser();
            /** @var User $userB */
            $userB = EntityUtils::getEntity("StudentLegacy", $post['studentIdA'])->getUserContext()->getUser();
            /** @var User $delUser */
            $delUser = null;
            if ($studentId == $post['studentIdA']) {
                $delUser = $userB;
            } elseif ($studentId == $post['studentIdB']) {
                $delUser = $userA;
            }

            // Step 11: Delete the things.
            $delSerial->delete();
            $delStudent->delete();
            $delContext->delete();
            $delUser->delete();

            return $retVal;
        } else {
            return $retVal;
        }
    }
}
