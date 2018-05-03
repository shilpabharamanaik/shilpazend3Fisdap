<?php namespace Fisdap\Api\Compliance\Jobs;

use Fisdap\Api\Compliance\Events\ComplianceWasUpdated;
use Fisdap\Api\Jobs\Job;
use Fisdap\Data\Requirement\RequirementRepository;
use Fisdap\Data\Slot\SlotAssignmentRepository;
use Fisdap\Entity\UserContext;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;


/**
 * Class UpdateCompliance
 *
 * @package Fisdap\Api\Compliance\Jobs
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class UpdateCompliance extends Job
{
    /**
     * @var UserContext[]
     */
    private $userContexts;


    /**
     * UpdateCompliance constructor.
     *
     * @param UserContext[] $userContexts
     */
    public function __construct(array $userContexts)
    {
        $this->userContexts = $userContexts;
    }


    /**
     * @param SlotAssignmentRepository $slotAssignmentRepository
     * @param RequirementRepository    $requirementRepository
     * @param EventDispatcher          $eventDispatcher
     */
    public function handle(
        SlotAssignmentRepository $slotAssignmentRepository,
        RequirementRepository $requirementRepository,
        EventDispatcher $eventDispatcher
    ) {
        foreach ($this->userContexts as $userContext) {
            $sites = [];

            // Get the user's slot assignments from today and onward
            $assignments = $slotAssignmentRepository->getUserContextAssignmentsByDate(
                $userContext->getId(),
                new \DateTime()
            );

            // Loop over slot assignments to get all sites they're going to
            foreach ($assignments as $assignment) {
                $sites[$assignment->slot->event->site->id][] = $assignment;
            }

            foreach ($sites as $siteId => $siteAssignments) {
                $siteCompliant = $requirementRepository->isProgramSiteCompliant($userContext->getId(), $siteId,
                    $userContext->getProgram()->getId());
                $globalSite = $userContext->getProgram()->sharesSite($siteId);
                $globalSiteCompliant = ($globalSite) ? $requirementRepository->isGlobalSiteCompliant($userContext->getId(),
                    $siteId) : 1;

                foreach ($siteAssignments as $assignment) {
                    $assignment->compliant = $siteCompliant;
                    $assignment->global_site_compliant = $globalSiteCompliant;
                }
            }
        }

        $requirementRepository->updateCollection([]);

        $eventDispatcher->fire(new ComplianceWasUpdated($this->userContexts));
    }
}