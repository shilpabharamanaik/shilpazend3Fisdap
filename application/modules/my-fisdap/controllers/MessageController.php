<?php

class MyFisdap_MessageController extends Fisdap_Controller_Private
{

	public function init()
	{
		$this->user =  \Fisdap\Entity\User::getLoggedInUser();
	}

	public function indexAction()
	{
		$this->view->output = '<a href="my-fisdap/message/create">Create a message</a>';
	}

	public function createAction()
	{
		// only show for staff and instructors
		$isUserStaff = ($this->user->staff != NULL && $this->user->staff->isStaff());
		$isUserInstructor = $this->user->getCurrentRoleName() == 'instructor';

		if($isUserStaff || ($isUserInstructor && $this->user->hasPermission('Edit Program Settings'))) {
			// if this user is instructor, set a particular programID for the form
			if ($isUserInstructor) {
				$programId = $this->user->getProgramId();
			} else {
				// staff just gets null
				$programId = null;
			}


			//check for POST data to see if we need to process form results
			$request = $this->getRequest();
			if ($request->isPost()) {

				$post = $request->getPost();

				// generate the form just so we can check post validity. We'll regenerate later.
				$testValuesForm = $this->getFormMessageCreate(1, $this->user, $programId);
				$autoValid = $testValuesForm->isValid($post);
				// manually check to see if we have at least one user criteria filter checked
				if ($post['userIds'] == '' && $post['everyone'] != TRUE &&  empty($post['user_context'])) {
					$criteriaValid = FALSE;
				} else if ($post['userIds'] != '') {
					// the Advanced Recipient ID field is populated, so we should check to make sure the data entered is formatted properly
					$userIdsString = trim($post['userIds']);
					$userIds = explode(',', $userIdsString);
					$criteriaValid = TRUE;
					$this->view->userIdsSpecified = TRUE;
					foreach($userIds as $id) {
						if (!is_numeric($id) || $id < 1) {
							$criteriaValid = FALSE;
						}
					}
				} else {
					$criteriaValid = TRUE;
				}

				if ($autoValid && $criteriaValid) {
					$this->view->userIsStaff = $isUserStaff;

					// Get the list of recipients to be displayed for confirmation
					$recipients = $this->processUserGroupFilterRecipients($post);
					$this->view->recipientCount = $recipients['count'];
					$this->view->recipientList = $recipients['recipients'];

					// If we don't have a recipient count, we are still at step one (can't send message without recipients)
					if($recipients['count'] < 1) {
						$currentStep = 1;
						$this->view->errorMessage = "Using the recipient settings you selected, your message would be sent to 0 people.  Please broaden your criteria.";
					} else {
						$currentStep = 2;
					}

					$this->view->currentStep = $currentStep;

					// get the form and set action
					$this->view->form = $this->getFormMessageCreate($currentStep, $this->user, $programId);
					$this->view->form->setAction('/my-fisdap/message/create/step/2');

					// prepare the preview / confirm form
					// call isValid to fill the form with post'd values (again, because we regenerated)
					$this->view->form->isValid($post);

					// Send back the message for preview
					$this->view->messageSubject = $post['subject'];
					// is the message plain text? If so, add linebreaks
					$match = preg_match('/<[A-Za-z][A-Za-z0-9]*>/', $post['body']);
					if ($match > 0) {
						// HTML is included! Display as-is
						// @todo should this be limited to staff?
						$this->view->messageBody = $post['body'];
					} else {
						// Plain text
						// replace carriage returns with simple <br /> for display
						$this->view->messageBody = str_replace(array("\r\n", "\n", "\r"), '<br />', $post['body']);
					}

					// Set form action to propel to the next step

					// If the user is not staff, there is no confirmation.
					$isValidMessage = false;
					if(($currentStep == 2 && $isUserStaff && $post['confirmation'] == 1)){
						$isValidMessage = true;
					}elseif($currentStep == 2 && !$isUserStaff && $isUserInstructor && $post['confirmation'] == 1){
						$isValidMessage = true;
					} elseif (!$isUserStaff && $isUserInstructor) {
						// staff need to get their confirmation value set implicitly
						// set confirmation to 1
						$confirmation = $this->view->form->getElement('confirmation');
						$this->view->form->populate(array('confirmation' => 1));
					}

					if ($isValidMessage) {

						// Message has been confirmed. Svae and deliver it.

						// @todo check for "is not staff": non-staff should only send to recipients within their program


						$message = new \Fisdap\Entity\Message();
						$message->created = new \DateTime();
						$message->set_title($this->view->messageSubject);
						$message->set_body($this->view->messageBody);
						$message->set_author_type(3); //user account is the sender
						$message->set_author($this->user);

						// add due date
						/*
						$event = new \Fisdap\Entity\Event();
						$event->set_start(new \DateTime('now'));
						$message->set_due($event);

						// enable todo for recipients
						$todo = new \Fisdap\Entity\Todo();
						 */

						$to = array_keys($recipients['recipients']);
						$successfulRecipients = $message->deliver($to);
						//$successfulRecipients = $message->deliver($to, 0, 0, array('todo' => $todo));

						//$this->view->output = 'Message title: ' . $message->title;
						$this->view->success = '<div class="success">Successfully delivered to ' . count($successfulRecipients) . " recipients.</div><br /> <br />";

						// list any uncessful deliveries
						if (count($successfulRecipients) != count($to)) {
							$errors = array();
							foreach($recipients['recipients'] as $id => $recipient) {
								if (!array_key_exists($id, $successfulRecipients)) {
									$errors[] = $recipient;
								}
							}

							// Don't error report if the everyone checkbox was checked, since that 
							// tends to freak out a bit.
							if(!$post['everyone']){
								$this->view->error = 'Delivery failed with the following recipients: ' . implode(', ', $errors);
							}
						}

					}
				} else {
					$this->view->form = $testValuesForm ;
					if ($criteriaValid) {
						$this->view->form->setDescription('Sorry, your message did not submit. Please check the data you entered.');
					} else if ($post['userIds'] != '') {
						$this->view->form->setDescription('Sorry, your message did not submit because you entered an improper value into the "Comma-separated list of user IDs" field. That field must be only numbers and commas. Numbers must be User IDs.');
					} else {
						$this->view->form->setDescription('Sorry, your message did not submit. You need tdo select at least one criteria indicating who the recipients of the message should be.');
					}
				}
			} else {
				// no POST data
				// prepare the starting form
				if ($isUserStaff) {
					$this->view->form = $form = $this->getFormMessageCreate($currentStep, $this->user, null); // staff version (no program id supplied) current step 1
				} else {
					$this->view->form = $form = $this->getFormMessageCreate($currentStep, $this->user, $programId); // instructor version (prog id) current step 1
				}
				$form->setAction('/my-fisdap/message/create/step/2'); // next step 2
			}

		} else {
			// this user is not staff or instructor, then he/she is not allowed to view the page, so DENY
			$this->_redirect("/index/not-allowed");
		}
	}


