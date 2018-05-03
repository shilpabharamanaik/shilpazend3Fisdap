<?php namespace Fisdap\Attachments\Processing;

use Fisdap\Attachments\Configuration\AttachmentVariationConfig;
use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;
use Fisdap\Attachments\Core\LogsAttachmentEvents;
use Fisdap\Attachments\Entity\Attachment;

/**
 * Template for AttachmentProcessor, providing common behavior
 *
 * @package Fisdap\Attachments\Processing
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
abstract class AttachmentProcessor implements ProcessesAttachments
{
    /**
     * @var AttachmentsKernel
     */
    protected $attachmentsKernel;

    /**
     * @var LogsAttachmentEvents
     */
    protected $logger;

    /**
     * @var string
     */
    protected $attachmentType;

    /**
     * @var array
     */
    protected $processedVariations = [];


    /**
     * @param AttachmentsKernel $attachmentsKernel
     * @param LogsAttachmentEvents       $logger
     */
    public function __construct(AttachmentsKernel $attachmentsKernel, LogsAttachmentEvents $logger)
    {
        $this->attachmentsKernel = $attachmentsKernel;
        $this->logger = $logger;
    }


    /**
     * @inheritdoc
     */
    public function setAttachmentType($attachmentType)
    {
        $this->attachmentType = $attachmentType;

        return $this;
    }


    /**
     * @param Attachment                $attachment
     * @param AttachmentVariationConfig $variationConfig
     *
     * @return string
     */
    protected function generateVariationFileName(Attachment $attachment, AttachmentVariationConfig $variationConfig)
    {
        return $attachment->getFileNameWithoutExtension()
            . '-' . $variationConfig->name . '.' . $attachment->getExtension();
    }


    /**
     * @param Attachment $attachment
     */
    protected function logCompletion(Attachment $attachment)
    {
        $this->logger->info(
            'Attachment processed',
            [
                'attachmentType'    => $this->attachmentType,
                'id'                => $attachment->getId(),
                'variations'        => $this->processedVariations
            ]
        );
    }
}
