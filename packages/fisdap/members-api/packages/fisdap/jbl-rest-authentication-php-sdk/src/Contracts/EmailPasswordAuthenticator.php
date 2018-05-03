<?php namespace Fisdap\JBL\Authentication\Contracts;

/**
 * Interface EmailPasswordAuthenticator
 *
 * Authenticate a user with email and password
 *
 * @package Fisdap\JBL\Authentication\Contracts
 * @author Jason Michels <jmichels@fisdap.net>
 * @version $Id$
 */
interface EmailPasswordAuthenticator
{
    /**
     * Authenticate a user with JBL by using email and password
     *
     * @param string $email
     * @param string $password
     * @return mixed
     */
    public function authenticateWithEmailPassword($email, $password);
}
