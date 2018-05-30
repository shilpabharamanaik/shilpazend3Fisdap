<?php

use Fisdap\Attachments\Categories\Entity\AttachmentCategory;
use Fisdap\Attachments\Commands\Modification\ModifyAttachmentCommand;
use Fisdap\Attachments\Core\LogsAttachmentEvents;
use Fisdap\Attachments\Core\TypeMapper\MapsAttachmentTypes;
use Fisdap\Attachments\Entity\Attachment;
use Fisdap\Attachments\Entity\AttachmentFactory;
use Fisdap\Attachments\Queries\FindsAttachments;
use Fisdap\Attachments\Repository\AttachmentsRepository;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

/**
 * Class ModifyAttachmentCommandCest
 *
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class ModifyAttachmentCommandCest
{
    /**
     * @var FindsAttachments|Mockery\mock
     */
    private $attachmentsFinderMock;

    /**
     * @var MapsAttachmentTypes|Mockery\mock
     */
    private $attachmentTypeMapperMock;

    /**
     * @var AttachmentFactory|Mockery\mock
     */
    private $attachmentFactoryMock;

    /**
     * @var AttachmentsRepository|Mockery\mock
     */
    private $attachmentsRepositoryMock;

    /**
     * @var LogsAttachmentEvents|Mockery\mock
     */
    private $loggerMock;

    /**
     * @var EventDispatcher|Mockery\mock
     */
    private $eventDispatcherMock;

    /**
     * @var ModifyAttachmentCommand
     */
    private $modifyAttachmentCommand;


    public function _before(UnitTester $I)
    {
        $this->attachmentsFinderMock = Mockery::mock(FindsAttachments::class);
        $this->attachmentTypeMapperMock = Mockery::mock(MapsAttachmentTypes::class);
        $this->attachmentFactoryMock = Mockery::Mock(AttachmentFactory::class);
        $this->attachmentsRepositoryMock = Mockery::mock(AttachmentsRepository::class);
        $this->loggerMock = Mockery::mock(LogsAttachmentEvents::class);
        $this->eventDispatcherMock = Mockery::mock(EventDispatcher::class);

        $this->modifyAttachmentCommand = new ModifyAttachmentCommand('fake', 12345, 'abc123', 'foo', 'bar baz', ['ECG']);
    }


    public function _after(UnitTester $I)
    {
    }


    public function it_can_handle_attachment_modification(UnitTester $I)
    {
        // arrange
        /** @var Attachment|Mockery\mock $fakeAttachment */
        $fakeAttachment = Mockery::mock(Attachment::class);

        $this->attachmentsFinderMock->shouldReceive('findAttachment')->once()->andReturn($fakeAttachment);

        $fakeAttachment->shouldReceive('setNickname')->once();
        $fakeAttachment->shouldReceive('setNotes')->once();

        $this->attachmentTypeMapperMock->shouldReceive('getAttachmentCategoryEntityClassName')->once()
            ->andReturn(AttachmentCategory::class);

        $fakeAttachment->shouldReceive('clearCategories')->once();

        $this->attachmentFactoryMock->shouldReceive('addCategories')->once();

        $this->attachmentsRepositoryMock->shouldReceive('update')->once()->with($fakeAttachment);

        $fakeAttachment->shouldReceive('toArray')->once()->andReturn([]);

        $this->loggerMock->shouldReceive('info')->once();

        $this->eventDispatcherMock->shouldReceive('fire')->once();

        // act
        $attachment = $this->modifyAttachmentCommand->handle(
            $this->attachmentsFinderMock,
            $this->attachmentTypeMapperMock,
            $this->attachmentFactoryMock,
            $this->attachmentsRepositoryMock,
            $this->loggerMock,
            $this->eventDispatcherMock
        );

        // assert
        $I->assertEquals($fakeAttachment, $attachment);
    }
}
