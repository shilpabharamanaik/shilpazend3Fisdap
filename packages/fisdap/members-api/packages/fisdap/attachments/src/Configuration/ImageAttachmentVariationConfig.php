<?php namespace Fisdap\Attachments\Configuration;

/**
 * Annotation for image attachment variation configuration
 *
 * @package Fisdap\Attachments\Configuration
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *
 * @Annotation
 * @Target("ANNOTATION")
 */
class ImageAttachmentVariationConfig extends AttachmentVariationConfig
{
    /**
     * @var string
     */
    public $mimeMedia = 'image';

    /**
     * @var string
     */
    public $imageProcessorFilterClassName;

    /**
     * @var array
     */
    public $imageProcessorFilterConstructorArguments = [];
}
