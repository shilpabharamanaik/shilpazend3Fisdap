<?php

use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;
use Fisdap\Attachments\Core\LogsAttachmentEvents;
use Fisdap\Attachments\Jobs\DeleteTempFiles;
use League\Flysystem\FilesystemInterface;

class DeleteTempFilesCest
{
    /**
     * @var LogsAttachmentEvents|Mockery\mock
     */
    private $loggerMock;

    /**
     * @var AttachmentsKernel|Mockery\mock
     */
    private $attachmentsKernelMock;


    /**
     * @var \Fisdap\Attachments\Jobs\DeleteTempFiles
     */
    private $deleteTempFiles;


    public function _before(UnitTester $I)
    {
        $this->loggerMock = Mockery::mock(LogsAttachmentEvents::class);
        $this->attachmentsKernelMock = Mockery::mock(AttachmentsKernel::class);

        $this->fakeQueueJob = Mockery::mock(Job::class);

        $this->deleteTempFiles = new DeleteTempFiles('fake', 'abc123', 'fakes/12345/abc123');
    }


    public function _after(UnitTester $I)
    {
    }


    public function it_can_delete_temp_files(UnitTester $I)
    {
        // arrange
        /** @var FilesystemInterface|Mockery\mock $fakeFileSystem */
        $fakeFileSystem = Mockery::mock(FilesystemInterface::class);

        $this->attachmentsKernelMock->shouldReceive('getFilesystemTempDiskName')->once()->andReturn('attachments');
        $this->attachmentsKernelMock->shouldReceive('getFilesystem->disk')->once()->with('attachments')
            ->andReturn($fakeFileSystem);

        $fakeFileSystem->shouldReceive('deleteDirectory')->once()->with('fakes/12345/abc123');

        $this->loggerMock->shouldReceive('info')->once();

        // act
        $this->deleteTempFiles->handle($this->attachmentsKernelMock, $this->loggerMock);
    }
}
