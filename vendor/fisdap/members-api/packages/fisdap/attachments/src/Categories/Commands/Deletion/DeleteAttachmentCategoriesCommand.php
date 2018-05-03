<?php namespace Fisdap\Attachments\Categories\Commands\Deletion;

use Doctrine\Common\Inflector\Inflector;
use Fisdap\Attachments\Categories\Commands\AttachmentCategoryCommand;
use Fisdap\Attachments\Categories\Entity\AttachmentCategory;
use Fisdap\Attachments\Categories\Entity\AttachmentCategoryNotFound;
use Fisdap\Attachments\Categories\Repository\AttachmentCategoriesRepository;
use Fisdap\Attachments\Core\LogsAttachmentEvents;
use Fisdap\Attachments\Core\TypeMapper\MapsAttachmentTypes;

/**
 * Encapsulates data pertinent to deleting attachment categories
 *
 * @package Fisdap\Attachments\Categories\Commands\Deletion
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class DeleteAttachmentCategoriesCommand extends AttachmentCategoryCommand
{
    /**
     * @var string
     */
    public $attachmentType;

    /**
     * @var int[]|null
     */
    public $ids = null;

    /**
     * @var string[]|null
     */
    public $names = null;


    /**
     * @param string        $attachmentType
     * @param int[]|null    $ids
     * @param string[]|null $names
     *
     * @throws \Exception
     */
    public function __construct($attachmentType, array $ids = null, array $names = null)
    {
        $this->attachmentType = $attachmentType;
        $this->ids = $ids;
        $this->names = $names;

        if ($ids === null and $names == null) {
            throw new \Exception("Either 'ids' or 'names' must be set when deleting attachments");
        }
    }


    /**
     * @param MapsAttachmentTypes            $attachmentTypeMapper
     * @param AttachmentCategoriesRepository $repository
     * @param LogsAttachmentEvents           $logger
     *
     * @return int
     * @throws AttachmentCategoryNotFound
     * @throws \Fisdap\Attachments\Categories\Commands\Exceptions\MissingAttachmentCategoryEntityClass
     */
    public function handle(
        MapsAttachmentTypes $attachmentTypeMapper,
        AttachmentCategoriesRepository $repository,
        LogsAttachmentEvents $logger
    ) {
        $this->attachmentTypeMapper = $attachmentTypeMapper;
        $this->repository = $repository;
        $this->logger = $logger;

        $attachmentCategoryEntityClassName = $this->getAttachmentCategoryEntityClassName($this->attachmentType);

        $attachmentCategoryIds = [];

        if ($this->ids !== null) {
            $attachmentCategoryIds = $this->ids;
        } elseif ($this->names !== null) {
            /** @var AttachmentCategory $attachmentCategory */
            $attachmentCategories = $this->repository->getByNameAndType(
                $this->names,
                $attachmentCategoryEntityClassName
            );

            if (count($attachmentCategories) == 0) {
                throw new AttachmentCategoryNotFound(
                    "No $this->attachmentType attachment categories named "
                    . implode(', ', $this->names) . ' were found'
                );
            }

            foreach ($attachmentCategories as $attachmentCategory) {
                $attachmentCategoryIds[] = $attachmentCategory->getId();
            }
        }

        $deleteCount = $this->repository->destroyCollection($attachmentCategoryIds);

        $idsOrNames = $this->ids ?: $this->names;

        $categoryInflection = (count($idsOrNames) > 1) ? Inflector::pluralize('category') : 'category';

        $this->logger->info(
            "Deleted '$this->attachmentType' attachment {$categoryInflection}: " . implode(', ', $idsOrNames)
        );

        return $deleteCount;
    }
}