	/**
	 * Return the message creation form
	 *
	 * @return MyFisdap_Form_MessageCreate
	 */
	public function getFormMessageCreate($step, $user, $programId)
	{
		return new MyFisdap_Form_MessageCreate($step, $user, $programId);
	}

	/**
	 * Derive a list of recipients from a UserGroupFilter form
	 *
	 * @param array $post POST data from form submission
	 * @param integer $programId Optional program ID to limit results
	 *
	 * @return array Array with 'count' and 'recipients' keys. In most cases recipients is array of firstname/lastname keyed by UserLegacy ID
	 */
	public function processUserGroupFilterRecipients($post, $programId = null) {
		// is a programId override set as an argument, or submitted in the form?
		if (!$programId && is_numeric($post['picker_program_id']) && $post['picker_program_id'] > 0) {
			$programId = $post['picker_program_id'];
		}

		// get user repo
		$userRepo = \Fisdap\EntityUtils::getRepository('User');

		// prep results
		$results = array('count' => 0, 'recipients' => array());

		if ($post['userIds'] != '') {
			// the Advanced Recipient ID field is populated, so this takes precedence over everything else
			$userIdsString = trim($post['userIds']);
			$userIds = explode(',', $userIdsString);
			$prelimCount = count($userIds);

			$this->em = \Fisdap\EntityUtils::getEntityManager();
			foreach($userIds as $key => $id) {
				$id = trim($id);
				$user = $this->em->find('\Fisdap\Entity\User', $id);
				if ($prelimCount > 250) {
					$results['recipients'][$id] = ''; // don't get firstname/lastname, list too long
				} else {
					$results['recipients'][$id] = $user->first_name . " " . $user->last_name;
				}
			}

			$results['count'] = count($results['recipients']);

		} else if ($post['everyone']) {
			// "everyone" checkbox is selected, so pull all users system-wide
			if ($programId) {
				// we are sending to everyone in this program
				$programUsers = $userRepo->findUsers($programId);
				foreach($programUsers as $user) {
					$results['count']++;
					$results['recipients'][$user->id] = $user->get_first_name() . " " . $user->get_last_name();
				}
			} else {
				// we are sending to everyone SYSTEMWIDE
				// do a permissions triple check, because this needs to be a staff person
				if ($this->user->staff != NULL && $this->user->staff->isStaff()) {
					$allUsers = $userRepo->getAllUsers(array('id'));
					foreach($allUsers as $id => $user) {
						$results['count']++;
						$results['recipients'][$id] = ''; // we don't retreive names for such a large dataset
					}
				}
			}
		} else { // "everyone" is NOT selected
			// more selective query. See if which roles we need to add, might be multiple
			// keep in mind that we are passing $selectFields to findUsers(), which causes arrays to be returned instead of entity objects
			$roleResults = array();
			if (in_array('instructor', $post['user_context'])) {
				$roleResults[] = $userRepo->findUsers($programId, '', null, array('instructor'), array('u.id', 'u.first_name', 'u.last_name'));
			}

			// commented out because now we always get student picker results if student role is checked
			//if (in_array('student', $post['user_context']) && $post['student_picker_enabled'] == 0) {
			//$roleResults[] = $userRepo->findUsers($programId, '', null, array('student'), array('u.id', 'st.first_name', 'st.last_name'));
			//}

			if (in_array('staff', $post['user_context'])) {
				$roleResults[] = $userRepo->findUsers($programId, '', null, array('staff'), array('u.id', 'staff.first_name', 'staff.last_name'));
			}

			$this->view->selectedRoles = $post['user_context'];
			$this->view->usingPicker = $post['student_picker_enabled'];

			$users = array();
			if (!empty($roleResults)) {
				foreach($roleResults as $roleUsers) {
					$users = array_merge($users, $roleUsers);
				}
			}

			if (!empty($users)) {
				$results['count'] = count($users);
				foreach($users as $user) {
					if ($results['count'] > 250) { // @todo set a class property for this 250 value instead of isolated hardcoded
						$results['recipients'][$user['id']] = ''; // don't get first_name/last_name, too long
					} else {
						$results['recipients'][$user['id']] = $user['first_name'] . " " . $user['last_name'];
					}
				}
			}

			// should we ge tstudent picker results?
			// If $results is empty or some kind of filtering was done (student['student'] SELECT reflects )
			if ($post['student']['selected_students'] != '' && (empty($results) || in_array('student', $post['user_context']))) {
				// we have no users from any of the other filter options.
				// as a default let's just assume then that we using the StudentFilter to choose recipients
				// STUDENT PICKER RETURNS STUDENT IDS, NOT USER IDS
				$studentIds = $post['student']['selected_students'];
				$studentIds = explode(',', $studentIds);

				// right now this ID array is spiked with underscores to maintain value (prob chrome bug), so remove those underscores
				$this->em = \Fisdap\EntityUtils::getEntityManager();
				foreach($studentIds as $key => $id) {
					$id = str_replace('_', '', $id);
					$student = $this->em->find('\Fisdap\Entity\StudentLegacy', $id);
					$results['recipients'][$student->user->id] = $student->first_name . " " . $student->last_name;
				}

				// re-count results
				$results['count'] = count($results['recipients']);
			}

		}

		return $results;
	}


