<?php

class LearningCenter_TestController extends Fisdap_Controller_Private
{
    public function init()
    {
        parent::init();
    }

    public function editAction()
    {
        $this->permissionsCheck();
    
        $request = $this->getRequest();
        $scheduledTest = \Fisdap\EntityUtils::getEntity('ScheduledTestsLegacy', $this->_getParam('stid', false));
        
        if ($scheduledTest->id) {
            $this->view->pageTitle = "Edit Test";
        } else {
            $this->view->pageTitle = "New Test";
        }
        
        // so we don't lose selected students after posting an erro
        $postData = $request->getPost();
        
        $this->view->form = new LearningCenter_Form_ScheduleTest($scheduledTest->id, $postData['studentIDs']);
        
        if ($request->isPost()) {
            // manually check a couple things for validation
            $post = $request->getPost();
            $this->view->form->checkedStudents = $post['studentIDs'];
            $today = new \DateTime();
            $start_date = strtotime($post['start_date']);
            $end_date = strtotime($post['end_date']);
            if ($post['start_date'] == '' || ($start_date <= $end_date)) {
                // OK, process the form
                if ($scheduledTestId = $this->view->form->process($request->getPost())) {
                    $this->_redirect("/learning-center/test/details/stid/" . $scheduledTestId);
                }
            } elseif ($start_date > $end_date) {
                $this->view->form->error ='Oops, your exams start date ( '.$start_date.' ) is later than its end date ('.$end_date.') . Please choose a start date that is the same day as or earlier than the end date.';
                $this->view->form->isValid($post);
            }
        }
    }
    
    public function deleteAction()
    {
        $this->permissionsCheck();

        $request = $this->getRequest();
        $scheduledTest = \Fisdap\EntityUtils::getEntity('ScheduledTestsLegacy', $this->_getParam('stid', false));
        
        if ($scheduledTest->id) {
            $this->view->pageTitle = "Delete scheduled exam " . $scheduledTest->test->test_name . " on " . $scheduledTest->start_date->format("m/d/Y") . " - " . $scheduledTest->end_date->format("m/d/Y");
        } else {
            $this->view->pageTitle = "Error - no scheduled exam found";
        }

        $this->view->form = new LearningCenter_Form_DeleteScheduleTest($scheduledTest->id);
        
        if ($request->isPost()) {
            $this->view->form->process($request->getPost());
            $this->_redirect("/learning-center/index/schedule");
        }
    }
    
    public function learningRxAction()
    {
        $this->permissionsCheck(true);
        
        $attemptId = $this->_getParam('aid');
        $stupdentId = $this->_getParam('sid');
    }
    
    public function detailsAction()
    {
        $this->permissionsCheck();
        
        $scheduledTest = \Fisdap\EntityUtils::getEntity('ScheduledTestsLegacy', $this->_getParam('stid', false));
        $this->view->scheduledTest = $scheduledTest;
        
        // get passwords!
        $passwords = $scheduledTest->get_passwords();
        ksort($passwords);
        $this->view->testPasswords = $passwords;

        // assign values that view can use for display
        $formattedStudents = array();
        // get students' attempt limit info on this test
        $attemptInfo = $scheduledTest->getStudentAttemptInfo();
        // get products associated
        $showUpgradeWarning = false;
        $usersToUpgrade = array();
        foreach ($scheduledTest->students as $student) {
            $hasProductAccess = false;
            foreach ($student->user->serial_numbers as $serialNumber) {
                if (count($scheduledTest->test->products) > 0) {
                    foreach ($scheduledTest->test->products as $product) {
                        if ($serialNumber->configuration & $product->configuration) {
                            $hasProductAccess = true;
                        }
                    }
                } else {
                    // there is no product associated wtih the moodletestdata entity, so we assume everyone has access
                    $hasProductAccess = true;
                }
            }

            // check if the student has access to testing, due to product limitation or being out of attempts
            $noProductAccess = '';
            if (!$hasProductAccess || ($attemptInfo[$student->user->id]['remaining'] != null && $attemptInfo[$student->user->id]['remaining'] < 1)) {
                $noProductAccess = '*';
                $showUpgradeWarning = true;
                
                //Save this user for later when we need to pass that info to the upgrade page
                $usersToUpgrade[] = $student->user->id;
            }

            $formattedStudents[$student->last_name . $student->first_name . $student->id] = $noProductAccess . $student->first_name . ' ' . $student->last_name;
        }
        
        // if one or more students' accounts need to be upgraded in order to access the test, we need to provide a link to do so
        if ($showUpgradeWarning) {
            // figure out which product should be offered
            foreach ($scheduledTest->test->products as $product) {
                if (!isset($offeredProduct) || $product->price < $offeredProduct->price) {
                    $offeredProduct = $product;
                }
            }
            $this->view->studentUpgradeWarning = '* <a href="/account/orders/upgrade?products[]=' . $offeredProduct->id . '&amp;users[]=' .  implode('&amp;users[]=', array_map('urlencode', $usersToUpgrade)) . '" class="account-upgrade-link">Click here to upgrade these accounts</a>. Students marked with * cannot take the test.';
        }
            
        ksort($formattedStudents);
        $this->view->students = $formattedStudents;
        $this->view->testName = $scheduledTest->test->get_test_name();
        $this->view->testNotes = $scheduledTest->test_notes;
    }
    
