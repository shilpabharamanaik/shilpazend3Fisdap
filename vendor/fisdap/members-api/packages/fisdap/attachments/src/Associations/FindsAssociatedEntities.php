<?php namespace Fisdap\Attachments\Associations;

use Fisdap\Attachments\Associations\Entities\HasAttachments;
use Fisdap\Attachments\Associations\Repositories\StoresAttachments;
use Fisdap\Data\Repository\Repository;

/**
 * Contract for finding associated entities
 *
 * @package Fisdap\Attachments\Associations
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface FindsAssociatedEntities
{
    /**
     * @param string $attachmentType
     * @param int    $associatedEntityId
     *
     * @return HasAttachments
     */
    public function find($attachmentType, $associatedEntityId);


    /**
     * @param string $attachmentType
     *
     * @return StoresAttachments|Repository
     */
    public function getAssociatedEntityRepository($attachmentType);
}
