<?php namespace Fisdap\Api\Users\UserContexts\Roles\Instructors\Http\Exceptions;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class InvalidPermission
 *
 * @package Fisdap\Api\Users\UserContexts\Roles\Instructors\Http\Exceptions
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class InvalidPermission extends AccessDeniedHttpException
{
}
