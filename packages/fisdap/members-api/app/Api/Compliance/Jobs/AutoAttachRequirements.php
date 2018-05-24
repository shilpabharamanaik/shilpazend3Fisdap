<?php namespace Fisdap\Api\Compliance\Jobs;

use Fisdap\Api\Compliance\Events\RequirementsWereAutoAttached;
use Fisdap\Api\Jobs\Job;
use Fisdap\Data\Requirement\RequirementAutoAttachmentRepository;
use Fisdap\Entity\Requirement;
use Fisdap\Entity\RequirementAttachment;
use Fisdap\Entity\UserContext;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;


/**
 * Assigns "Auto-Attach" (a.k.a. Default) Requirements to a UserContext
 *
 * @package Fisdap\Api\Compliance\Jobs
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class AutoAttachRequirements extends Job
{
    /**
     * @var UserContext
     */
    private $userContext;


    /**
     * AutoAttachRequirements constructor.
     *
     * @param UserContext $userContext
     */
    public function __construct(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }


    /**
     * @param RequirementAutoAttachmentRepository $requirementAutoAttachmentRepository
     * @param EventDispatcher                     $eventDispatcher
     */
    public function handle(
        RequirementAutoAttachmentRepository $requirementAutoAttachmentRepository,
        EventDispatcher $eventDispatcher
    ) {
        $autoAttachmentRequirements = $requirementAutoAttachmentRepository->findBy([
            'role'                => $this->userContext->getRole(),
            'program'             => $this->userContext->getProgram(),
            'certification_level' => $this->userContext->getCertificationLevel()
        ]);

        $requirementAttachments = [];

        foreach ($autoAttachmentRequirements as $autoAttachmentRequirement) {
            $requirementAttachments[] = $this->assignRequirement(
                $this->userContext, $autoAttachmentRequirement->requirement, null, 0, null, "new account created"
            );
        }

        $requirementAutoAttachmentRepository->storeCollection($requirementAttachments);

        $eventDispatcher->fire(new RequirementsWereAutoAttached($requirementAttachments));
    }


    /**
     * @param UserContext $userContext
     * @param Requirement $req
     * @param null        $expirationDate
     * @param int         $completed
     * @param null        $dueDate
     * @param null        $notes
     * @param null        $assigner_userContextId
     * @param bool        $sendNotification
     *
     * @return bool|RequirementAttachment
     */
    private function assignRequirement(
        UserContext $userContext,
        Requirement $req,
        $expirationDate = null,
        $completed = 0,
        $dueDate = null,
        $notes = null,
        $assigner_userContextId = null,
        $sendNotification = false
    ) {
        if ($userContext->hasRequirement($req)) {
            return false;
        }

        $requirementAttachment = new RequirementAttachment;
        $requirementAttachment->requirement = $req;
        $requirementAttachment->user_context = $userContext;
        $requirementAttachment->set_completed($completed, $assigner_userContextId);

        if ($dueDate) {
            $requirementAttachment->set_due_date($dueDate, $assigner_userContextId);
        }

        if ($expirationDate) {
            $requirementAttachment->set_expiration_date($expirationDate, $assigner_userContextId);
        }

        // todo
//        $requirementAttachment->recordHistory(4, $notes, $assigner_userContextId);

        //todo - refactor to use e-mail
//        $usersToNotify = [];
//        if ($sendNotification == true) {
//            $usersToNotify[$userContext->getId()][] = [
//                "name"            => $userContext->getUser()->getName(),
//                "email"           => $userContext->getUser()->getEmail(),
//                "requirementName" => $requirementAttachment->requirement->name,
//                "status"          => "assigned",
//                "due_date"        => $requirementAttachment->due_date->format("M j, Y")
//            ];
//
//            RequirementNotification::sendNotifications($usersToNotify, "requirement-assigned-notification.phtml");
//        }

        return $requirementAttachment;
    }
}