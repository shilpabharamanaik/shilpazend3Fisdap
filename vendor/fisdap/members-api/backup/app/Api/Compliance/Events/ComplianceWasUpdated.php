<?php namespace Fisdap\Api\Compliance\Events;

use Fisdap\Api\Events\Event;
use Fisdap\Entity\UserContext;


/**
 * Event to fire when (requirement) compliance was updated for one or more UserContexts
 *
 * @package Fisdap\Api\Compliance\Events
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class ComplianceWasUpdated extends Event
{
    /**
     * @var int[]
     */
    private $userContextIds;


    /**
     * ComplianceWasUpdated constructor.
     *
     * @param UserContext[] $userContexts
     */
    public function __construct(array $userContexts)
    {
        foreach ($userContexts as $userContext) {
            $this->userContextIds[] = $userContext->getId();
        }
    }


    /**
     * @return int[]
     */
    public function getUserContextIds()
    {
        return $this->userContextIds;
    }
}