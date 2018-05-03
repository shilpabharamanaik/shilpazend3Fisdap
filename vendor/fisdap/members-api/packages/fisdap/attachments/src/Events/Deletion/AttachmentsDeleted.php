<?php namespace Fisdap\Attachments\Events\Deletion;

/**
 * Template for attachment deletion events
 *
 * @package Fisdap\Attachments\Commands\Deletion\Events
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
abstract class AttachmentsDeleted
{
    /**
     * @var array Associative array, keyed by attachment ID, containing attachmentType and associatedEntityId
     */
    public $deletedAttachmentEntityData;


    /**
     * @param array $deletedAttachmentEntityData
     */
    public function __construct(array $deletedAttachmentEntityData)
    {
        $this->deletedAttachmentEntityData = $deletedAttachmentEntityData;
    }
}
