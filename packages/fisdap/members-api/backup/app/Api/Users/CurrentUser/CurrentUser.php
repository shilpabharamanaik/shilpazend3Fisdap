<?php namespace Fisdap\Api\Users\CurrentUser;

use Fisdap\Entity\User;
use Fisdap\Entity\UserContext;

/**
 * Interface CurrentUser
 *
 * @package Fisdap\Api\Users\CurrentUser
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface CurrentUser
{
    /**
     * @return User|null
     */
    public function user();


    /**
     * @return User|null
     */
    public function getWritableUser();


    /**
     * Loads the current user from the database and re-saves to the session
     *
     * @return void
     */
    public function reload();


    /**
     * @param User $user
     *
     * @return void
     */
    public function setUser(User $user);


    /**
     * @return UserContext|null
     */
    public function context();


    /**
     * @param UserContext $userContext
     *
     * @return void
     */
    public function setContext(UserContext $userContext);


    /**
     * @param int $userContextId
     *
     * @return void
     */
    public function setContextFromId($userContextId);
}
