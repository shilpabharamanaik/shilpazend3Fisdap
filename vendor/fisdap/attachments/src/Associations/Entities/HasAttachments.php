<?php namespace Fisdap\Attachments\Associations\Entities;

use Fisdap\Attachments\Entity\Attachment;

/**
 * Contract for entities associated with (having) attachments
 *
 * @package Fisdap\Attachments\Associations\Entities
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface HasAttachments
{
    /**
     * @return mixed
     */
    public function getId();


    /**
     * @return mixed
     */
    public function getAttachments();


    /**
     * @param string $attachmentId
     *
     * @return mixed
     */
    public function getAttachmentById($attachmentId);


    /**
     * @param Attachment $attachment
     */
    public function addAttachment(Attachment $attachment);


    /**
     * @param Attachment $attachment
     */
    public function removeAttachment(Attachment $attachment);
}
