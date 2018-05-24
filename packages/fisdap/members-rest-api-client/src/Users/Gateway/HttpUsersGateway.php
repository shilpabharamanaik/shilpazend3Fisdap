<?php namespace Fisdap\Api\Client\Users\Gateway;

use Fisdap\Api\Client\Gateway\CommonHttpGateway;
use Fisdap\Api\Client\Gateway\GetOneById;
use Fisdap\Api\Client\Gateway\RetrievesById;


/**
 * HTTP implementation of a UsersGateway
 *
 * @package Fisdap\Api\Client\Users\Gateway
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class HttpUsersGateway extends CommonHttpGateway implements UsersGateway, RetrievesById
{
    use GetOneById;


    protected static $uriRoot = '/users';
}