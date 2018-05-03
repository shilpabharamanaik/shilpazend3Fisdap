<?php namespace Fisdap\Attachments\Commands\Modification;

use Fisdap\Attachments\Core\LogsAttachmentEvents;
use Fisdap\Attachments\Core\TypeMapper\MapsAttachmentTypes;
use Fisdap\Attachments\Entity\AttachmentFactory;
use Fisdap\Attachments\Events\Modification\AttachmentEntityModified;
use Fisdap\Attachments\Queries\FindsAttachments;
use Fisdap\Attachments\Repository\AttachmentsRepository;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Encapsulates data pertinent to attachment modification
 *
 * @package Fisdap\Attachments\Commands\Modification
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ModifyAttachmentCommand
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
     * @param string           $attachmentType
     * @param int              $associatedEntityId
     * @param string           $id
     * @param string|null|bool $nickname
     * @param string|null|bool $notes
     * @param string[]|null    $categories
     */
    public function __construct(
        $attachmentType,
        $associatedEntityId,
        $id,
        $nickname = false,
        $notes = false,
        array $categories = null
    ) {
        $this->attachmentType = $attachmentType;
        $this->associatedEntityId = $associatedEntityId;
        $this->id = $id;
        $this->nickname = $nickname;
        $this->notes = $notes;
        $this->categories = $categories;
    }

    public function handle(
        FindsAttachments $attachmentsFinder,
        MapsAttachmentTypes $attachmentTypeMapper,
        AttachmentFactory $attachmentFactory,
        AttachmentsRepository $attachmentsRepository,
        LogsAttachmentEvents $logger,
        EventDispatcher $eventDispatcher
    ) {
        if ($this->nickname === false && $this->notes === false && $this->categories === []) {
            throw new UnprocessableEntityHttpException(
                "Please provide a JSON object with at least one of the following parameters: nickname (string), notes (string), categories (string[])"
            );
        }

        $attachment = $attachmentsFinder->findAttachment($this->attachmentType, $this->id);

        if ($this->nickname !== false) {
            $attachment->setNickname($this->nickname);
        }

        if ($this->notes !== false) {
            $attachment->setNotes($this->notes);
        }

        // replace existing categories with new categories
        if ($this->categories !== []) {
            $attachmentCategoryEntityClassName = $attachmentTypeMapper->getAttachmentCategoryEntityClassName(
                $this->attachmentType
            );

            $attachment->clearCategories();

            if ($this->categories !== null) {
                $attachmentFactory->addCategories(
                    $this->categories,
                    $attachmentCategoryEntityClassName,
                    $attachment
                );
            }
        }

        $attachmentsRepository->update($attachment);

        $logger->info('Attachment entity modified', $attachment->toArray());

        $eventDispatcher->fire(new AttachmentEntityModified($attachment));

        return $attachment;
    }
}
