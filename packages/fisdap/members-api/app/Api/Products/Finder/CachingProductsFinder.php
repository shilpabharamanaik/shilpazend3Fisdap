<?php namespace Fisdap\Api\Products\Finder;

use Fisdap\Entity\User;
use Fisdap\Logging\ClassLogging;
use Illuminate\Auth\AuthManager;
use Illuminate\Cache\Repository as Cache;
use Illuminate\Contracts\Cache\Store as CacheStore;
use Illuminate\Cache\TaggableStore;
use Illuminate\Config\Repository as Config;

/**
 * Decorator for ProductsFinder that enables caching
 *
 * @package Fisdap\Api\Products
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class CachingProductsFinder implements FindsProducts
{
    use ClassLogging;


    const CACHE_TAG = 'UserContextProductNames';


    /**
     * @var AuthManager
     */
    private $authManager;

    /**
     * @var FindsProducts
     */
    private $productsFinder;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var CacheStore|TaggableStore
     */
    private $cacheStore;

    /**
     * @var Config
     */
    private $config;


    /**
     * @param AuthManager   $authManager
     * @param FindsProducts $productsFinder
     * @param Cache         $cache
     * @param Config        $config
     */
    public function __construct(AuthManager $authManager, FindsProducts $productsFinder, Cache $cache, Config $config)
    {
        $this->authManager = $authManager;
        $this->productsFinder = $productsFinder;
        $this->cache = $cache;
        $this->cacheStore = $cache->getStore();
        $this->config = $config;
    }


    /**
     * @param int $userContextId
     *
     * @return array
     * @throws \Fisdap\Api\Queries\Exceptions\ResourceNotFound
     */
    public function getUserContextProductNames($userContextId)
    {
        /** @var User $user */
        $user = $this->authManager->guard()->user();
        $accessToken = $user !== null ? $user->getAccessToken() : '';

        $cacheKey = "{$accessToken}_$userContextId";

        if ($this->cacheStore->tags(self::CACHE_TAG)->has($cacheKey)) {
            $productNames = $this->cacheStore->tags(self::CACHE_TAG)->get($cacheKey);
            $this->classLogDebug(
                "Products for userContextId '$userContextId' are cached in tag '"
                    . self::CACHE_TAG . "' with key '$cacheKey'",
                $productNames
            );

            return $productNames;
        } else {
            $productNames = $this->productsFinder->getUserContextProductNames($userContextId);
            $this->classLogDebug("Products for userContextId '$userContextId' were NOT cached", $productNames);
            $this->cacheStore->tags(self::CACHE_TAG)
                ->put($cacheKey, $productNames, $this->config->get('cache.user_context_product_names_lifetime'));

            return $productNames;
        }
    }


    /**
     * @inheritdoc
     */
    public function getUserContextProducts($userContextId)
    {
        return $this->productsFinder->getUserContextProducts($userContextId);
    }


    /**
     * @inheritdoc
     */
    public function getUniqueProductsFromPackages(array $packages)
    {
        return $this->productsFinder->getUniqueProductsFromPackages($packages);
    }


    /**
     * @inheritdoc
     */
    public function getConfigurationValueForProductsOrPackages(
        array $productIds = null,
        array $productPackageIds = null
    ) {
        return $this->productsFinder->getConfigurationValueForProductsOrPackages($productIds, $productPackageIds);
    }


    /**
     * @inheritdoc
     */
    public function hasProduct($productName, array $userProducts)
    {
        return $this->productsFinder->hasProduct($productName, $userProducts);
    }


    /**
     * @inheritdoc
     */
    public function findProductsAndPackagesByIsbns(array $isbns)
    {
        return $this->productsFinder->findProductsAndPackagesByIsbns($isbns);
    }
}
