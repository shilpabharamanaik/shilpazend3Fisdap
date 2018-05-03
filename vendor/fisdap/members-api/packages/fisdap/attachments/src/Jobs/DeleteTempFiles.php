<?php namespace Fisdap\Attachments\Jobs;

use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;
use Fisdap\Attachments\Core\LogsAttachmentEvents;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Queued job for deleting temp files, to be delayed until a few minutes after creation/processing has completed
 *
 * @package Fisdap\Attachments\Jobs
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class DeleteTempFiles implements ShouldQueue
{
    use Queueable, InteractsWithQueue;


    /**
     * @var string
     */
    private $attachmentType;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $relativeSavePath;


    /**
     * @param string $attachmentType
     * @param string $id
     * @param string $relativeSavePath
     */
    public function __construct($attachmentType, $id, $relativeSavePath)
    {
        $this->attachmentType = $attachmentType;
        $this->id = $id;
        $this->relativeSavePath = $relativeSavePath;
    }


    /**
     * @param AttachmentsKernel $attachmentsKernel
     * @param LogsAttachmentEvents       $logger
     */
    public function handle(AttachmentsKernel $attachmentsKernel, LogsAttachmentEvents $logger)
    {
        /** @var Filesystem $tempFileSystem */
        $tempFileSystem = $attachmentsKernel->getFilesystem()->disk(
            $attachmentsKernel->getFilesystemTempDiskName()
        );
        $tempFileSystem->deleteDirectory($this->relativeSavePath);

        $logger->info('Attachment temp files deleted', get_object_vars($this));
    }
}
