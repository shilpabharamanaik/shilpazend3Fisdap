<?php namespace Fisdap\Attachments\Configuration;

/**
 * Annotation for attachment configuration
 *
 * @package Fisdap\Attachments\Configuration
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *
 * @Annotation
 * @Target("CLASS")
 */
final class AttachmentConfig
{
    /**
     * @var string
     */
    public $associatedEntityRepositoryInterfaceName;

    /**
     * @var string
     */
    public $transformerClassName = null;

    /**
     * @var array
     */
    public $variationConfigurations = null;
}
