<?php namespace Fisdap\Api\Client\Attachments\Categories\Gateway;

use Fisdap\Api\Client\Gateway\Gateway;


/**
 * Contract for attachment categories gateways
 *
 * @package Fisdap\Api\Client\Attachments\Categories\Gateway
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface AttachmentCategoriesGateway extends Gateway
{
    /**
     * Rename an attachment category
     *
     * @param int    $id
     * @param string $newName
     *
     * @return object
     */
    public function renameCategory($id, $newName);
}