<?php namespace Fisdap\Attachments\Queries;

use Fisdap\Attachments\Core\TypeMapper\MapsAttachmentTypes;
use Fisdap\Attachments\Entity\AttachmentNotFound;
use Fisdap\Attachments\Queries\Events\AttachmentFound;
use Fisdap\Attachments\Queries\Events\AttachmentsFound;
use Fisdap\Attachments\Queries\Specifications\AttachmentById;
use Fisdap\Attachments\Queries\Specifications\AttachmentsByAssociatedEntityId;
use Fisdap\Attachments\Repository\AttachmentsRepository;
use Fisdap\Queries\Specifications\CommonSpec;
use Happyr\DoctrineSpecification\Spec;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

/**
 * Service for retrieving one or all attachments associated with an entity
 *
 * @package Fisdap\Attachments\Queries
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class AttachmentsFinder implements FindsAttachments
{
    /**
     * @var MapsAttachmentTypes
     */
    private $attachmentTypeMapper;

    /**
     * @var AttachmentsRepository
     */
    private $attachmentsRepository;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;


    /**
     * @param MapsAttachmentTypes   $attachmentTypeMapper
     * @param AttachmentsRepository $attachmentsRepository
     * @param EventDispatcher       $eventDispatcher
     */
    public function __construct(
        MapsAttachmentTypes $attachmentTypeMapper,
        AttachmentsRepository $attachmentsRepository,
        EventDispatcher $eventDispatcher
    ) {
        $this->attachmentTypeMapper = $attachmentTypeMapper;
        $this->attachmentsRepository = $attachmentsRepository;
        $this->eventDispatcher = $eventDispatcher;
    }


    /**
     * @inheritdoc
     */
    public function findAttachment(
        $attachmentType,
        $id,
        array $associations = null,
        array $associationIds = null,
        $asArray = false
    ) {
        $attachmentEntityClassName = $this->attachmentTypeMapper->getAttachmentEntityClassName($attachmentType);

        $spec = Spec::andX(new AttachmentById($attachmentEntityClassName, $id));
        $spec->andX(CommonSpec::makeSpecWithAssociations($associations, $associationIds));

        $attachment = $this->attachmentsRepository->match($spec, $asArray === true ? Spec::asArray() : null);

        if (empty($attachment)) {
            throw new AttachmentNotFound("No '$attachmentType' attachment found with ID '$id'");
        }

        $this->eventDispatcher->fire(new AttachmentFound($attachment[0]));

        return array_shift($attachment);
    }


    /**
     * @inheritdoc
     */
    public function findAllAttachments(
        $attachmentType,
        $associatedEntityId,
        array $associations = null,
        array $associationIds = null
    ) {
        $attachmentEntityClassName = $this->attachmentTypeMapper->getAttachmentEntityClassName($attachmentType);

        $spec = Spec::andX(new AttachmentsByAssociatedEntityId($attachmentEntityClassName, $associatedEntityId));
        $spec->andX(CommonSpec::makeSpecWithAssociations($associations, $associationIds));

        $attachments = $this->attachmentsRepository->match($spec, Spec::asArray());

        if (empty($attachments)) {
            throw new AttachmentNotFound(
                "No attachments found for '$attachmentType' ID '$associatedEntityId'"
            );
        }

        $this->eventDispatcher->fire(new AttachmentsFound($attachments));

        return $attachments;
    }
}
