<?php namespace Fisdap\Attachments\Associations\Repositories;

use Fisdap\Attachments\Associations\Entities\AssociatedEntityNotFound;

/**
 * Trait for implementing StoreAttachments contract
 *
 * @package Fisdap\Attachments\Associations\Repositories
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait RepositoryAttachmentsSupport
{
    /**
     * @param string $attachmentType
     * @param int    $associatedEntityId
     *
     * @return mixed
     * @throws AssociatedEntityNotFound
     */
    public function findAssociatedEntity($attachmentType, $associatedEntityId)
    {
        $associatedEntity = $this->getOneById($associatedEntityId);

        if ($associatedEntity === null) {
            throw new AssociatedEntityNotFound("No $attachmentType with ID '$associatedEntityId'");
        }

        return $associatedEntity;
    }
}
