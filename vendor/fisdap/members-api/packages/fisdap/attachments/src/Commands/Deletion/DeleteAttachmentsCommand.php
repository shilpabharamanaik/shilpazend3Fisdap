<?php namespace Fisdap\Attachments\Commands\Deletion;

use Fisdap\Attachments\Core\LogsAttachmentEvents;
use Fisdap\Attachments\Events\Deletion\AttachmentEntitiesDeleted;
use Fisdap\Attachments\Jobs\ProcessAttachmentsDeletion;
use Fisdap\Attachments\Queries\FindsAttachments;
use Fisdap\Attachments\Repository\AttachmentsRepository;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

/**
 * Encapsulates data pertinent to deleting one or more attachments by ID
 *
 * @package Fisdap\Attachments\Commands\Deletion
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class DeleteAttachmentsCommand
{
    /**
     * @var string
     */
    public $attachmentType;

    /**
     * @var string[]
     */
    public $ids;


    /**
     * @param string   $attachmentType
     * @param string[] $ids
     */
    public function __construct($attachmentType, array $ids)
    {
        $this->attachmentType = $attachmentType;
        $this->ids = $ids;
    }


    /**
     * @param FindsAttachments      $attachmentsFinder
     * @param AttachmentsRepository $attachmentsRepository
     * @param LogsAttachmentEvents  $logger
     * @param EventDispatcher       $eventDispatcher
     * @param BusDispatcher         $busDispatcher
     */
    public function handle(
        FindsAttachments $attachmentsFinder,
        AttachmentsRepository $attachmentsRepository,
        LogsAttachmentEvents $logger,
        EventDispatcher $eventDispatcher,
        BusDispatcher $busDispatcher
    ) {
        $deletedAttachmentEntityData = [];

        foreach ($this->ids as $id) {
            $attachment = $attachmentsFinder->findAttachment($this->attachmentType, $id);

            $attachmentsRepository->destroy($attachment);

            $deletedAttachmentEntityData[$id]['attachmentType'] = $this->attachmentType;
            $deletedAttachmentEntityData[$id]['savePath'] = $attachment->getSavePath();
        }

        $logger->info('Attachment entities deleted', $deletedAttachmentEntityData);

        $eventDispatcher->fire(new AttachmentEntitiesDeleted($deletedAttachmentEntityData));

        // queue attachment deleted job
        $busDispatcher->dispatch(new ProcessAttachmentsDeletion($deletedAttachmentEntityData));
    }
}
