<?php namespace Fisdap\Attachments\Repository;

use Fisdap\Data\Repository\Repository;

/**
 * Contract for attachments repository
 *
 * @package Fisdap\Attachments\Repository
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface AttachmentsRepository extends Repository
{
    /**
     * @param int           $userContextId
     * @param string[]|null $attachmentEntityClassNames
     *
     * @return int
     */
    public function getCountByUserContextId($userContextId, array $attachmentEntityClassNames = null);


    /**
     * @param string $attachmentEntityClassName
     *
     * @return $this
     */
    public function setAttachmentEntityClassName($attachmentEntityClassName);
}
