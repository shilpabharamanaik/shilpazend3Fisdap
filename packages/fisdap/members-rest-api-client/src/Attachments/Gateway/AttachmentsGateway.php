<?php namespace Fisdap\Api\Client\Attachments\Gateway;

use Fisdap\Api\Client\Gateway\Gateway;

/**
 * Contract for attachments gateways
 *
 * @package Fisdap\Api\Client\Attachments\Gateway
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface AttachmentsGateway extends Gateway
{
    /**
     * Add an attachment
     *
     * @param int           $associatedEntityId
     * @param int           $userContextId
     * @param string        $filePath
     * @param string|null   $id
     * @param string|null   $nickname
     * @param string|null   $notes
     * @param string[]|null $categories
     *
     * @return object
     */
    public function create(
        $associatedEntityId,
        $userContextId,
        $filePath,
        $id = null,
        $nickname = null,
        $notes = null,
        array $categories = null
    );


    /**
     * Get a list of attachments
     *
     * @param int        $associatedEntityId
     * @param array|null $includes
     * @param array|null $includeIds
     *
     * @return \object[]
     */
    public function get($associatedEntityId, array $includes = null, array $includeIds = null);


    /**
     * @param int        $associatedEntityId
     * @param string     $id
     * @param array|null $includes
     * @param array|null $includeIds
     *
     * @return object
     */
    public function getOne($associatedEntityId, $id, array $includes = null, array $includeIds = null);


    /**
     * Modify the nickname, notes, or categories of an attachment
     *
     * @param int                $associatedEntityId
     * @param string             $id
     * @param string|null|bool   $nickname
     * @param string|null|bool   $notes
     * @param string[]|null|bool $categories
     *
     * @return object
     */
    public function modify($associatedEntityId, $id, $nickname = false, $notes = false, $categories = false);


    /**
     * Delete one or more attachments
     *
     * Attachments are deleted asynchronously on the server, so the response will be null
     *
     * @param int      $associatedEntityId
     * @param string[] $ids
     *
     * @return null
     */
    public function delete($associatedEntityId, array $ids);



    /*
     * Categories
     */

    /**
     * Create one or more shift attachment categories
     *
     * @param string[] $names
     * @return object[]
     */
    public function createCategories(array $names);


    /**
     * Get a list of attachment categories
     *
     * @return object[]
     */
    public function getCategories();


    /**
     * Delete one or more attachment categories
     *
     * @param int[] $ids
     *
     * @return object
     */
    public function deleteCategories(array $ids);
}
