<?php namespace Fisdap\Api\Shifts\Attachments\Http\Exceptions;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;


/**
 * Class ShiftAttachmentLimitReached
 *
 * @package Fisdap\Api\Shifts\Attachments\Http\Exceptions
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ShiftAttachmentLimitReached extends AccessDeniedHttpException
{
}