<?php namespace Fisdap\Api\Users\UserContexts\Http\Exceptions;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class InadequateRole
 *
 * @package Fisdap\Api\Users\UserContexts\Http\Exceptions
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class InadequateRole extends AccessDeniedHttpException
{
}
