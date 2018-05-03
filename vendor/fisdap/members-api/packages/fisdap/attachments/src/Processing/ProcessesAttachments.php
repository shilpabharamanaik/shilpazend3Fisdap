<?php namespace Fisdap\Attachments\Processing;

use Fisdap\Attachments\Configuration\AttachmentConfig;
use Fisdap\Attachments\Entity\Attachment;

/**
 * Contract for attachment processing
 *
 * @package Fisdap\Attachments\Processing
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface ProcessesAttachments
{
    /**
     * @param Attachment       $attachment
     * @param AttachmentConfig $attachmentConfig
     *
     * @return mixed
     */
    public function process(Attachment $attachment, AttachmentConfig $attachmentConfig);


    /**
     * @param string $attachmentType
     *
     * @return $this
     */
    public function setAttachmentType($attachmentType);
}
