<?php namespace Fisdap\Api\Users\Http\Exceptions;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;


/**
 * Class UserIdMismatch
 *
 * @package Fisdap\Api\Users\Http\Exceptions
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class UserIdMismatch extends AccessDeniedHttpException
{
}