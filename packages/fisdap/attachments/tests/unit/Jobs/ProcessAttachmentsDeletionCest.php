<?php

use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;
use Fisdap\Attachments\Core\LogsAttachmentEvents;
use Fisdap\Attachments\Jobs\ProcessAttachmentsDeletion;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use League\Flysystem\FilesystemInterface;

class ProcessAttachmentsDeletionCest
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
     * @var EventDispatcher|Mockery\mock
     */
    private $eventDispatcherMock;

    /**
     * @var ProcessAttachmentsDeletion|Mockery\mock
     */
    private $processAttachmentsDeletion;


    public function _before(UnitTester $I)
    {
        $this->attachmentsKernelMock = Mockery::mock(AttachmentsKernel::class);
        $this->loggerMock = Mockery::mock(LogsAttachmentEvents::class);
        $this->eventDispatcherMock = Mockery::mock(EventDispatcher::class);

        $this->fakeQueueJob = Mockery::mock(Job::class);

        $this->processAttachmentsDeletion = new ProcessAttachmentsDeletion([
            'abc123' => [
                'attachmentType' => 'fake',
                'savePath' => 'fakes/12345/abc123'
            ]
        ]);
    }


    public function _after(UnitTester $I)
    {
    }


    public function it_can_process_attachment_deletions(UnitTester $I)
    {
        // arrange
        $this->attachmentsKernelMock->shouldReceive('getFilesystemDiskName')->once()->with('fake')
            ->andReturn('attachments');

        /** @var FilesystemInterface|Mockery\mock $fakeFileSystem */
        $fakeFileSystem = Mockery::mock(FilesystemInterface::class);

        $this->attachmentsKernelMock->shouldReceive('getFilesystem->disk')->once()->with('attachments')
            ->andReturn($fakeFileSystem);

        $fakeFileSystem->shouldReceive('deleteDirectory')->once()->with('fakes/12345/abc123');

        $this->loggerMock->shouldReceive('info')->once();

        $this->eventDispatcherMock->shouldReceive('fire')->once();

        // act
        $this->processAttachmentsDeletion->handle(
            $this->attachmentsKernelMock,
            $this->loggerMock,
            $this->eventDispatcherMock
        );
    }
}
