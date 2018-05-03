<?php namespace Fisdap\Attachments\Categories\Commands\Creation;

use Doctrine\Common\Inflector\Inflector;
use Fisdap\Attachments\Categories\Commands\AttachmentCategoryCommand;
use Fisdap\Attachments\Categories\Entity\AttachmentCategory;
use Fisdap\Attachments\Categories\Repository\AttachmentCategoriesRepository;
use Fisdap\Attachments\Core\LogsAttachmentEvents;
use Fisdap\Attachments\Core\TypeMapper\MapsAttachmentTypes;

/**
 * Encapsulates data pertinent to attachment category creation
 *
 * @package Fisdap\Attachments\Categories\Commands\Creation
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class CreateAttachmentCategoriesCommand extends AttachmentCategoryCommand
{
    /**
     * @var string
     */
    public $attachmentType;

    /**
     * @var string[]
     */
    public $names;


    /**
     * @param string   $attachmentType
     * @param string[] $names
     */
    public function __construct($attachmentType, array $names)
    {
        $this->attachmentType = $attachmentType;
        $this->names = $names;
    }


    /**
     * @param MapsAttachmentTypes            $attachmentTypeMapper
     * @param AttachmentCategoriesRepository $repository
     * @param LogsAttachmentEvents           $logger
     *
     * @return array
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
        
        /** @var AttachmentCategory $attachmentCategoryEntityClassName */
        $attachmentCategoryEntityClassName = $this->getAttachmentCategoryEntityClassName($this->attachmentType);

        $attachmentCategories = [];

        foreach ($this->names as $name) {
            $attachmentCategories[] = $attachmentCategoryEntityClassName::create($name);
        }

        $this->repository->storeCollection($attachmentCategories);

        $categoryInflection = (count($this->names) > 1) ? Inflector::pluralize('category') : 'category';

        $this->logger->info(
            "Created '$this->attachmentType' attachment $categoryInflection named " . implode(', ', $this->names)
        );

        return $attachmentCategories;
    }
}
