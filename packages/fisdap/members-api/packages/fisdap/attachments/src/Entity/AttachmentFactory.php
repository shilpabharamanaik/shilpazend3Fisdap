<?php namespace Fisdap\Attachments\Entity;

use Fisdap\Attachments\Associations\Entities\HasAttachments;
use Fisdap\Attachments\Categories\Entity\AttachmentCategoryNotFound;
use Fisdap\Attachments\Categories\Repository\AttachmentCategoriesRepository;
use Fisdap\Attachments\Core\TypeMapper\MapsAttachmentTypes;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Creates attachments with any required associations
 *
 * @package Fisdap\Attachments\Entity
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class AttachmentFactory
{
    /**
     * @var MapsAttachmentTypes
     */
    private $attachmentTypeMapper;

    /**
     * @var AttachmentCategoriesRepository
     */
    private $attachmentCategoriesRepository;


    /**
     * @param MapsAttachmentTypes            $attachmentTypeMapper
     * @param AttachmentCategoriesRepository $attachmentCategoriesRepository
     */
    public function __construct(
        MapsAttachmentTypes $attachmentTypeMapper,
        AttachmentCategoriesRepository $attachmentCategoriesRepository
    ) {
        $this->attachmentTypeMapper = $attachmentTypeMapper;
        $this->attachmentCategoriesRepository = $attachmentCategoriesRepository;
    }


    /**
     * @param string         $attachmentType
     * @param int            $userContextId
     * @param HasAttachments $associatedEntity
     * @param string         $id
     * @param File           $tempFile
     * @param string         $savePath
     * @param string         $nickname
     * @param string         $notes
     * @param array|null     $categories
     *
     * @return Attachment
     * @throws AttachmentCategoryNotFound
     */
    public function create(
        $attachmentType,
        $userContextId,
        HasAttachments $associatedEntity,
        $id,
        File $tempFile,
        $savePath,
        $nickname = null,
        $notes = null,
        array $categories = null
    ) {
        /** @var Attachment $attachmentEntityClassName */
        $attachmentEntityClassName = $this->attachmentTypeMapper->getAttachmentEntityClassName($attachmentType);

        $attachmentCategoryEntityClassName = $this->attachmentTypeMapper->getAttachmentCategoryEntityClassName(
            $attachmentType
        );


        $attachment = $attachmentEntityClassName::createFromFile(
            $userContextId,
            $associatedEntity,
            $tempFile,
            $savePath,
            $id,
            $nickname,
            $notes
        );

        // add categories
        if (isset($categories)) {
            $this->addCategories($categories, $attachmentCategoryEntityClassName, $attachment);
        }

        return $attachment;
    }


    /**
     * @param array      $categories
     * @param string     $attachmentCategoryEntityClassName
     * @param Attachment $attachment
     *
     * @throws AttachmentCategoryNotFound
     */
    public function addCategories(array $categories, $attachmentCategoryEntityClassName, $attachment)
    {
        foreach ($categories as $categoryName) {
            $category = $this->attachmentCategoriesRepository->getOneByNameAndType(
                $categoryName,
                $attachmentCategoryEntityClassName
            );

            if ($category !== null) {
                $attachment->addCategory($category);
            } else {
                throw new AttachmentCategoryNotFound("No attachment category named '$categoryName' was found.");
            }
        }
    }
}
