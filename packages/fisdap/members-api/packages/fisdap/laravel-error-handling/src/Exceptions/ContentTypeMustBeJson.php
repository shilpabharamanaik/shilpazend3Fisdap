<?php namespace Fisdap\ErrorHandling\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

/**
 * Exception to be thrown when the 'Content-Type' request header must be 'application/json'
 *
 * @package Fisdap\ErrorHandling\Exceptions
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ContentTypeMustBeJson extends UnsupportedMediaTypeHttpException
{
}
