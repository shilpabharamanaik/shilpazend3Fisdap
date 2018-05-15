<?php namespace AscendLearning\Lti\Storage;

use Franzl\Lti\Storage\AbstractStorage;

/**
 * Class HybridStorage
 *
 * @package AscendLearning\Lti\Storage
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class HybridStorage extends AbstractStorage
{
    /**
     * @var AbstractStorage
     */
    private $defaultStorage;

    /**
     * @var AbstractStorage
     */
    private $toolConsumerStorage;

    /**
     * @var AbstractStorage
     */
    private $resourceLinkStorage;

    /**
     * @var AbstractStorage
     */
    private $nonceStorage;

    /**
     * @var AbstractStorage
     */
    private $shareKeyStorage;

    /**
     * @var AbstractStorage
     */
    private $userStorage;


    /**
     * HybridStorage constructor.
     *
     * @param AbstractStorage $defaultStorage
     * @param AbstractStorage $toolConsumerStorage
     * @param AbstractStorage $resourceLinkStorage
     * @param AbstractStorage $nonceStorage
     * @param AbstractStorage $shareKeyStorage
     * @param AbstractStorage $userStorage
     */
    public function __construct(
        AbstractStorage $defaultStorage,
        AbstractStorage $toolConsumerStorage = null,
        AbstractStorage $resourceLinkStorage = null,
        AbstractStorage $nonceStorage = null,
        AbstractStorage $shareKeyStorage = null,
        AbstractStorage $userStorage = null
    ) {
        $this->defaultStorage = $defaultStorage;
        $this->toolConsumerStorage = $toolConsumerStorage ?: $defaultStorage;
        $this->resourceLinkStorage = $resourceLinkStorage ?: $defaultStorage;
        $this->nonceStorage = $nonceStorage ?: $defaultStorage;
        $this->shareKeyStorage = $shareKeyStorage ?: $defaultStorage;
        $this->userStorage = $userStorage ?: $defaultStorage;
    }


    /**
     * @inheritdoc
     */
    public function toolConsumerLoad($consumer)
    {
        return $this->toolConsumerStorage->toolConsumerLoad($consumer);
    }

    /**
     * @inheritdoc
     */
    public function toolConsumerSave($consumer)
    {
        return $this->toolConsumerStorage->toolConsumerSave($consumer);
    }

    /**
     * @inheritdoc
     */
    public function toolConsumerDelete($consumer)
    {
        return $this->toolConsumerStorage->toolConsumerDelete($consumer);
    }

    /**
     * @inheritdoc
     */
    public function toolConsumerList()
    {
        return $this->toolConsumerStorage->toolConsumerList();
    }

    /**
     * @inheritdoc
     */
    public function resourceLinkLoad($resource_link)
    {
        return $this->resourceLinkStorage->resourceLinkLoad($resource_link);
    }

    /**
     * @inheritdoc
     */
    public function resourceLinkSave($resource_link)
    {
        return $this->resourceLinkStorage->resourceLinkSave($resource_link);
    }

    /**
     * @inheritdoc
     */
    public function resourceLinkDelete($resource_link)
    {
        return $this->resourceLinkStorage->resourceLinkDelete($resource_link);
    }

    /**
     * @inheritdoc
     */
    public function resourceLinkGetUserResultSourcedIDs($resource_link, $local_only, $id_scope)
    {
        return $this->resourceLinkStorage->resourceLinkGetUserResultSourcedIDs($resource_link, $local_only, $id_scope);
    }

    /**
     * @inheritdoc
     */
    public function resourceLinkGetShares($resource_link)
    {
        return $this->resourceLinkStorage->resourceLinkGetShares($resource_link);
    }

    /**
     * @inheritdoc
     */
    public function consumerNonceLoad($nonce)
    {
        return $this->nonceStorage->consumerNonceLoad($nonce);
    }

    /**
     * @inheritdoc
     */
    public function consumerNonceSave($nonce)
    {
        return $this->nonceStorage->consumerNonceSave($nonce);
    }

    /**
     * @inheritdoc
     */
    public function resourceLinkShareKeyLoad($share_key)
    {
        return $this->shareKeyStorage->resourceLinkShareKeyLoad($share_key);
    }

    /**
     * @inheritdoc
     */
    public function resourceLinkShareKeySave($share_key)
    {
        return $this->shareKeyStorage->resourceLinkShareKeySave($share_key);
    }

    /**
     * @inheritdoc
     */
    public function resourceLinkShareKeyDelete($share_key)
    {
        return $this->shareKeyStorage->resourceLinkShareKeyDelete($share_key);
    }

    /**
     * @inheritdoc
     */
    public function userLoad($user)
    {
        return $this->userStorage->userLoad($user);
    }

    /**
     * @inheritdoc
     */
    public function userSave($user)
    {
        return $this->userStorage->userSave($user);
    }

    /**
     * @inheritdoc
     */
    public function userDelete($user)
    {
        return $this->userStorage->userDelete($user);
    }
}
