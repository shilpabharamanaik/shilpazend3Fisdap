<?php namespace Fisdap\Attachments\Categories\Queries;

use Fisdap\Attachments\Categories\Repository\AttachmentCategoriesRepository;
use Fisdap\Attachments\Core\TypeMapper\MapsAttachmentTypes;

/**
 * Service for retrieving one or more attachment categories by various criteria
 *
 * @package Fisdap\Attachments\Categories\Queries
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @todo    throw exceptions on not found
 */
final class AttachmentCategoriesFinder implements FindsAttachmentCategories
{
    /**
     * @var MapsAttachmentTypes
     */
    private $attachmentTypeMapper;

    /**
     * @var AttachmentCategoriesRepository
     */
    private $repository;


    /**
     * @param MapsAttachmentTypes           $attachmentTypeMapper
     * @param AttachmentCategoriesRepository $repository
     */
    public function __construct(MapsAttachmentTypes $attachmentTypeMapper, AttachmentCategoriesRepository $repository)
    {
        $this->attachmentTypeMapper = $attachmentTypeMapper;
        $this->repository = $repository;
    }


    /**
     * @inheritdoc
     */
    public function findById($id)
    {
        return $this->repository->getOneById($id);
    }


    /**
     * @inheritdoc
     */
    public function findAll($attachmentType)
    {
        $attachmentCategoryEntityClassName = $this->attachmentTypeMapper->getAttachmentCategoryEntityClassName(
            $attachmentType
        );

        return $this->repository->getAllByType($attachmentCategoryEntityClassName);
    }
}
