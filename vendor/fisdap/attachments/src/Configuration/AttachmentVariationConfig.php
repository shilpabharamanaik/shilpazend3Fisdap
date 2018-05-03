<?php namespace Fisdap\Attachments\Configuration;

/**
 * Annotation for attachment variation configuration
 *
 * @package Fisdap\Attachments\Configuration
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *
 * @Annotation
 * @Target("ANNOTATION")
 */
class AttachmentVariationConfig
{
    /**
     * @var string
     */
    public $mimeMedia;

    /**
     * @var string
     */
    public $name;
}
