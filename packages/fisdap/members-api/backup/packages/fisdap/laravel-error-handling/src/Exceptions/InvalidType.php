<?php namespace Fisdap\ErrorHandling\Exceptions;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Exception to be thrown when an unexpected data type has been detected
 *
 * @package Fisdap\ErrorHandling\Exceptions
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class InvalidType extends BadRequestHttpException
{
}
