<?php

use Fisdap\Attachments\Associations\Entities\HasAttachments;
use Fisdap\Attachments\Associations\FindsAssociatedEntities;
use Fisdap\Attachments\Commands\Creation\CreateAttachmentCommand;
use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;
use Fisdap\Attachments\Core\LogsAttachmentEvents;
use Fisdap\Attachments\Entity\Attachment;
use Fisdap\Attachments\Entity\AttachmentFactory;
use Fisdap\Attachments\Repository\AttachmentsRepository;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * Class CreateAttachmentCommandCest
 * 
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class CreateAttachmentCommandCest
{
    /**
     * @var AttachmentsKernel|Mockery\mock
     */
    private $attachmentsKernelMock;

    /**
     * @var LogsAttachmentEvents|Mockery\mock
     */
    private $loggerMock;

    /**
     * @var FindsAssociatedEntities|Mockery\mock
     */
    private $associatedEntityFinderMock;

    /**
     * @var AttachmentsRepository|Mockery\mock
     */
    private $attachmentsRepositoryMock;

    /**
     * @var AttachmentFactory|Mockery\mock
     */
    private $attachmentFactoryMock;

    /**
     * @var EventDispatcher|Mockery\mock
     */
    private $eventDispatcherMock;

    /**
     * @var Dispatcher|Mockery\mock
     */
    private $dispatcherMock;

    /**
     * @var CreateAttachmentCommand
     */
    private $createAttachmentCommand;

    /**
     * @var string
     */
    private $pretendUpload;


    public function _before(UnitTester $I)
    {
        $this->attachmentsKernelMock = Mockery::mock(AttachmentsKernel::class);
        $this->loggerMock = Mockery::mock(LogsAttachmentEvents::class);
        $this->associatedEntityFinderMock = Mockery::mock(FindsAssociatedEntities::class);
        $this->attachmentsRepositoryMock = Mockery::mock(AttachmentsRepository::class);
        $this->attachmentFactoryMock = Mockery::mock(AttachmentFactory::class);
        $this->eventDispatcherMock = Mockery::mock(EventDispatcher::class);
        $this->dispatcherMock = Mockery::mock(Dispatcher::class);

        $testFileName = 'cute-cat-wallpapers.jpg';

        $testFilePath = codecept_data_dir($testFileName);

        $this->pretendUpload = tempnam('/tmp', 'test-attachment');

        copy($testFilePath, $this->pretendUpload);

        $uploadedFile = new UploadedFile(
            $this->pretendUpload,
            $testFileName,
            'image/jpeg',
            filesize($testFilePath),
            null,
            true
        );

        $this->createAttachmentCommand = new CreateAttachmentCommand(
            'fake',
            12345,
            67890,
            $uploadedFile,
            'abc123'
        );
    }


    public function _after(UnitTester $I)
    {
        unlink('/tmp/attachments/temp/fakes/12345/abc123/cute-cat-wallpapers.jpg');
    }


    public function it_can_handle_attachment_creation(UnitTester $I)
    {
        // arrange
        $this->attachmentsKernelMock->shouldReceive('generateTempSavePath')->once()->with('fakes/12345/abc123')
            ->andReturn('/tmp/attachments/temp/fakes/12345/abc123');

        $fakeAssociatedEntity = Mockery::mock(HasAttachments::class);
        $this->associatedEntityFinderMock->shouldReceive('find')->once()->withArgs(['fake', 12345])->andReturn($fakeAssociatedEntity);

        /** @var Attachment|Mockery\mock $fakeAttachment */
        $fakeAttachment = Mockery::mock(Attachment::class);

        $this->attachmentFactoryMock->shouldReceive('create')->once()->andReturn($fakeAttachment);
        $this->attachmentsRepositoryMock->shouldReceive('store')->once()->with($fakeAttachment);

        $fakeAttachment->shouldReceive('toArray')->once()->andReturn([]);

        $this->loggerMock->shouldReceive('info')->once();
        $this->eventDispatcherMock->shouldReceive('fire')->once();
        $this->dispatcherMock->shouldReceive('dispatch')->once();

        // act
        $attachment = $this->createAttachmentCommand->handle(
            $this->attachmentsKernelMock,
            $this->loggerMock,
            $this->associatedEntityFinderMock,
            $this->attachmentsRepositoryMock,
            $this->attachmentFactoryMock,
            $this->eventDispatcherMock,
            $this->dispatcherMock
        );

        // assert
        $I->assertEquals($fakeAttachment, $attachment);
    }
}