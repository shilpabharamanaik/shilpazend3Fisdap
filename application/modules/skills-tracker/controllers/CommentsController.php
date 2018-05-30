<?php

use Fisdap\Data\Shift\ShiftLegacyRepository;
use Fisdap\Data\User\UserRepository;
use Fisdap\Entity\Comment;
use Fisdap\Entity\Message;
use Fisdap\Entity\User;

/**
 * Class Lib_CommentingController
 *
 * @todo create CommentRepository
 */
class SkillsTracker_CommentsController extends Zend_Controller_Action
{
    /**
     * @var Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var User
     */
    private $user;


    public function init()
    {
        // enable database logging:
        //Util_Db::dbLoggingOn();

        $this->user = User::getLoggedInUser();

        /* Initialize action controller here */
        $this->logger = Zend_Registry::get('logger');
    }


    public function getCommentsAction(UserRepository $userRepository)
    {
        $p = $this->getAllParams();
        $p['userId'] = $this->user->id;    //logged in user
        $idPrefix = $p['dynElPrefix'];

        $curUserIsInstructor = ($this->user->getCurrentRoleName() == 'instructor');

        $messages = array();

        $comments = array();

        $requiredNotEmpty = array('tb', 'dId');
        foreach ($requiredNotEmpty as $fl) {
            if (empty($p[$fl])) {
                $messages['requiredNotEmpty'] = 'Expecting not empty parameter: ' . $fl;
            }
        }

        // enable / disable viewing of soft-deleted comments
        Comment::loadSoftDeletedRows(false);

        if (empty($messages)) {    // skip loading if errors
            // get comments
            //$comment=\Fisdap\EntityUtils::getEntity('Comment', $commentId);
            $users = array();
            $commentEnts = Comment::getUserViewableComments($p['tb'], $p['dId'], $p['userId']);
            foreach ($commentEnts as $id => $cEnt) {

                // skip instructor only comments for not instructors
                if ($cEnt->instructor_viewable_only && !$curUserIsInstructor) {
                    continue;
                }

                $cId = $idPrefix . $cEnt->id;

                $uId = $cEnt->user->id;
                if (!isset($users[$uId])) {
                    $users[$uId] = $userRepository->getOneById($uId);
                }
                $comments[$cId]['cId'] = $cEnt->id;
                $comments[$cId]['uId'] = $cEnt->user->id;    //$users[$uId]->id;
                $comments[$cId]['uNick'] = $this->userNickname(
                    $users[$uId]
                ); //->first_name . ' ' . $users[$uId]->last_name;
                $comments[$cId]['comment'] = $cEnt->comment;
                $comments[$cId]['created'] = $cEnt->created->format(
                    'F j, Y' . "\n" . 'H:i:s'
                );// ('n/j/Y<br>H:i:s');	//'m/d/Y H:i:s' <b\r/>
                $comments[$cId]['createdDate'] = $cEnt->created->format('F j, Y');
                $comments[$cId]['createdTime'] = $cEnt->created->format('H:i:s');

                $comments[$cId]['editable'] = $cEnt->editable;
                $comments[$cId]['deletable'] = $cEnt->deletable;
                if ($cEnt->soft_deleted) {
                    $comments[$cId]['deleted'] = true;
                }
                if ($cEnt->instructor_viewable_only) {
                    $comments[$cId]['instructor_only'] = true;
                }

                // remove comment content if comment deleted and not undeletable (different user)
                if ($comments[$cId]['deleted'] && !$comments[$cId]['deletable']) {
                    $comments[$cId]['comment'] = '';
                }
            }
        }

        $debug['comments_array'] = $comments;

        $this->view->debug = print_r($debug, true);
        $this->view->messages = $messages;
        $this->view->comments = $comments;
        $this->view->curUserNick = $this->user->first_name . ' ' . $this->user->last_name;
        $this->view->curUserIsInstructor = $curUserIsInstructor;
        $form = $this->getCommentForm($p['dId']);
        $this->view->commentform = (string)$form;

        // this may look odd, but it works...previously a Zend AjaxContext was used but that broke when we upgraded ZF
        $this->_helper->json($this->view);
    }


    private function getCommentForm($runId)
    {
        $addCommentForm = new Zend_Form();
        $commentsForm = new Fisdap_Form_CommentContacts();
        $commentsForm->customInit($runId);

        $addCommentForm->addSubForm($commentsForm, 'contactSubform');

        $addCommentForm->setDecorators(
            array(
                'FormElements',
                'Form',
                array('comments_div' => 'HtmlTag', array('tag' => 'div', 'id' => 'add-comment-form')),
            )
        );

        return $addCommentForm;
    }


