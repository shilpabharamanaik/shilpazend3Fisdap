<?php namespace Fisdap\Attachments\Commands\Creation;

use Doctrine\Common\Inflector\Inflector;
use Fisdap\Attachments\Associations\FindsAssociatedEntities;
use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;
use Fisdap\Attachments\Core\LogsAttachmentEvents;
use Fisdap\Attachments\Entity\Attachment;
use Fisdap\Attachments\Entity\AttachmentFactory;
use Fisdap\Attachments\Events\Creation\AttachmentEntityCreated;
use Fisdap\Attachments\Jobs\ProcessAttachment;
use Fisdap\Attachments\Repository\AttachmentsRepository;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Encapsulates data pertinent to attachment creation
 *
 * @package Fisdap\Attachments\Commands\Creation
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class CreateAttachmentCommand
{
    /**
     * @var string
     */
    public $attachmentType;

    /**
     * @var int
     */
    public $associatedEntityId;

    /**
     * @var int
     */
    public $userContextId;

    /**
     * @var UploadedFile
     */
    public $uploadedFile;

    /**
     * @var string|null
     */
    public $id;

    /**
     * @var string|null
     */
    public $nickname;

    /**
     * @var string|null
     */
    public $notes;

    /**
     * @var string[]|null
     */
    public $categories;


    /**
     * @param string                $attachmentType
     * @param int                   $associatedEntityId
     * @param int                   $userContextId
     * @param UploadedFile          $uploadedFile
     * @param string|null           $id
     * @param string|null           $nickname
     * @param string|null           $notes
     * @param string[]|null         $categories
     */
    public function __construct(
        $attachmentType,
        $associatedEntityId,
        $userContextId,
        UploadedFile $uploadedFile,
        $id = null,
        $nickname = null,
        $notes = null,
        array $categories = null
    ) {
        $this->attachmentType = $attachmentType;
        $this->associatedEntityId = $associatedEntityId;
        $this->userContextId = $userContextId;
        $this->uploadedFile = $uploadedFile;

        if ($id === null) {
            $id = Attachment::generateId();
        }
        
        $this->id = $id;
        
        $this->nickname = $nickname;
        $this->notes = $notes;
        $this->categories = $categories;
    }


    /**
     * @param AttachmentsKernel       $attachmentsKernel
     * @param LogsAttachmentEvents    $logger
     * @param FindsAssociatedEntities $associatedEntityFinder
     * @param AttachmentsRepository   $attachmentsRepository
     * @param AttachmentFactory       $attachmentFactory
     * @param EventDispatcher         $eventDispatcher
     * @param BusDispatcher           $busDispatcher
     *
     * @return Attachment
     */
    public function handle(
        AttachmentsKernel $attachmentsKernel,
        LogsAttachmentEvents $logger,
        FindsAssociatedEntities $associatedEntityFinder,
        AttachmentsRepository $attachmentsRepository,
        AttachmentFactory $attachmentFactory,
        EventDispatcher $eventDispatcher,
        BusDispatcher $busDispatcher
    ) {
        $savePath = Inflector::pluralize($this->attachmentType) . '/' . $this->associatedEntityId . '/' . $this->id;

        // move uploaded file from OS temp to pre-process temp, decoding filename
        $tempFile = $this->uploadedFile->move(
            $attachmentsKernel->generateTempSavePath($savePath),
            urldecode($this->uploadedFile->getClientOriginalName())
        );

        // get associated entity
        $associatedEntity = $associatedEntityFinder->find($this->attachmentType, $this->associatedEntityId);

        // create and save attachment entity
        $attachment = $attachmentFactory->create(
            $this->attachmentType,
            $this->userContextId,
            $associatedEntity,
            $this->id,
            $tempFile,
            $savePath,
            $this->nickname,
            $this->notes,
            $this->categories
        );

        $attachmentsRepository->store($attachment);

        $logger->info('Attachment entity created', $attachment->toArray());

        $eventDispatcher->fire(new AttachmentEntityCreated($attachment));

        // queue attachment created job
        $busDispatcher->dispatch(new ProcessAttachment($this->attachmentType, $this->id));

        return $attachment;
    }
}
