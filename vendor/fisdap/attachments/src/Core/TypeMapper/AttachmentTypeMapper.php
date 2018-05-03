<?php namespace Fisdap\Attachments\Core\TypeMapper;

use Fisdap\Attachments\Core\ConfigProvider\ProvidesConfig;

/**
 * Maps attachment type strings to entity class names and vice-versa
 *
 * @package Fisdap\Attachments\Core
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class AttachmentTypeMapper implements MapsAttachmentTypes
{
    /**
     * @var ProvidesConfig
     */
    private $configProvider;


    /**
     * @param ProvidesConfig $configProvider
     */
    public function __construct(ProvidesConfig $configProvider)
    {
        $this->configProvider = $configProvider;
    }


    /**
     * @inheritdoc
     */
    public function getAttachmentEntityClassName($attachmentType)
    {
        return $this->configProvider->get('types.' . $attachmentType . '.entity');
    }


    /**
     * @inheritdoc
     */
    public function getAttachmentTypeFromEntityClassName($attachmentEntityClassName)
    {
        $attachmentTypes = $this->configProvider->get('types');

        return str_replace('.entity', '', array_search($attachmentEntityClassName, array_dot($attachmentTypes)));
    }


    /**
     * @inheritdoc
     */
    public function getAttachmentCategoryEntityClassName($attachmentType)
    {
        $attachmentCategoryEntityClassName = $this->getAttachmentEntityClassName($attachmentType) . 'Category';

        return class_exists($attachmentCategoryEntityClassName) ? $attachmentCategoryEntityClassName : null;
    }
}
