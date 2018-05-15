<?php namespace Fisdap\Api\Users\Events;

use Fisdap\Api\Events\Event;
use Fisdap\Entity\User;

/**
 * An event to fire when a user (User Entity) was created
 *
 * @package Fisdap\Api\Users\Events
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class UserWasCreated extends Event
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $email;


    /**
     * UserWasCreated constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->id = $user->getId();
        $this->username = $user->getUsername();
        $this->email = $user->getEmail();
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
    public function getUsername()
    {
        return $this->username;
    }


    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
}
