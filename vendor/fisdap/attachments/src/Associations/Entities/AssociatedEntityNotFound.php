<?php namespace Fisdap\Attachments\Associations\Entities;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Exception to be thrown when an attachment's associated entity is not found
 *
 * @package Fisdap\Attachments\Associations\Entities
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class AssociatedEntityNotFound extends NotFoundHttpException
{
}
