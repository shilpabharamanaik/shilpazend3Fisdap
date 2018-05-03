<?php namespace Fisdap\Api\Users\Events;

use Fisdap\Api\Events\Event;
use Fisdap\Entity\PasswordReset;

/**
 * An event to fire when a password reset (PasswordReset Entity) was created
 *
 * @package Fisdap\Api\Users\Events
 * @author  Nick Karnick <nkarnick@fisdap.net>
 * @codeCoverageIgnore
 */
final class PasswordResetWasCreated extends Event
{
    /**
     * @var int
     */
    private $id;


    /**
     * PasswordResetWasCreated constructor.
     *
     * @param PasswordReset $passwordReset
     */
    public function __construct(PasswordReset $passwordReset)
    {
        $this->id = $passwordReset->getId();
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
