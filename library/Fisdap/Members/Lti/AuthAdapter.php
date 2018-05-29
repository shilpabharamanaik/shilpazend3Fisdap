<?php namespace Fisdap\Members\Lti;

use Zend\Authentication\Adapter\Exception\ExceptionInterface;
use Zend\Authentication\Result;


/**
 * Zend_Auth Adapter for LTI launches
 *
 * @package Fisdap\Members\Lti
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class AuthAdapter implements Zend\Authentication\Adapter\AdapterInterface
{
    /**
     * @var string
     */
    private $username;


    /**
     * AuthAdapter constructor.
     *
     * @param string $username
     */
    public function __construct($username)
    {
        $this->username = $username;
    }


    /**
     * Performs an authentication attempt
     *
     * @throws Zend_Auth_Adapter_Exception If authentication cannot be performed
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        return new Zend\Authentication\Result(Zend\Authentication\Result::SUCCESS, $this->username, []);
    }
}