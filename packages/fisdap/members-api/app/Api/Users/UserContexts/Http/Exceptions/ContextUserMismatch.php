<?php namespace Fisdap\Api\Users\UserContexts\Http\Exceptions;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class ContextUserMismatch
 *
 * @package Fisdap\Api\Users\UserContexts\Http\Exceptions
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class ContextUserMismatch extends AccessDeniedHttpException
{
}
