<?php namespace Fisdap\Api\Attachments\Listeners;

use Fisdap\Attachments\Queries\Events\AttachmentFound;
use Fisdap\Attachments\Queries\Events\AttachmentsFound;
use Fisdap\Entity\User;
use Fisdap\Logging\Events\EventLogging;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Events\Dispatcher;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Event subscriber ensuring current student UserContext ID matches Attachment userContextId
 *
 * @package Fisdap\Api\Attachments\Listeners
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class StudentUserContextIdMatchesAttachmentUserContextId
{
    use EventLogging;

    /**
     * @var User|null
     */
    private $user;
    
    
    /**
     * StudentUserContextIdMatchesAttachmentUserContextId constructor.
     *
     * @param AuthManager $auth
     */
    public function __construct(AuthManager $auth)
    {
        $this->user = $auth->guard()->user();
    }


    /**
     * @param Dispatcher $events
     *
     * @codeCoverageIgnore
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen([AttachmentFound::class], __CLASS__ . '@onAttachmentFound');
        $events->listen([AttachmentsFound::class], __CLASS__ . '@onAttachmentsFound');
    }


    /**
     * @param AttachmentFound $event
     */
    public function onAttachmentFound(AttachmentFound $event)
    {
        $this->matches($event->attachment);
    }


    /**
     * @param AttachmentsFound $event
     */
    public function onAttachmentsFound(AttachmentsFound $event)
    {
        foreach ($event->attachments as $attachment) {
            $this->matches($attachment);
        }
    }


    /**
     * @param array $attachment
     */
    private function matches($attachment)
    {
        if (is_null($this->user)) {
            return;
        }

        if ($this->user->context()->getRole()->getName() != 'student') {
            return;
        }

        if ($this->user->context()->getId() != $attachment['userContextId']) {
            throw new AccessDeniedHttpException('Student\'s UserContext ID does not match attachment userContextId');
        }

        $this->eventLogDebug('Attachment belongs to student');
    }
}
