<?php namespace Fisdap\Attachments\Jobs;

use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;
use Fisdap\Attachments\Core\LogsAttachmentEvents;
use Fisdap\Attachments\Events\Deletion\AttachmentFilesDeleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

/**
 * Queued job for deleting attachments
 *
 * @package Fisdap\Attachments\Commands\Deletion\Jobs
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ProcessAttachmentsDeletion implements ShouldQueue
{
    use Queueable, InteractsWithQueue;


    /**
     * @var array
     */
    private $deletedAttachmentEntityData;


    /**
     * @param array $deletedAttachmentEntityData
     */
    public function __construct(array $deletedAttachmentEntityData)
    {
        $this->deletedAttachmentEntityData = $deletedAttachmentEntityData;
    }


    /**
     * @param AttachmentsKernel    $attachmentsKernel
     * @param LogsAttachmentEvents $logger
     * @param EventDispatcher      $eventDispatcher
     */
    public function handle(
        AttachmentsKernel $attachmentsKernel,
        LogsAttachmentEvents $logger,
        EventDispatcher $eventDispatcher
    ) {
        foreach ($this->deletedAttachmentEntityData as $id => $attachmentMetadata) {
            $attachmentType = $attachmentMetadata['attachmentType'];

            $this->deleteAttachmentDirectory($attachmentsKernel, $attachmentType, $attachmentMetadata['savePath']);
        }

        $logger->info('Attachment files deleted', $this->deletedAttachmentEntityData);

        $eventDispatcher->fire(new AttachmentFilesDeleted($this->deletedAttachmentEntityData));

        //$this->delete(); // shouldn't need this anymore
    }


    /**
     * Delete attachment directory from permanent storage
     *
     * @param AttachmentsKernel $attachmentsKernel
     * @param string            $attachmentType
     * @param string            $relativeSavePath
     */
    private function deleteAttachmentDirectory(AttachmentsKernel $attachmentsKernel, $attachmentType, $relativeSavePath)
    {
        /** @var Filesystem $fileSystem */
        $fileSystem = $attachmentsKernel->getFilesystem()->disk(
            $attachmentsKernel->getFilesystemDiskName($attachmentType)
        );

        // delete attachment directory
        $fileSystem->deleteDirectory($relativeSavePath);
    }
}
