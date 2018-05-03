<?php namespace Fisdap\Attachments\Core\TypeMapper;

/**
 * Contract for mapping attachment type strings to entity class names and vice-versa
 *
 * @package Fisdap\Attachments\Core
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface MapsAttachmentTypes
{
    /**
     * @param string $attachmentType
     *
     * @return string|null
     */
    public function getAttachmentEntityClassName($attachmentType);


    /**
     * @param string $attachmentEntityClassName
     *
     * @return string
     */
    public function getAttachmentTypeFromEntityClassName($attachmentEntityClassName);


    /**
     * Determine AttachmentCategory entity name from Attachment entity name
     *
     * @param string $attachmentType
     *
     * @return string
     */
    public function getAttachmentCategoryEntityClassName($attachmentType);
}
