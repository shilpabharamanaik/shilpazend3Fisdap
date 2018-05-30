<?php

use Fisdap\Api\Users\CurrentUser\CurrentUser;
use Fisdap\Data\Student\StudentLegacyRepository;
use Fisdap\Entity\StudentLegacy;

/**
 * @package    Account
 */
class Account_Form_ResearchConsentForm extends Fisdap_Form_Base
{
    /**
     * @var array the decorators for the form
     */
    protected static $_formDecorators = array(
        'FormErrors',
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/researchConsentForm.phtml")),
        array('Form'),
    );

    /**
     * @var StudentLegacy
     */
    public $student;

    /**
     * @var string
     */
    public $studentName;

    /**
     * @var string
     */
    public $todaysDate;

    /**
     * @var CurrentUser
     */
    private $currentUser;

    /**
     * @var StudentLegacyRepository
     */
    private $studentRepository;


    /**
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($options = null)
    {
        $date = new \DateTime();
        $this->todaysDate = $date->format("F j, Y");
        if (!$this->isInstructor) {
            $this->currentUser = \Zend_Registry::get('container')->make(CurrentUser::class);

            $this->studentRepository = \Zend_Registry::get('container')->make(StudentLegacyRepository::class);

            $this->student = $this->studentRepository->getOneById($this->currentUser->context()->getRoleData()->getId());
            $this->studentName = $this->student ? $this->student->getUserContext()->getUser()->getFullname() : null;
        }

        parent::__construct($options);
    }

    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();

        $this->setDecorators(self::$_formDecorators);

        $dataUse = new Zend_Form_Element_Radio("dataUse");
        $dataUse->setMultiOptions(array(
            "I do not consent to having my anonymous data used for research purposes.",
            "I consent to having my anonymous data used for research purposes."));

        $dataRelease = new Zend_Form_Element_Radio("dataRelease");
        $dataRelease->setMultiOptions(array(
            "I do not consent to having my anonymous data released to other person(s) or college(s) for research purposes only.",
            "I consent to having my anonymous data released to other person(s) or college(s) for research purposes only."));

        $save = new \Fisdap_Form_Element_SaveButton("save");
        $save->setLabel("Submit");
        $this->addElement($save);

        // it's an instructor, just disable everything
        if ($this->isInstructor) {
            $dataUse->setAttrib("disabled", "disabled");
            $dataRelease->setAttrib("disabled", "disabled");
            $save->setAttrib("disabled", "disabled");
            $this->todaysDate = "";
        } else {
            if (!is_null($this->student->research_consent)) {
                if ($this->student->research_consent == 1) {
                    $dataUse->setValue(1);
                    $dataRelease->setValue(1);
                } else {
                    $dataUse->setValue(0);
                    $dataRelease->setValue(0);
                }
            }
        }

        $this->addElement($dataUse);
        $this->addElement($dataRelease);
    }


    public function process($post)
    {
        if ($this->isValid($post)) {
            $values = $this->getValues();

            // either one of these were set to "I do not consent"
            if ($values['dataUse'] == 1 && $values['dataRelease'] == 1) {
                $this->student->research_consent = 1;
            } else {
                $this->student->research_consent = 0;
            }

            $this->studentRepository->update($this->student);
            $this->currentUser->reload();

            return true;
        }
        return false;
    }
}
