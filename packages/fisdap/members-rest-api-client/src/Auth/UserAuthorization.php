<?php namespace Fisdap\Api\Client\Auth;

/**
 * DTO to encapsulate user authorization data
 *
 * @package Fisdap\Api\Client\Auth
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class UserAuthorization
{
    /**
     * @var string Access token issued by the Identity Management System (IDMS) OAuth2 server
     */
    public $accessToken;

    /**
     * @var int User role (context) id
     */
    public $userRoleId;


    /**
     * @param string $accessToken
     * @param int    $userRoleId
     */
    public function __construct($accessToken, $userRoleId)
    {
        $this->accessToken = $accessToken;
        $this->userRoleId = $userRoleId;
    }
}
