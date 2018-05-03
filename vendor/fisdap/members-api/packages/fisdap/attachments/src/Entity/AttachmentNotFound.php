<?php namespace Fisdap\Attachments\Entity;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Exception to be thrown when an attachment was unable to be found
 *
 * @package Fisdap\Attachments\Entity
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class AttachmentNotFound extends NotFoundHttpException
{
}
