<?php namespace Fisdap\Members\Lti\Session;


/**
 * DTO encapsulating pertinent user account information for use when linking during an LTI launch
 *
 * @package Fisdap\Members\Lti\Session
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class FisdapUserIdentity
{
    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $fullName;
}