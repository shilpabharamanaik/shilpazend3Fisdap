<?php namespace Fisdap\JBL\Authentication\Contracts;

/**
 * Interface UserByIdAuthenticator
 *
 * Authenticate a user by id
 *
 * @package Fisdap\JBL\Authentication\Contracts
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface UserByIdAuthenticator
{
    /**
     * Authenticate a JBL user by ID
     *
     * @param string $emailOrJblUserId
     * @return mixed
     */
    public function authenticateUserById($emailOrJblUserId);
}
