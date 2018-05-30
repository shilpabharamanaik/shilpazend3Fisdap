<?php

namespace Account\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\SessionManager;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Mvc\MvcEvent;
use Zend\Http\Response;

use User\Entity\User;
use User\Entity\UserContext;
use User\Entity\UserRole;

use User\Entity\StudentLegacy;
use Account\Form\StudentForm;
use Account\Form\ResearchConsentForm;

class StudentController extends AbstractActionController
{
    /**
         * Session manager.
         * @var Zend\Session\SessionManager
         */
    private $sessionManager;

    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    private $objUser;
    private $username;
    private $table;
    
   
    public function __construct($entityManager)
    {
        //$this->table = $table;
        $this->entityManager = $entityManager;
        $userSession = new Container('user');
        $this->username = $userSession->username;
        $this->objUser = $this->entityManager->getRepository(User::class)->findOneByUsername($this->username);
    }
    public function studentAction()
    {
        // Check instructor permissions
        $userContext = $this->objUser->getCurrentUserContext();
        if ($this->objUser->isInstructor()
            && !$userContext->getRoleData()->hasPermission(
                "Edit Student Accounts",
                $this->entityManager
            )
        ) {
            $this->displayError(
                "You do not have permission to edit student accounts. Please contact " . $userContext
                    ->getProgram()->getProgramContactName() . " for more information."
            );

            return;
        }

        // Get the student's ID
        if ($userContext->isInstructor()) {
            //echo "jhgjh";
            $studentId = 327700; //$this->getParam('studentId', $this->globalSession->studentId);
            //$this->globalSession->studentId = $studentId;

            // set up single student picker for instructors
           /* $config = array("student" => $studentId);
            $picklistOptions = array(
                'mode'              => 'single',
                'loadJSCSS'         => true,
                'loadStudents'      => true,
                'useSessionFilters' => true,
                'longLabel'         => true
            );
            echo "FIlTER";*/
            //$this->view->studentPicker = $this->view->multistudentPicklist($this->user, $config, $picklistOptions);
        } else {
            $studentId = $userContext->getRoleData()->getId();
        }

        //$this->view->studentId = $studentId;

        /** @var StudentLegacy $student */
        //$this->view->student = $student = EntityUtils::getEntity('StudentLegacy', $studentId);
        //echo $studentId;
        
        $student = $this->entityManager->getRepository(StudentLegacy::class)->findOneById($studentId);
        //print_r($student);
        
        // Make sure this student is in this user's program
        if ($student && $student->getUserContext()->getProgram()->getId()
            && ($student->getUserContext()->getProgram()->getId() != $userContext->getProgram()->getId())
        ) {
            //reset the student id in the session and the view
           // $this->globalSession->studentId = 0;
           // $this->view->studentId = $studentId = null;
        }

        $pageTitle = "Edit Student Accounts";
        //$this->view->form = new Account_Form_Student($studentId);
        $form = new StudentForm('update', $this->entityManager, $student);
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
             
            $student = $this->entityManager->getRepository(StudentLegacy::class)->findOneById($studentId);
            /*$serial = $student->getSerialNumber();
            if(!is_null($data['certLevel'])){
             $serial->set_certification_level($data['certLevel']);
             $serial->save();
            }*/
            $student->user->first_name = $data['firstName'];
            $student->user->last_name = $data['lastName'];
            $student->user->email = $data['email'];
            $student->user->homePhone = $data['homePhone'];
            $student->user->cellPhone = $data['cellPhone'];
            $student->user->workPhone = $data['workPhone'];
            /* $student->user->address = $data['address'];
             $student->user->city = $data['city'];
             $student->user->country = $data['country'];
             $student->user->state = $data['state'];
             $student->user->zip = $data['zip'];
             $student->user->contact_name = $data['contactName'];
             $student->user->contact_phone = $data['contactPhone'];
             $student->user->contact_relation = $data['contactRelationship'];

             //Transition Course stuff

             if ($serial->hasTransitionCourse()) {
                 $student->user->license_number = $data['licenseNumber'];
                 $student->user->license_state = $data['licenseState'];
                 $student->user->license_expiration_date = $data['licenseExpirationDate'];
                 $student->user->state_license_number = $data['stateLicenseNumber'];
                 $student->user->state_license_expiration_date = $data['stateLicenseExpirationDate'];

                 //Update the user fields in moodle
                 $moodleAPI = new \Util_MoodleAPI("transition_course");
                 $moodleAPI->updateMoodleUser($student->user);
             }


             //student only fields
             if (!$this->instructorView && $data['studentId']) {
                 $data['mailingList'] ? $student->addToMailingList() : $student->removeFromMailingList();
             }

             //Instructor only fields
             if ($this->instructorView && $data['studentId']) {
                 $student->setGraduationDate(new \DateTime($data['gradDate']['year'] . "-" . $data['gradDate']['month'] . "-01"));
                 $student->graduation_status = $data['graduationStatus'];

                 switch ($data['goodData']) {
                     case 1:
                         $student->good_data = true;
                         break;
                     case 0:
                         $student->good_data = false;
                         break;
                     case -1:
                         $student->good_data = NULL;
                         break;
                 }
             }

             //Staff only fields
             if ($this->staffView && $data['studentId']) {
                 $student->setCertification($data['certLevel']);
             }
             if (array_key_exists('newPassword', $data) && $data['newPassword']) {
                 $student->user->password = $data['newPassword'];
             }
*/
            //          $student->save();
            $this->entityManager->persist($student);
            $this->entityManager->flush();
            //$this->saveStudent($student);
        }

        return new ViewModel([
            'studentId' => $studentId,
            'student' => $student,
            'form' => $form,
            'username' => $this->username,
        ]);
    }
    
    public function researchConsentAction()
    {
        $userContext = $this->objUser->getCurrentUserContext();
        $studentname = $userContext->getUser()->getFullname();
        $date = new \DateTime();
        $todaysDate = $date->format("F j, Y");
        if ($this->objUser->isInstructor()) {
            $pageTitle = "Sample Research Consent Form";
        } else {
            $pageTitle = "Research Consent";
        }
        $form = new ResearchConsentForm();
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
        }
        return new ViewModel([
        'studentname' => $studentname,
            'date' => $todaysDate,
            'form' => $form
            ]);

        /*if ($request->isPost()) {
            if ($this->view->form->process($request->getPost()) === true) {
                //Is there a URL to remember from being redirected here?
                if (isset($this->globalSession->requestAgreementURL)) {
                    $url = $this->globalSession->requestAgreementURL;
                    unset($this->globalSession->requestAgreementURL);
                } else {
                    //Otherwise redirect to my fisdap
                    $url = "/my-fisdap";
                }

                $this->redirect($url);
            }
        }*/
    }
}
