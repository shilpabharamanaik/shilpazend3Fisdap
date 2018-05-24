<?php namespace Fisdap\Api\Client\Attachments\Gateway;

use Fisdap\Api\Client\Gateway\CommonHttpGateway;


/**
 * Template for HTTP implementation of an AttachmentsGateway
 *
 * @package Fisdap\Api\Client\Attachments\Gateway
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
abstract class HttpAttachmentsGatewayTemplate extends CommonHttpGateway implements AttachmentsGateway
{
    /**
     * @inheritdoc
     */
    public function create(
        $associatedEntityId, $userContextId, $filePath, $id = null, $nickname = null, $notes = null, array $categories = null
    ) {
        $multipart = [];

        $multipart[] = [
            'name' => 'attachment',
            'contents' => fopen($filePath, 'r')
        ];

        // userContextId needs to be cast to a string
        // see https://github.com/guzzle/guzzle/issues/628
        $multipart[] = ['name' => 'userRoleId', 'contents' => (string) $userContextId];

        if ($id !== null) {
            $multipart[] = ['name' => 'id', 'contents' => $id];
        }

        if ($nickname !== null) {
            $multipart[] = ['name' => 'nickname', 'contents' => $nickname];
        }

        if ($notes !== null) {
            $multipart[] = ['name' => 'notes', 'contents' => $notes];
        }

        if (is_array($categories)) {
            foreach ($categories as $category) {
                $multipart[] = ['name' => 'categories[]', 'contents' => $category];
            }
        }

        return $this->client->post(static::$uriRoot . "/$associatedEntityId/attachments", [
            'multipart' => $multipart,
            'responseType' => $this->responseType
        ]);
    }


    /**
     * @inheritdoc
     */
    public function get($associatedEntityId, array $includes = null, array $includeIds = null)
    {
        return $this->client->get(static::$uriRoot . "/$associatedEntityId/attachments", [
            'query' => [
                'includes'     => is_array($includes) ? implode(',', $includes) : null,
                'includeIds'   => is_array($includeIds) ? implode(',', $includeIds) : null,
            ],
            'responseType' => $this->responseType
        ]);
    }


    /**
     * @inheritdoc
     */
    public function getOne($associatedEntityId, $id, array $includes = null, array $includeIds = null)
    {
        return $this->client->get(static::$uriRoot . "/$associatedEntityId/attachments/$id", [
            'query' => [
                'includes'     => is_array($includes) ? implode(',', $includes) : null,
                'includeIds'   => is_array($includeIds) ? implode(',', $includeIds) : null,
            ],
            'responseType' => $this->responseType
        ]);
    }


    /**
     * @inheritdoc
     */
    public function modify($associatedEntityId, $id, $nickname = false, $notes = false, $categories = false)
    {
        $body = [];

        if ($nickname !== false) {
            $body['nickname'] = $nickname;
        }

        if ($notes !== false) {
            $body['notes'] = $notes;
        }

        if ($categories !== false) {
            if (is_array($categories) and empty($categories)) {
                throw new \BadMethodCallException('$categories must be an array with at least one element, or null');
            }

            $body['categories'] = $categories;
        }

        return $this->client->patch(static::$uriRoot . "/$associatedEntityId/attachments/$id", [
            'json' => $body,
            'responseType' => $this->responseType
        ]);
    }


    /**
     * @inheritdoc
     */
    public function delete($associatedEntityId, array $ids)
    {
        $ids = implode(',', $ids);

        return $this->client->delete(static::$uriRoot . "/$associatedEntityId/attachments/$ids", [
            'responseType' => $this->responseType
        ]);
    }



    /*
     * Categories
     */

    /**
     * Create one or more attachment categories
     *
     * @param string[] $names
     * @return object[]
     */
    public function createCategories(array $names)
    {
        // TODO: Implement createAttachmentCategories() method.
    }


    /**
     * @inheritdoc
     */
    public function getCategories()
    {
        return $this->client->get(static::$uriRoot . '/attachments/categories', [
            'responseType' => $this->responseType
        ]);
    }


    /**
     * Delete one or more attachment categories
     *
     * @param int[] $ids
     *
     * @return object
     */
    public function deleteCategories(array $ids)
    {
        // TODO: Implement deleteAttachmentCategories() method.
    }
}