<?php namespace Fisdap\Api\Programs\Http\Exceptions;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class ProgramIdMismatch
 *
 * @package Fisdap\Api\Programs\Http\Exceptions
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ProgramIdMismatch extends AccessDeniedHttpException
{
}
