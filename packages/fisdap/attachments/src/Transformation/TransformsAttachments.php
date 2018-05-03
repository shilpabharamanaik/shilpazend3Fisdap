<?php namespace Fisdap\Attachments\Transformation;

use Fisdap\Attachments\Entity\Attachment;

/**
 * Contract for transforming attachment data for JSON output
 *
 * @package Fisdap\Attachments
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface TransformsAttachments
{
    /**
     * @param string $attachmentType
     *
     * @return $this
     */
    public function setAttachmentType($attachmentType);


    /**
     * @param Attachment|array $attachment
     *
     * @return array
     */
    public function transform($attachment);
}
