<?php namespace Fisdap\Attachments\Processing;

use Hoa\Mime\Mime;
use Illuminate\Contracts\Container\Container;

/**
 * Creates an AttachmentProcessor according to specified MIME type, resolving appropriate class from service container
 *
 * @package Fisdap\Attachments\Processing
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class AttachmentProcessorFactory
{
    /**
     * @var Container
     */
    protected $container;


    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }


    /**
     * @param string $mimeType
     *
     * @return AttachmentProcessor|null
     * @throws \Hoa\Mime\Exception
     */
    public function create($mimeType)
    {
        $mimeParts = Mime::parseMime($mimeType);
        $mimeMedia = $mimeParts[0];

        switch ($mimeMedia) {
            case 'image':
                return $this->container->make(ImageAttachmentProcessor::class);
                break;
            case 'example':
                return $this->container->make(TestAttachmentProcessor::class);
                break;
            default:
                return null;
                break;
        }
    }
}