    public function saveCommentAction(UserRepository $userRepository, ShiftLegacyRepository $shiftRepository)
    {
        $p = $this->getAllParams(); // tb, dId, cId, comment
        $p['userId'] = $this->user->id; //logged in user

        $p['instructor_only'] = ($p['instructor_only'] == 'true');

        // return messages, empty means: everything's OK
        $messages = array();

        // todo: check permissions for logged in user (allowed to view shifts)
        $commentId = (empty($p['cId']) || $p['cId'] == 'null') ? false : $p['cId'];
        $comment = \Fisdap\EntityUtils::getEntity('Comment', $commentId);

        if (is_null($comment)) {
            $messages['null_entity_returned'][] = 'Unexpected error occurred. Please try again.';
            $this->view->messages = $messages;

            return;
        }

        // Data integrity checks
        if ($commentId) { // editing exising comment
            $debug['commentId'] = 'true: ' . $commentId;
            // When editing not allow to change: 1.userid, 2.table, 3.table_data_id
            $errdt = 'data_cannot_be_changed';
            if ($comment->table != $p['tb']) {
                $messages[$errdt][] = 'Table changed';
            }
            if ($comment->table_data_id != $p['dId']) {
                $messages[$errdt][] = 'Table Id Field changed';
            }
            if ($comment->user->id != $this->user->id) {
                $debuginfo = ' from: ' . $comment->user->id . " to: " . $this->user->id;
                $messages[$errdt][] = 'User Id has changed' . $debuginfo;
            }
        } else { // new comment
            $debug['commentId'] = 'false: ' . $commentId;
            // When new comment verify required info is set
            $comment->table = $p['tb'];
            $comment->table_data_id = $p['dId'];
            // get the Fisdap User to associate with the comment
            $pUser = $userRepository->getOneById($p['userId']);
            $comment->user = $pUser;
            $comment->instructor_viewable_only = $p['instructor_only'];

            $notEmpty = array(
                'tb'     => 'Table name must be provided',
                'dId'    => 'Table Id Field must be provided',
                'userId' => 'User Id is missing'
            );
            foreach ($notEmpty as $dt => $errMsg) {
                if (empty($p[$dt])) {
                    $messages['empty_' . $dt] = $errMsg;
                }
            }
        }

        if (empty($messages)) {
            $comment->comment = $p['comment'];
            $comment->save();

            // Create a Fisdap Message
            // for the shift's user OR relevant instructors
            if ($comment->table == 'shifts') {
                // load the shift info so we can determine if the commenting user is the shift owner or not
                // instead of relying solely on the "email to..." interaction
                $shift = $shiftRepository->getOneById($comment->table_data_id);

                if ($shift->id) {
                    // assemble recipients
                    $to = array();
                    if ($shift->student->user->id != $this->user->id) {
                        // shift owner does not match commenting user, so send to shift owner
                        $to[] = $shift->student->user->id;
                    }
                    if (is_array($p['toEmail'])) {
                        foreach ($p['toEmail'] as $emailString) {
                            // Also send to each recipient that the user marked as needing email notification
                            $emailStringSplit = explode('_', $emailString);
                            if (is_numeric($emailStringSplit[1]) && !in_array($emailStringSplit[1], $to)
                                && $emailStringSplit[1] != $this->user->id
                            ) {
                                $to[] = $emailStringSplit[1];
                            }
                        }
                    }

                    if (!empty($to)) {
                        // assemble message and deliver
                        $message = new Message();
                        $message->set_title(
                            $this->user->first_name . ' ' . $this->user->last_name . ' left a comment for you'
                        );
                        $message->set_body(
                            '<p>' . $comment->comment . '</p><p><a href="/skills-tracker/shifts/comments/id/'
                            . $shift->id . '">Click here to view and respond to the comment</a></p>'
                        );
                        $message->set_author_type(1); // sent as a system (fisdap robot) message

                        $successfulRecipients = $message->deliver($to);
                    }
                }
            }

            if (is_array($p['toEmail'])) {
                // Loop over the toEmails and send out emails to all users who were checked...
                foreach ($p['toEmail'] as $email) {
                    list($type, $id) = explode('_', $email);

                    //list($junk, $type) = explode('-', $junk);
                    if (stripos($type, 'preceptor') || $type == 'preceptor') {
                        $this->sendEmail($p['comment'], \Fisdap\EntityUtils::getEntity('PreceptorLegacy', $id));
                    } elseif (stripos($type, 'instructor') || $type == 'instructor') {
                        $this->sendEmail(
                            $p['comment'],
                            $userRepository->getOneById($id)->getCurrentRoleData()
                        );
                    } elseif (stripos($type, 'student') || $type == 'student') {
                        $this->sendEmail(
                            $p['comment'],
                            $userRepository->getOneById($id)->getCurrentRoleData()
                        );
                    }
                }
            }
        }

        // return values: dId
        $this->view->cId = $comment->id;
        $this->view->created = $comment->created->format('F j, Y' . "\n" . 'H:i:s');
        $this->view->createdDate = $comment->created->format('F j, Y');
        $this->view->createdTime = $comment->created->format('H:i:s');
        $this->view->uNick = $this->userNickname($this->user);
        $this->view->uId = $this->user->id;
        $this->view->editable = $comment->editable;
        $this->view->deletable = $comment->deletable;
        $this->view->comment = $comment->comment;
        $this->view->instructor_only = $comment->instructor_viewable_only;

        // errors / notifications
        $this->view->messages = $messages;

        // debug
        $debug['cId'] = $comment->id;
        $debug['pa'] = $p;
        $debug['user'] = (is_null($this->user)) ? null
            : $this->user->toArray();     //Zend_Auth::getInstance()->getIdentity()
        $debug['test_return'] = 'testing savecomment';
        $debug['messages'] = $messages;

        $this->view->debug = print_r($debug, true);
        $this->view->userNickname = $this->userNickname($this->user);

        $this->_helper->json($this->view);
    }


