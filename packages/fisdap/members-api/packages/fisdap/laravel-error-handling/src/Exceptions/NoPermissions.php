<?php namespace Fisdap\ErrorHandling\Exceptions;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;


/**
 * Exception to be thrown when a user does not have permission to initiate a command/activity
 *
 * @package Fisdap\ErrorHandling\Exceptions
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class NoPermissions extends AccessDeniedHttpException
{
}