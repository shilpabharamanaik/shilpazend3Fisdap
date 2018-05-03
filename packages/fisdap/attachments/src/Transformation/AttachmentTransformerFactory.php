<?php namespace Fisdap\Attachments\Transformation;

use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;
use Illuminate\Contracts\Container\Container;

/**
 * Creates attachment transformers
 *
 * @package Fisdap\Attachments\Transformation
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class AttachmentTransformerFactory
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var AttachmentsKernel
     */
    private $attachmentsKernel;


    /**
     * @param Container         $container
     * @param AttachmentsKernel $attachmentsKernel
     */
    public function __construct(Container $container, AttachmentsKernel $attachmentsKernel)
    {
        $this->container = $container;
        $this->attachmentsKernel = $attachmentsKernel;
    }


    /**
     * @param string $attachmentType
     *
     * @return TransformsAttachments
     */
    public function create($attachmentType)
    {
        $attachmentConfig = $this->attachmentsKernel->getAttachmentConfig($attachmentType);

        if (isset($attachmentConfig->transformerClassName)) {
            return $this->container->make($attachmentConfig->transformerClassName);
        }

        return $this->container->make(TransformsAttachments::class);
    }
}
