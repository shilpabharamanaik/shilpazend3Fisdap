<?php

use Fisdap\Attachments\Configuration\AttachmentConfig;
use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;
use Fisdap\Attachments\Core\LogsAttachmentEvents;
use Fisdap\Attachments\Core\TypeMapper\MapsAttachmentTypes;
use Fisdap\Attachments\Entity\Attachment;
use Fisdap\Attachments\Jobs\ProcessAttachment;
use Fisdap\Attachments\Processing\AttachmentProcessor;
use Fisdap\Attachments\Processing\AttachmentProcessorFactory;
use Fisdap\Attachments\Repository\AttachmentsRepository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use League\Flysystem\FilesystemInterface;


class ProcessAttachmentCest
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
     * @var MapsAttachmentTypes|Mockery\mock
     */
    private $attachmentTypeMapperMock;

    /**
     * @var AttachmentsRepository|Mockery\mock
     */
    private $attachmentsRepositoryMock;

    /**
     * @var Container|Mockery\mock
     */
    private $containerMock;

    /**
     * @var AttachmentProcessorFactory|Mockery\mock
     */
    private $attachmentProcessorFactoryMock;

    /**
     * @var Dispatcher|Mockery\mock
     */
    private $dispatcherMock;

    /**
     * @var EventDispatcher|Mockery\mock
     */
    private $eventDispatcherMock;

    /**
     * @var ProcessAttachment
     */
    private $processAttachment;

    /**
     * @var string
     */
    private $pretendUpload;

    /**
     * @var string
     */
    private $pretendThumbnail;


    public function _before(UnitTester $I)
    {
        $this->attachmentsKernelMock = Mockery::mock(AttachmentsKernel::class);
        $this->loggerMock = Mockery::mock(LogsAttachmentEvents::class);
        $this->attachmentTypeMapperMock = Mockery::mock(MapsAttachmentTypes::class);
        $this->attachmentsRepositoryMock = Mockery::mock(AttachmentsRepository::class);
        $this->containerMock = Mockery::mock(Container::class);
        $this->attachmentProcessorFactoryMock = new AttachmentProcessorFactory($this->containerMock);
        $this->dispatcherMock = Mockery::mock(Dispatcher::class);
        $this->eventDispatcherMock = Mockery::mock(EventDispatcher::class);

        $this->processAttachment = new ProcessAttachment('fake', 'abc123');

        $testFileName = 'cute-cat-wallpapers.jpg';

        $testFilePath = codecept_data_dir($testFileName);

        $this->pretendUpload = tempnam('/tmp', 'test-attachment');
        $this->pretendThumbnail = tempnam('/tmp', 'test-attachment-thumb');

        copy($testFilePath, $this->pretendUpload);
        copy($testFilePath, $this->pretendThumbnail);
    }


    public function _after(UnitTester $I)
    {
        unlink($this->pretendUpload);
        unlink($this->pretendThumbnail);
    }


    public function it_can_process_an_attachment(UnitTester $I)
    {
        // arrange
        $this->attachmentTypeMapperMock->shouldReceive('getAttachmentEntityClassName')->once()
            ->andReturn(Attachment::class);

        /** @var Attachment|Mockery\mock $fakeAttachment */
        $fakeAttachment = Mockery::mock(Attachment::class);

        $this->attachmentsRepositoryMock->shouldReceive('setAttachmentEntityClassName->getOneById')->once()
            ->with('abc123')->andReturn($fakeAttachment);

        $fakeAttachment->shouldReceive('getSavePath')->times(4)->andReturn('fakes/12345/abc123');

        $this->attachmentsKernelMock->shouldReceive('generateTempSavePath')->once()->with('fakes/12345/abc123')
            ->andReturn('/tmp');


        $fakeAttachment->shouldReceive('getMimeType')->once()->andReturn('example/type');

        /** @var AttachmentProcessor|Mockery\mock $fakeAttachmentProcessor */
        $fakeAttachmentProcessor = Mockery::mock(AttachmentProcessor::class);

        $this->containerMock->shouldReceive('make')->once()->andReturn($fakeAttachmentProcessor);

        $this->attachmentsKernelMock->shouldReceive('getAttachmentConfig')->once()
            ->andReturn(new AttachmentConfig());

        $fakeAttachmentProcessor->shouldReceive('setAttachmentType->process')->once();


        /** @var FilesystemInterface|Mockery\mock $fakeFileSystem */
        $fakeFileSystem = Mockery::mock(FilesystemInterface::class);

        $this->attachmentsKernelMock->shouldReceive('getFilesystemDiskName')->once()->andReturn('attachments');
        $this->attachmentsKernelMock->shouldReceive('getFilesystem->disk')->once()->with('attachments')
            ->andReturn($fakeFileSystem);

        $fakeAttachment->shouldReceive('getFileName')->times(3)->andReturn(basename($this->pretendUpload));

        $fakeAttachment->shouldReceive('getVariationFileNames')->once()
            ->andReturn(['thumbnail' => basename($this->pretendThumbnail)]);

        $fakeFileSystem->shouldReceive('getDriver->put')->twice();

        $this->attachmentsRepositoryMock->shouldReceive('update')->once()->with($fakeAttachment);

        $fakeAttachment->shouldReceive('getId')->once()->andReturn('abc123');

        $this->attachmentsKernelMock->shouldReceive('getConfigProvider->get')->once()->with('temp_file_delete_delay')
            ->andReturn(300);
        $this->dispatcherMock->shouldReceive('dispatch')->once();

        $fakeAttachment->shouldReceive('setProcessed')->once()->with(true);

        $this->loggerMock->shouldReceive('info')->once();

        $fakeAttachment->shouldReceive('toArray')->once()->andReturn([]);

        $this->eventDispatcherMock->shouldReceive('fire')->once();


        // act
        $this->processAttachment->handle(
            $this->attachmentsKernelMock,
            $this->loggerMock,
            $this->attachmentTypeMapperMock,
            $this->attachmentsRepositoryMock,
            $this->attachmentProcessorFactoryMock,
            $this->dispatcherMock,
            $this->eventDispatcherMock
        );
    }
}