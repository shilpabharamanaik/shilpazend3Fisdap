<?php namespace Fisdap\Attachments\Categories\Repository;

use Fisdap\Data\Repository\Repository;

/**
 * Contract for attachment categories repository
 *
 * @package Fisdap\Attachments\Categories\Repository
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface AttachmentCategoriesRepository extends Repository
{
    /**
     * @param string $name
     * @param string $attachmentCategoryEntityClassName
     *
     * @return mixed
     */
    public function getOneByNameAndType($name, $attachmentCategoryEntityClassName);


    /**
     * @param array $names
     * @param string $attachmentCategoryEntityClassName
     *
     * @return mixed
     */
    public function getByNameAndType(array $names, $attachmentCategoryEntityClassName);


    /**
     * @param string $attachmentCategoryEntityClassName
     * @param bool   $asArray
     *
     * @return array
     */
    public function getAllByType($attachmentCategoryEntityClassName, $asArray = true);
}
