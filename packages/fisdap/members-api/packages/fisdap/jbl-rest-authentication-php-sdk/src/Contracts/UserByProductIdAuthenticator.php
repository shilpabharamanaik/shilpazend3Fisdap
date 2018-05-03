<?php namespace Fisdap\JBL\Authentication\Contracts;

/**
 * Interface UserByProductIdAuthenticator
 *
 * Authenticate a user by product id interface
 *
 * @package Fisdap\JBL\Authentication\Contracts
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface UserByProductIdAuthenticator
{
    /**
     * Authenticate user by product id
     *
     * @param $userProductId
     * @return mixed
     */
    public function authenticateUserByProductId($userProductId);
}
