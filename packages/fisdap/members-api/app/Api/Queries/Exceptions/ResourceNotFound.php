<?php namespace Fisdap\Api\Queries\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * Exception to be thrown when a requested resource (Entity) is not found or a collection of resources is empty
 *
 * @package Fisdap\Api\Queries\Exceptions
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ResourceNotFound extends NotFoundHttpException
{
}