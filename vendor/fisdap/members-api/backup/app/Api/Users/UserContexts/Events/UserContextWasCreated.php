<?php namespace Fisdap\Api\Users\UserContexts\Events;

use Fisdap\Api\Events\Event;
use Fisdap\Entity\UserContext;


/**
 * An event to fire when a user context (UserContext Entity) was created
 *
 * @package Fisdap\Api\Users\UserContexts\Events
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class UserContextWasCreated extends Event
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $roleName;

    /**
     * @var int
     */
    private $programId;

    /**
     * @var string
     */
    private $email;


    /**
     * UserContextWasCreated constructor.
     *
     * @param UserContext $userContext
     */
    public function __construct(UserContext $userContext)
    {
        $this->id = $userContext->getId();
        $this->roleName = $userContext->getRole()->getName();
        $this->programId = $userContext->getProgram()->getId();
        $this->email = $userContext->getEmail();
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return string
     */
    public function getRoleName()
    {
        return $this->roleName;
    }


    /**
     * @return int
     */
    public function getProgramId()
    {
        return $this->programId;
    }


    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
}