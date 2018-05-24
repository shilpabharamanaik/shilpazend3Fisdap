<?php namespace Fisdap\Api\Shifts\Http\Exceptions;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;


/**
 * Class NoEditPermission
 *
 * @package Fisdap\Api\Shifts\Http\Exceptions
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class NoEditPermission extends AccessDeniedHttpException
{
}