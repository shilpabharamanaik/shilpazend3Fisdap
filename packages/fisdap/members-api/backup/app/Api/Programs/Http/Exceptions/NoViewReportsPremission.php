<?php namespace Fisdap\Api\Programs\Http\Exceptions;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;


/**
 * Class NoViewReportsPermission
 *
 * @package Fisdap\Api\Programs\Http\Exceptions
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class NoViewReportsPermission extends AccessDeniedHttpException
{
}