	/**
	 * Create a todo message for yourself
	 */
	function todoAction() {
		// @todo real perm check
		if ($this->user->staff != NULL && $this->user->staff->isStaff()) {
			// the form

			$form = $this->getFormTodoCreate();
			//check for POST data to see if we need to process form results
			$request = $this->getRequest();				
			if ($request->isPost()) {
				$post = $request->getPost();
				if ($form->isValid($post)) {
					// PROCESS DATA
					$message = new \Fisdap\Entity\Message();
					$message->set_title($post['subject']);
					$message->set_author_type(3);
					$message->set_author($this->user);

					// add due date
					$event = new \Fisdap\Entity\Event();
					$event->set_start(new \DateTime($post['date']));
					$message->set_due($event);

					// enable todo for recipients
					$todo = new \Fisdap\Entity\Todo();
					$todo->set_notes($post['notes']);

					$to = array($this->user);
					$successfulRecipients = $message->deliver($to, 0, 0, array('todo' => $todo));

					if (count($successfulRecipients) == 1) {
						$this->view->result = 'Todo successfully created.';
						$this->view->form = $this->getFormTodoCreate();
					} else {
						$this->view->result = 'Oops, there was a problem. Your todo was not created.';
						$this->view->form = $form;
					}
				}
			} else {
				$this->view->form = $form;
			}
		} else {
			// this user does not have proper permissions, so DENY
			$this->_redirect("/index/not-allowed");
		}
	}


	/**
	 * Return the Todo creation form
	 *
	 * @return MyFisdap_Form_TodoCreate
	 */
	public function getFormTodoCreate()
	{
		return new MyFisdap_Form_TodoCreate();
	}
}
