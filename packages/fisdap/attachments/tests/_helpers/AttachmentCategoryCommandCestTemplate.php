<?php

use Fisdap\Attachments\Categories\Repository\AttachmentCategoriesRepository;
use Fisdap\Attachments\Core\LogsAttachmentEvents;
use Fisdap\Attachments\Core\TypeMapper\MapsAttachmentTypes;


/**
 * Class AttachmentCategoryCommandCestTemplate
 * 
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
abstract class AttachmentCategoryCommandCestTemplate
{
    /**
     * @var MapsAttachmentTypes|Mockery\mock
     */
    protected $attachmentTypeMapperMock;

    /**
     * @var AttachmentCategoriesRepository|Mockery\mock
     */
    protected $repositoryMock;

    /**
     * @var LogsAttachmentEvents|Mockery\mock
     */
    protected $loggerMock;


    public function _before(UnitTester $I)
    {
        $this->attachmentTypeMapperMock = Mockery::mock(MapsAttachmentTypes::class);
        $this->repositoryMock = Mockery::mock(AttachmentCategoriesRepository::class);
        $this->loggerMock = Mockery::mock(LogsAttachmentEvents::class);
    }
}