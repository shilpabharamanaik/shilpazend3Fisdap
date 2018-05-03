<?php namespace Fisdap\Members\Lti;

use Zend_Auth_Adapter_Exception;
use Zend_Auth_Result;


/**
 * Zend_Auth Adapter for LTI launches
 *
 * @package Fisdap\Members\Lti
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class AuthAdapter implements \Zend_Auth_Adapter_Interface
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
        return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $this->username, []);
    }
}