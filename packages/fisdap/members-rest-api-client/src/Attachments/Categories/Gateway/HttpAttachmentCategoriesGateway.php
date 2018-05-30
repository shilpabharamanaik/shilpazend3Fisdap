<?php namespace Fisdap\Api\Client\Attachments\Categories\Gateway;

use Fisdap\Api\Client\Gateway\CommonHttpGateway;

/**
 * HTTP implementation of an AttachmentsGateway
 *
 * @package Fisdap\Api\Client\Attachments\Categories\Gateway
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class HttpAttachmentCategoriesGateway extends CommonHttpGateway implements AttachmentCategoriesGateway
{
    protected static $uriRoot = '/attachments';


    /**
     * Rename an attachment category
     *
     * @param int $id
     * @param string $newName
     *
     * @return object
     */
    public function renameCategory($id, $newName)
    {
        // TODO: Implement renameCategory() method.
    }
}
