<?php namespace Fisdap\ErrorHandling\Exceptions;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


/**
 * Class PostMaxSizeExceeded
 *
 * @package Fisdap\ErrorHandling\Exceptions
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class PostMaxSizeExceeded extends BadRequestHttpException
{
}