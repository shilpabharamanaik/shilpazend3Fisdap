<?php namespace Fisdap\Attachments\Core\Kernel;

use Doctrine\Common\Annotations\Reader;
use Fisdap\Attachments\Configuration\AttachmentConfig;
use Fisdap\Attachments\Core\ConfigProvider\ProvidesConfig;
use Fisdap\Attachments\Core\MissingAttachmentConfiguration;
use Fisdap\Attachments\Core\TypeMapper\MapsAttachmentTypes;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Filesystem\Factory as Filesystem;

/**
 * Core service for providing attachments functionality
 *
 * Enables cross-framework compatibility, configuration, access to filesystems,
 * and common string manipulation for various file paths and URLs.
 *
 * @package Fisdap\Attachments\Core\Kernel
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class DefaultAttachmentsKernel implements AttachmentsKernel
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var ProvidesConfig
     */
    private $configProvider;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var MapsAttachmentTypes
     */
    private $attachmentTypeMapper;


    /**
     * @param Container            $container
     * @param ProvidesConfig       $configProvider
     * @param Filesystem           $filesystem
     * @param MapsAttachmentTypes  $attachmentTypeMapper
     */
    public function __construct(
        Container $container,
        ProvidesConfig $configProvider,
        Filesystem $filesystem,
        MapsAttachmentTypes $attachmentTypeMapper
    ) {
        $this->container = $container;
        $this->configProvider = $configProvider;
        $this->filesystem = $filesystem;
        $this->attachmentTypeMapper = $attachmentTypeMapper;
    }


    /**
     * @inheritdoc
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }


    /**
     * @inheritdoc
     */
    public function getConfigProvider()
    {
        return $this->configProvider;
    }


    /**
     * @inheritdoc
     * @todo support config via php array?
     */
    public function getAttachmentConfig($attachmentType)
    {
        $attachmentEntityClassName = $this->attachmentTypeMapper->getAttachmentEntityClassName($attachmentType);

        $attachmentConfig = null;

        if (isset($this->annotationReader) && isset($attachmentEntityClassName)) {
            $reflectionClass = new \ReflectionClass($attachmentEntityClassName);
            $attachmentConfig = $this->annotationReader->getClassAnnotation(
                $reflectionClass,
                AttachmentConfig::class
            );
        }

        if ($attachmentConfig === null) {
            throw new MissingAttachmentConfiguration("No configuration was found for '$attachmentType' attachments'");
        }

        return $attachmentConfig;
    }


    /**
     * @inheritdoc
     */
    public function setAnnotationReader($annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }


    /**
     * @inheritdoc
     */
    public function getMimeTypesBlacklist()
    {
        return $this->configProvider->get('mime_types_blacklist', []);
    }


    /*
     * Filesystem
     */

    /**
     * @inheritdoc
     */
    public function generateTempSavePath($savePath)
    {
        return $this->configProvider->get('public_path') . $this->generateTempPublicUrlRoot($savePath);
    }


    /**
     * @inheritdoc
     */
    public function generateTempPublicUrlRoot($savePath)
    {
        return '/' . $this->configProvider->get('temp_public_relative_path') . '/' . $savePath;
    }


    /**
     * @inheritdoc
     */
    public function getFilesystemDiskName($attachmentType = null)
    {
        $defaultDisk = $this->configProvider->get('filesystem_disks.default');

        if ($attachmentType === null) {
            return $defaultDisk;
        } else {
            return $this->configProvider->get("filesystem_disks.$attachmentType") ?: $defaultDisk;
        }
    }


    /**
     * @inheritdoc
     */
    public function getFilesystemTempDiskName()
    {
        return $this->configProvider->get('filesystem_disks.temp');
    }



    /*
     * CDN
     */

    /**
     * @inheritdoc
     */
    public function getCdnName($attachmentType = null)
    {
        $defaultName = $this->configProvider->get('cdn.default');

        if ($attachmentType === null) {
            return $defaultName;
        } else {
            return $this->configProvider->get("cdn.$attachmentType") ?: $defaultName;
        }
    }


    /**
     * @inheritdoc
     */
    public function getCdnUrlRoot($cdnName)
    {
        return $this->configProvider->get("cdn.$cdnName.url_root");
    }
}
