<?php namespace Fisdap\Attachments\Categories\Commands\Modification;

use Fisdap\Attachments\Categories\Commands\AttachmentCategoryCommand;
use Fisdap\Attachments\Categories\Entity\AttachmentCategoryNotFound;
use Fisdap\Attachments\Categories\Repository\AttachmentCategoriesRepository;
use Fisdap\Attachments\Core\LogsAttachmentEvents;
use Fisdap\Attachments\Core\TypeMapper\MapsAttachmentTypes;

/**
 * Encapsulates data pertinent to renaming attachment categories
 *
 * @package Fisdap\Attachments\Categories\Commands\Modification
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class RenameAttachmentCategoryCommand extends AttachmentCategoryCommand
{
    /**
     * @var string
     */
    public $newName;

    /**
     * @var string|null
     */
    public $oldName;

    /**
     * @var int | null
     */
    public $id;

    /**
     * @var string|null
     */
    public $attachmentType;


    /**
     * @param string      $newName
     * @param string|null $oldName
     * @param int|null    $id
     * @param string|null $attachmentType
     */
    public function __construct($newName, $oldName = null, $id = null, $attachmentType = null)
    {
        $this->newName = $newName;
        $this->oldName = $oldName;
        $this->id = $id;
        $this->attachmentType = $attachmentType;
    }


    /**
     * @param MapsAttachmentTypes            $attachmentTypeMapper
     * @param AttachmentCategoriesRepository $repository
     * @param LogsAttachmentEvents           $logger
     *
     * @return mixed|null|object
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
        
        $attachmentCategory = null;

        if (isset($this->id)) {
            $attachmentCategory = $this->repository->getOneById($this->id);
        } elseif (isset($this->oldName)) {
            $attachmentCategory = $this->repository->getOneByNameAndType(
                $this->oldName,
                $this->getAttachmentCategoryEntityClassName($this->attachmentType)
            );
        }

        if ($attachmentCategory === null) {
            throw new AttachmentCategoryNotFound(
                "No {$this->attachmentType} attachment category found identified by "
                . isset($this->id) ? $this->id : $this->oldName
            );
        }

        $oldName = $attachmentCategory->getName();
        $newName = $this->newName;

        $attachmentCategory->setName($newName);

        $this->repository->update($attachmentCategory);

        $this->logger->info("Renamed attachment ID '{$this->id}' from '$oldName' to '$newName'");

        return $attachmentCategory;
    }
}
