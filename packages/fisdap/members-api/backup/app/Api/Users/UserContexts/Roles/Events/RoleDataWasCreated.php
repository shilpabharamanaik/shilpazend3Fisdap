<?php namespace Fisdap\Api\Users\UserContexts\Roles\Events;

use Fisdap\Api\Events\Event;
use Fisdap\Entity\RoleData;

/**
 * An event to fire when a role data (RoleData) entity has been created
 *
 * @package Fisdap\Api\Users\UserContexts\Roles\Events
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class RoleDataWasCreated extends Event
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $className;


    /**
     * RoleDataWasCreated constructor.
     *
     * @param RoleData $roleData
     */
    public function __construct(RoleData $roleData)
    {
        $this->id = $roleData->getId();
        $this->className = get_class($roleData);
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
    public function getClassName()
    {
        return $this->className;
    }
}
