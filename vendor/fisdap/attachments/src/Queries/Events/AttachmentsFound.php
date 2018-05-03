<?php namespace Fisdap\Attachments\Queries\Events;

/**
 * Event to be dispatched when multiple attachments were found
 *
 * @package Fisdap\Attachments\Queries\Events
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class AttachmentsFound
{
    /**
     * @var array An array of Attachments as arrays
     */
    public $attachments = [];


    /**
     * @param array $attachments
     */
    public function __construct(array $attachments)
    {
        $this->attachments = $attachments;
    }
}
