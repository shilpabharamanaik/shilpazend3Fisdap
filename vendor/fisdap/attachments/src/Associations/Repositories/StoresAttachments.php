<?php namespace Fisdap\Attachments\Associations\Repositories;

use Fisdap\Attachments\Associations\Entities\AssociatedEntityNotFound;

/**
 * Contract for repositories that are responsible for handling entities associated with attachments
 *
 * @package Fisdap\Attachments\Associations\Repositories
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface StoresAttachments
{
    /**
     * @param string $attachmentType
     * @param int    $associatedEntityId
     *
     * @return mixed
     * @throws AssociatedEntityNotFound
     */
    public function findAssociatedEntity($attachmentType, $associatedEntityId);
}
