<?php namespace Fisdap\Attachments\Events;

use Fisdap\Attachments\Entity\Attachment;

/**
 * Template for events that occurred on an Attachment
 *
 * @package Fisdap\Attachments\Events
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
abstract class AttachmentEvent
{
    /**
     * @var array An array representation of an Attachment
     */
    public $attachment;


    /**
     * @param Attachment|array $attachment
     */
    public function __construct($attachment)
    {
        $this->attachment = $attachment instanceof Attachment ? $attachment->toArray() : $attachment;
    }
}
