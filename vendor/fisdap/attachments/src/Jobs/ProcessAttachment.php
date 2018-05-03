<?php namespace Fisdap\Attachments\Jobs;

use Fisdap\Attachments\Core\Kernel\AttachmentsKernel;
use Fisdap\Attachments\Core\LogsAttachmentEvents;
use Fisdap\Attachments\Core\TypeMapper\MapsAttachmentTypes;
use Fisdap\Attachments\Entity\Attachment;
use Fisdap\Attachments\Entity\AttachmentNotFound;
use Fisdap\Attachments\Events\Creation\AttachmentFilesSaved;
use Fisdap\Attachments\Processing\AttachmentProcessor;
use Fisdap\Attachments\Processing\AttachmentProcessorFactory;
use Fisdap\Attachments\Repository\AttachmentsRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

/**
 * Queued job for processing attachments
 *
 * @package Fisdap\Attachments\Jobs
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ProcessAttachment implements SelfHandling, ShouldQueue
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
     * @param string $attachmentType
     * @param string $id
     */
    public function __construct($attachmentType, $id)
    {
        $this->attachmentType = $attachmentType;
        $this->id = $id;
    }


    /**
     * @param AttachmentsKernel          $attachmentsKernel
     * @param LogsAttachmentEvents       $logger
     * @param MapsAttachmentTypes        $attachmentTypeMapper
     * @param AttachmentsRepository      $attachmentsRepository
     * @param AttachmentProcessorFactory $attachmentProcessorFactory
     * @param BusDispatcher              $busDispatcher
     * @param EventDispatcher            $eventDispatcher
     */
    public function handle(
        AttachmentsKernel $attachmentsKernel,
        LogsAttachmentEvents $logger,
        MapsAttachmentTypes $attachmentTypeMapper,
        AttachmentsRepository $attachmentsRepository,
        AttachmentProcessorFactory $attachmentProcessorFactory,
        BusDispatcher $busDispatcher,
        EventDispatcher $eventDispatcher
    ) {
        /** @var Attachment $attachment */
        $attachment = $attachmentsRepository->setAttachmentEntityClassName(
            $attachmentTypeMapper->getAttachmentEntityClassName($this->attachmentType)
        )->getOneById($this->id);

        if ($attachment === null) {
            $logger->error("Unable to process missing attachment entity", [
                'attachmentType' => $this->attachmentType, 'id' => $this->id
            ]);
            
            throw new AttachmentNotFound("No '{$this->attachmentType}' attachment found with ID '{$this->id}'");
        }

        // process attachment (generate variations)
        $attachmentProcessor = $attachmentProcessorFactory->create($attachment->getMimeType());

        if ($attachmentProcessor instanceof AttachmentProcessor) {
            $attachmentProcessor->setAttachmentType(
                $this->attachmentType
            )->process(
                $attachment,
                $attachmentsKernel->getAttachmentConfig($this->attachmentType)
            );
        }

        $this->copyFilesToPermanentStorage($attachmentsKernel, $attachment);

        $attachment->setProcessed(true);

        $this->saveChangesAndCleanUp($attachmentsKernel, $attachmentsRepository, $attachment, $busDispatcher);

        $logger->info('Attachment files saved', ['attachmentType' => $this->attachmentType, 'id' => $this->id]);

        $eventDispatcher->fire(new AttachmentFilesSaved($attachment));
    }


    /**
     * Copy original attachment and variations to permanent storage
     *
     * Setting the 'ContentDisposition' option to 'attachment', forces files to be downloaded (instead of displaying
     * in-browser) by default. The ContentDisposition must contain the filename in double quotes (RFC-2231) in order
     * for filenames with spaces to work properly.
     *
     * @see http://kb.mozillazine.org/Filenames_with_spaces_are_truncated_upon_download
     * @see http://www.ietf.org/rfc/rfc2231.txt
     *
     * @param AttachmentsKernel $attachmentsKernel
     * @param Attachment $attachment
     */
    private function copyFilesToPermanentStorage(AttachmentsKernel $attachmentsKernel, Attachment $attachment)
    {
        $tempSavePath = $attachmentsKernel->generateTempSavePath($attachment->getSavePath());

        /** @var FilesystemAdapter $fileSystem */
        $fileSystem = $attachmentsKernel->getFilesystem()->disk(
            $attachmentsKernel->getFilesystemDiskName($this->attachmentType)
        );

        // upload original
        $fileSystem->getDriver()->put(
            "{$attachment->getSavePath()}/{$attachment->getFileName()}",
            file_get_contents($tempSavePath . DIRECTORY_SEPARATOR . $attachment->getFileName()),
            [
                'ContentDisposition' => "attachment; filename=\"{$attachment->getFileName()}\""
            ]
        );

        // upload variations
        if (is_array($attachment->getVariationFileNames())) {
            foreach ($attachment->getVariationFileNames() as $variationName => $variationFileName) {
                $fileSystem->getDriver()->put(
                    "{$attachment->getSavePath()}/$variationFileName",
                    file_get_contents($tempSavePath . DIRECTORY_SEPARATOR . $variationFileName),
                    [
                        'ContentDisposition' => "attachment; filename=\"$variationFileName\""
                    ]
                );
            }
        }
    }


    /**
     * Bump updated timestamp on associated entity and save, also persisting updates to the attachment entity
     *
     * @param AttachmentsKernel     $attachmentsKernel
     * @param AttachmentsRepository $attachmentsRepository
     * @param Attachment            $attachment
     * @param BusDispatcher         $busDispatcher
     */
    private function saveChangesAndCleanUp(
        AttachmentsKernel $attachmentsKernel,
        AttachmentsRepository $attachmentsRepository,
        Attachment $attachment,
        BusDispatcher $busDispatcher
    ) {
        $attachmentsRepository->update($attachment);

        // remove files from temp public storage
        $deleteTempFiles = (new DeleteTempFiles(
            $this->attachmentType,
            $attachment->getId(),
            $attachment->getSavePath()
        ))->delay($attachmentsKernel->getConfigProvider()->get('temp_file_delete_delay'));

        $busDispatcher->dispatch($deleteTempFiles);
    }
}
