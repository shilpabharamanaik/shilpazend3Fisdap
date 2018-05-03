<?php namespace Fisdap\Attachments\Categories\Queries;

/**
 * Contract for retrieving one or more attachment categories by various criteria
 *
 * @package Fisdap\Attachments\Categories\Queries
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface FindsAttachmentCategories
{
    /**
     * @param int $id
     *
     * @return mixed
     */
    public function findById($id);


    /**
     * @param string $attachmentType
     *
     * @return array
     */
    public function findAll($attachmentType);
}
