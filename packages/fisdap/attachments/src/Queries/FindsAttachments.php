<?php namespace Fisdap\Attachments\Queries;

use Fisdap\Attachments\Entity\Attachment;
use Fisdap\Attachments\Entity\AttachmentNotFound;

/**
 * Contract for retrieving one or all attachments associated with an entity
 *
 * @package Fisdap\Attachments\Queries
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface FindsAttachments
{
    /**
     * @param string     $attachmentType
     * @param string     $id
     * @param array|null $associations
     * @param array|null $associationIds
     * @param bool       $asArray
     *
     * @return Attachment
     * @throws AttachmentNotFound
     */
    public function findAttachment(
        $attachmentType,
        $id,
        array $associations = null,
        array $associationIds = null,
        $asArray = false
    );


    /**
     * @param string      $attachmentType
     * @param int         $associatedEntityId
     * @param array|null  $associations
     * @param array|null  $associationIds
     *
     * @return array
     * @throws AttachmentNotFound
     */
    public function findAllAttachments(
        $attachmentType,
        $associatedEntityId,
        array $associations = null,
        array $associationIds = null
    );
}
