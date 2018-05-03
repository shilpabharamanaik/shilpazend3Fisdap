<?php

class LearningCenter_Form_SubmitScores extends Fisdap_Form_Base
{

    /**
     * @var array of user ids for students that have a completed test attempt.
     */
    public $users;

    /**
     * @var array of test scores for the user's program
     */
    public $program_scores;

    /**
     * @var array of filter information for setting the defaults of the filter elements, if desired
     */
    public $filters;

    public function __construct($formUsers, $programScores, $filters = null, $options = null)
    {
        $this->users = $formUsers;
        $this->program_scores = $programScores;
        $this->filters = $filters;

        return parent::__construct($options);
    }

    public function init()
    {
        parent::init();

        $this->setAttrib('id', "submit-scores-form");
        $this->addCssFile("/css/library/LearningCenter/Form/submit-scores.css");
        $this->addJsFile("/js/library/LearningCenter/Form/submit-scores.js");

        //make the filter set
        //cert levels
        $cert = new Fisdap_Form_Element_CertificationLevel("certificationLevels");
        $cert->setLabel("Certification Level:");

        if($this->filters['certificationLevels']){
            $cert->setValue($this->filters['certificationLevels']);
        }

        $this->addElement($cert);

        //grad date
        $grad = new Fisdap_Form_Element_GraduationDate("grad");
        $grad->useExistingGraduationYears();

        if($this->filters['graduationYear'] || $this->filters['graduationMonth']){
            $value = array('year' => $this->filters['graduationYear'], 'month' => $this->filters['graduationMonth']);
            $grad->setValue($value);
        }
        $this->addElement($grad);

        //graduation status

        $status = new Zend_Form_Element_MultiCheckbox("status");

        $options = array(
            1 => "Active",
            2 => "Graduated",
            4 => "Left Program",
        );

        $status->setMultiOptions($options);
        $status->setLabel("Graduation Status:");

        if($this->filters['gradStatus']){
            $status->setValue($this->filters['gradStatus']);
        }else{
            $status->setValue(array(1));
        }

        $this->addElement($status);

        //student groups
        $user = \Fisdap\Entity\User::getLoggedInUser();
        $classSectionRepository = \Fisdap\EntityUtils::getRepository('ClassSectionLegacy');
        $groups = $classSectionRepository->getFormOptions($user->getProgramId());
        $groups['Any group'] = "Any group";
        ksort($groups);

        $groupSelect = new Zend_Form_Element_Select('groups');
        $groupSelect->setMultiOptions($groups);

        $groupSelect->setAttribs(array("style"=>"width:250px"))
            ->setLabel('Groups:');

        if($this->filters['section']){
            $groupSelect->setValue($this->filters['section']);
        }

        $this->addElement($groupSelect);

        //create two radio buttonsets for each student

        foreach($this->users as $user){

            $userScores = array();

            if($this->program_scores[$user['user_id']]){
                $userScores = $this->program_scores[$user['user_id']];
            }

            $writtenPassFail = new Zend_Form_Element_Radio("pass_fail_written_" . $user['user_id']);
            $writtenPassFail->setMultiOptions(array(
                "-1" => "Unset",
                "1" => "Pass",
                "0" => "Fail",
            ));
            $writtenPassFail->setSeparator('');
            //set value to stored score if we have it
            if($this->program_scores[$user['user_id']]) {
                foreach ($userScores as $score) {
                    if (in_array($score['type_id'], array(1, 3, 21))) {
                        $writtenPassFail->setValue($score['pass_or_fail']);
                        break;
                    }else{
                        $writtenPassFail->setValue(-1);
                    }
                }
            }else {
                $writtenPassFail->setValue(-1);
            }

            $this->addElement($writtenPassFail);


            $practicalPassFail = new Zend_Form_Element_Radio("pass_fail_practical_" . $user['user_id']);
            $practicalPassFail->setMultiOptions(array(
                "-1" => "Unset",
                "1" => "Pass",
                "0" => "Fail",
            ));
            $practicalPassFail->setSeparator('');

            //set value to stored score if we have it
            if($this->program_scores[$user['user_id']]) {
                foreach ($userScores as $score) {
                    if (in_array($score['type_id'], array(2, 4, 23))) {
                        $practicalPassFail->setValue($score['pass_or_fail']);
                        break;
                    }else{
                        $practicalPassFail->setValue(-1);
                    }
                }
            }else {
                $practicalPassFail->setValue(-1);
            }


            $this->addElement($practicalPassFail);

        }

        $save = new Fisdap_Form_Element_SaveButton("save");
        $save->setDecorators(array("ViewHelper"));
        $this->addElement($save);

        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => "forms/submitScores.phtml")),
            'Form'
        ));
    }

    public function process($post){

        if($this->isValid($post)){
            $values = $this->getValues();

            // get repo to retrieve existing scores for each user
            $testScoreRepo = \Fisdap\EntityUtils::getRepository('TestScoreLegacy');

            foreach($this->users as $user){

                $userid = $user['user_id'];

                $userScores = $testScoreRepo->getNremtScoresByUser($userid);

                if(in_array($values['pass_fail_written_' . $userid], array('1', '0', '-1', 1, 0, -1), TRUE)){
                    //if we have scores for this user look through those for one corresponding to written test types, if none, create a new one
                    if($userScores){
                        foreach($userScores as $score){
                            if(in_array($score->test_type->id, array(3, 21, 1))){
                                $testScore = $score;
                                break;
                            }else{
                                $testScore = new \Fisdap\Entity\TestScoreLegacy();
                            }
                        }
                    }else {
                        $testScore = new \Fisdap\Entity\TestScoreLegacy();
                    }
                    //ugly, but we want to only update and save the entity if the value is different than the existing one
                    if(($testScore->pass_or_fail != $values['pass_fail_written_' . $userid]) && (!is_null($testScore->pass_or_fail) || in_array($values['pass_fail_written_' . $userid], array('1', '0', 1, 0), TRUE))) {

                        $userEntity = \Fisdap\EntityUtils::getEntity('User', $userid);

                        //old users won't have a user context set, so we need to make this call to have one set before we access role data
                        $userContext = $userEntity->getCurrentUserContext();

                        $testScore->set_student($userContext->getRoleData());
                        $testScore->test_score = -1; //we don't record actual scores

                        $testScore->pass_or_fail = $values['pass_fail_written_' . $userid];
                        $testScore->entry_time = new \DateTime();

                        // 1 = EMT, 2 = AEMT, 4 = Paramedic

                        if ($user['cert_bit'] == 1) {
                            $testScore->set_test_type(3);
                        } else if ($user['cert_bit'] == 2) {
                            $testScore->set_test_type(21);
                        } else if ($user['cert_bit'] == 4) {
                            $testScore->set_test_type(1);
                        } else {
                            //default to 1
                            $testScore->set_test_type(1);
                        }

                        $testScore->save();
                    }
                }

                if(in_array($values['pass_fail_practical_' . $userid], array('1', '0', "-1", 1, 0, -1), TRUE)){

                    //if we have scores for this user look through those for one corresponding to written test types, if none, create a new one
                    if($userScores){
                        foreach($userScores as $score){
                            if(in_array($score->test_type->id, array(4, 23, 2))){
                                $testScore = $score;
                                break;
                            }else{
                                $testScore = new \Fisdap\Entity\TestScoreLegacy();
                            }
                        }
                    }else {
                        $testScore = new \Fisdap\Entity\TestScoreLegacy();
                    }

                    if(($testScore->pass_or_fail != $values['pass_fail_practical_' . $userid]) && (!is_null($testScore->pass_or_fail) || in_array($values['pass_fail_practical_' . $userid], array('1', '0', 1, 0), TRUE))) {

                        $userEntity = \Fisdap\EntityUtils::getEntity('User', $userid);

                        //old users won't have a user context set, so we need to make this call to have one set before we access role data
                        $userContext = $userEntity->getCurrentUserContext();

                        $testScore->set_student($userContext->getRoleData());
                        $testScore->test_score = -1; //we don't record actual scores

                        $testScore->pass_or_fail = $values['pass_fail_practical_' . $userid];
                        $testScore->entry_time = new \DateTime();

                        // 1 = EMT, 2 = AEMT, 4 = Paramedic
                        if ($user['cert_bit'] == 1) {
                            $testScore->set_test_type(4);
                        } else if ($user['cert_bit'] == 2) {
                            $testScore->set_test_type(23);
                        } else if ($user['cert_bit'] == 4) {
                            $testScore->set_test_type(2);
                        } else {
                            //default to 1
                            $testScore->set_test_type(2);
                        }

                        $testScore->save();
                    }
                }
            }
            return true;
        }
        return false;
    }

}
