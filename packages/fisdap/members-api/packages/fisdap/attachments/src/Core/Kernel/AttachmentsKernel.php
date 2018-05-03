<?php namespace Fisdap\Attachments\Core\Kernel;

use Doctrine\Common\Annotations\Reader;
use Fisdap\Attachments\Configuration\AttachmentConfig;
use Fisdap\Attachments\Core\ConfigProvider\ProvidesConfig;

/**
 * Contract for the core service for providing attachments functionality
 *
 * @package Fisdap\Attachments\Core\Kernel
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface AttachmentsKernel
{
    /**
     * @return \Illuminate\Contracts\Filesystem\Factory
     */
    public function getFilesystem();


    /**
     * @return ProvidesConfig
     * @throws \Exception
     */
    public function getConfigProvider();


    /**
     * @param $attachmentType
     *
     * @return null|AttachmentConfig
     * @throws \Exception
     */
    public function getAttachmentConfig($attachmentType);


    /**
     * @param Reader $annotationReader
     */
    public function setAnnotationReader($annotationReader);


    /**
     * @return mixed
     */
    public function getMimeTypesBlacklist();


    /**
     * @param string $savePath
     *
     * @return string
     */
    public function generateTempSavePath($savePath);


    /**
     * @param string $savePath
     *
     * @return string
     */
    public function generateTempPublicUrlRoot($savePath);


    /**
     * @param string|null $attachmentType
     *
     * @return string
     */
    public function getFilesystemDiskName($attachmentType = null);


    /**
     * @return string
     */
    public function getFilesystemTempDiskName();


    /**
     * @param string|null $attachmentType
     *
     * @return string
     */
    public function getCdnName($attachmentType = null);


    /**
     * @param string $cdnName
     *
     * @return string
     */
    public function getCdnUrlRoot($cdnName);
}
