<?php namespace Fisdap\Api\Compliance\Events;

use Fisdap\Api\Events\Event;
use Fisdap\Entity\RequirementAttachment;


/**
 * Event to fire when requirements were "auto-attached" to a UserContext
 *
 * @package Fisdap\Api\Compliance\Events
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class RequirementsWereAutoAttached extends Event
{
    /**
     * @var int[]
     */
    private $requirementAttachmentIds;


    /**
     * RequirementsWereAutoAttached constructor.
     *
     * @param RequirementAttachment[] $requirementAttachments
     */
    public function __construct(array $requirementAttachments)
    {
        foreach ($requirementAttachments as $requirementAttachment) {
            $this->requirementAttachmentIds[] = $requirementAttachment->getId();
        }
    }


    /**
     * @return int[]
     */
    public function getRequirementAttachmentIds()
    {
        return $this->requirementAttachmentIds;
    }
}