    public function doneCommentingAction()
    {
        $p = $this->getAllParams();

        $this->sendNotifications();
        $debug['allReports'] = Comment::generateNotifications($p[tb], $p[dId], $p[cIds]);
        $debug['p'] = $p;

        $debug['var_dumps'] = Util_Debug::getVarDumps();
        $this->view->debug = print_r($debug, true);

        $this->_helper->json($this->view);
    }


    private function sendNotifications()
    {
        $p = $this->getAllParams();

        $users = Comment::getUsersOnPost($p[tb], $p[dId]);

        foreach ($users as $user) {
            $comments = Comment::getUserViewableComments('shifts', $p[dId], $user);
            /// TODO HEREEEEE
        }

        foreach ($users as $user) {
            $user_ids[] = $user->id;
        }
        $debug['users'] = $user_ids;
        $debug['params'] = $p;

        $this->view->debug = print_r($debug, true);
    }


    public function deleteCommentAction()
    {
        $p = $this->getAllParams();

        // todo: check if user is allowed to delete this comment?

        $success = false;
        if (empty($p['cId'])) {
            $debug['actionTaken'] = 'Could not delete comment ' . $p['cId'] . ' Requested action: '
                . $p['requestedAction'];
        } else {
            // delete / undelete actions
            if ($p['requestedAction'] == 'delete' || $p['requestedAction'] == 'undelete') {
                $action = $p['requestedAction'];
                $newDbValue = ($action == 'delete');

                $debug['actionTaken'] = "ready to $action comment" . $p['cId'];
                $comment = \Fisdap\EntityUtils::getEntity('Comment', $p['cId']);
                if (!is_null($comment)) {
                    $comment->soft_deleted = $newDbValue;
                    $comment->save();
                    $success = true;
                    $debug['actionTaken'] = "Soft $action successful";
                } else {
                    $debug['actionTaken'] = "Tried {$action}ing, but comment " . $p['cId'] . ' not present in database';
                }
            }
        }
        $debug['params'] = $p;

        $this->view->debug = print_r($debug, true);

        $this->view->success = $success;

        $this->_helper->json($this->view);
    }


    /**
     * This function sends out a simple email to the requested recipients
     *
     * @param type $comment
     * @param type $recipient
     */
    private function sendEmail($comment, $recipient)
    {
        $p = $this->getAllParams();
        if ($recipient->email != '') {
            $message = $this->user->first_name . ' ' . $this->user->last_name . ' posted the following comment: \n\n'
                . $comment;
            $htmlMessage = $this->user->first_name . ' ' . $this->user->last_name
                . ' posted the following comment: <br /><br />' . $comment;
                
            $htmlMessage .= '<br /><br /> Click <a href="https://members.fisdap.net/skills-tracker/shifts/comments/id/'.$p[dId].'"> here </a> to view the comment in Fisdap<br /><br />';

            $mail = new \Fisdap_TemplateMailer();
            $mail->setBodyText($message);
            $mail->setBodyHtml($htmlMessage);

            $mail->addTo($recipient->email, $recipient->first_name . ' ' . $recipient->last_name);
            // For testing purposes
            $mail->setSubject('New comment posted.');
            $mail->send();
        }
    }


    /**
     * @param User $user
     *
     * @return string
     */
    private function userNickname(User $user)
    {
        return $user->first_name . ' ' . $user->last_name;
    }
}
