<?php namespace Fisdap\Attachments\Categories\Commands;

use Fisdap\Attachments\Categories\Commands\Exceptions\MissingAttachmentCategoryEntityClass;
use Fisdap\Attachments\Categories\Repository\AttachmentCategoriesRepository;
use Fisdap\Attachments\Core\LogsAttachmentEvents;
use Fisdap\Attachments\Core\TypeMapper\MapsAttachmentTypes;

/**
 * Template for attachment category commands
 *
 * @package Fisdap\Attachments\Categories\Commands
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
abstract class AttachmentCategoryCommand
{
    /**
     * @var MapsAttachmentTypes
     */
    protected $attachmentTypeMapper;

    /**
     * @var AttachmentCategoriesRepository
     */
    protected $repository;

    /**
     * @var LogsAttachmentEvents
     */
    protected $logger;
    


    /**
     * @param $attachmentType
     *
     * @return string
     * @throws MissingAttachmentCategoryEntityClass
     */
    protected function getAttachmentCategoryEntityClassName($attachmentType)
    {
        $attachmentCategoryEntityClassName = $this->attachmentTypeMapper->getAttachmentCategoryEntityClassName(
            $attachmentType
        );

        if ($attachmentCategoryEntityClassName === null) {
            throw new MissingAttachmentCategoryEntityClass(
                "No AttachmentCategory class was found for the '$attachmentType' attachment type"
            );
        }

        return $attachmentCategoryEntityClassName;
    }
}
