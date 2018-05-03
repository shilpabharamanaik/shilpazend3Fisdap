<?php namespace Fisdap\Attachments\Processing;

use Fisdap\Attachments\Configuration\AttachmentConfig;
use Fisdap\Attachments\Entity\Attachment;

/**
 * Fake attachment processor for use in testing
 *
 * @package Fisdap\Attachments\Processing
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class TestAttachmentProcessor extends AttachmentProcessor
{
    /**
     * @inheritdoc
     */
    public function process(Attachment $attachment, AttachmentConfig $attachmentConfig)
    {
        return null;
    }
}