    public function multiStudentPickerAction()
    {
    }

    
    public function testDocumentsAction()
    {
        // check for staff permissions
        if (!$this->user->isStaff()) {
            $this->displayError("This tool is available for Fisdap staff only.");
            return;
        }
        
        $this->view->pageTitle = "Moodle Test Documents";
        
        // Get Moodle tests
        $moodleRepos = \Fisdap\EntityUtils::getRepository('MoodleTestDataLegacy');
        $tests = $moodleRepos->getMoodleTestList(array('active' => 1, 'extraGroups' => array('pilot_tests')), 'entity');
        $this->view->tests = $tests;
    }
    
    /*
     * Allow a document to be uploaded that is associated with a Moodle Test Data entity
     * Documents are displayed in the test details page as additional resources
     */
    public function uploadTestDocumentAction()
    {
        if (!$this->user->isStaff()) {
            $this->displayError("This tool is available for Fisdap staff only.");
            return;
        }
        
        $this->view->pageTitle = "Upload a document associated with a Test";
        
        if (is_numeric($this->_getParam('did', false))) {
            $moodleDocumentId = $this->_getParam('did', false);
        } else {
            $moodleDocumentId = null;
        }
        $this->view->form = new LearningCenter_Form_UploadTestDocument($moodleDocumentId);
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $values = $request->getPost();
            $receivedFile = $this->view->form->upload->receive();
            if ($values['test_document_id'] == '' && !$receivedFile) {
                // there should have been a file since we're uploading a brand new document
                $this->displayError("Sorry, there was a problem with the uploaded file.");
            } else {
                if ($receivedFile) {
                    // new file has been uploaded to replace the prior one in this entity
                    $file = $this->view->form->upload;
                } else {
                    $file = null;
                }
                $this->view->form->process($values, $file);
                if ($moodleDocumentId) {
                    // re-create the form, because we've modified the document that already existed (defaults need to change in form)
                    $this->view->form = new LearningCenter_Form_UploadTestDocument($moodleDocumentId);
                }
                $this->_redirect("/learning-center/test/test-documents");
            }
        }
    }
    
    /*
     * Download an uploaded test document
     */
    public function downloadTestDocumentAction()
    {
        if ($this->_getParam('did', false) > 0) {
            $upload = \Fisdap\EntityUtils::getEntity('MoodleTestDocument', $this->_getParam('did', false));
            if ($upload) {
                $upload->getFile();
            } else {
                // error: some problem loading the document. redirect to learning center as a mild sort of error handling
                $this->_redirect("/learning-center");
            }
        }
    }
    
    /*
     * Delete a test document
     */
    public function deleteTestDocumentAction()
    {
        if (!$this->user->isStaff()) {
            $this->displayError("This tool is available for Fisdap staff only.");
            return;
        }
        
        if (is_numeric($this->_getParam('did', false))) {
            $moodleDocument = \Fisdap\EntityUtils::getEntity("MoodleTestDocument", $this->_getParam('did', false));
        } else {
            $this->displayError("Could not load the specified docuemnt.");
            return;
        }
        
        $this->view->pageTitle = "Delete the document: " . $moodleDocument->label;
        $this->view->form = new LearningCenter_Form_DeleteTestDocument($moodleDocument);
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $values = $request->getPost();
            $this->view->form->process($values);
            $this->_redirect("/learning-center/test/test-documents");
        }
    }
    
    private function permissionsCheck($studentsAllowed = false)
    {
        //Don't allow students onto this page unless explicitly stated
        if (!$this->user->isInstructor() && !$studentsAllowed) {
            $this->displayError("Students are not allowed to view this page.");
            return;
        }
        //Check instructor permissions
        if ($this->user->isInstructor() && !$this->user->getCurrentRoleData()->hasPermission("Admin Exams")) {
            $this->displayError("You do not have permission to schedule exams or retrieve scores. Please contact " . $this->user->getCurrentProgram()->getProgramContactName() . " for more information.");
            return;
        }
    }
}
