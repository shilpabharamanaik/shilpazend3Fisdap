<?php namespace Fisdap\Api\Users\UserContexts\Permissions\Finder;

use Fisdap\Entity\User;
use Fisdap\Logging\ClassLogging;
use Illuminate\Auth\AuthManager;
use Illuminate\Cache\Repository as Cache;
use Illuminate\Contracts\Cache\Store as CacheStore;
use Illuminate\Cache\TaggableStore;
use Illuminate\Config\Repository as Config;


/**
 * Decorator for PermissionsFinder that enables caching
 *
 * @package Fisdap\Api\Users\UserContexts\Permissions\Finder
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class CachingPermissionsFinder implements FindsPermissions
{
    use ClassLogging;


    const PERMISSIONS_CACHE_TAG = 'InstructorPermissions';

    const PERMISSION_NAMES_CACHE_TAG = 'InstructorPermissionNames';


    /**
     * @var AuthManager
     */
    private $authManager;

    /**
     * @var FindsPermissions
     */
    private $permissionsFinder;

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
     * @param AuthManager      $authManager
     * @param FindsPermissions $permissionsFinder
     * @param Cache            $cache
     * @param Config           $config
     */
    public function __construct(AuthManager $authManager, FindsPermissions $permissionsFinder, Cache $cache, Config $config)
    {
        $this->authManager = $authManager;
        $this->permissionsFinder = $permissionsFinder;
        $this->cache = $cache;
        $this->cacheStore = $cache->getStore();
        $this->config = $config;
    }


    /**
     * @inheritdoc
     */
    public function all()
    {
        return $this->permissionsFinder->all();
    }


    /**
     * @inheritdoc
     */
    public function one($permissionId)
    {
        return $this->permissionsFinder->one($permissionId);
    }


    /**
     * @inheritdoc
     */
    public function getInstructorPermissions($instructorId)
    {
        $cacheKey = $this->generateCacheKey($instructorId);

        if ($this->cacheStore->tags(self::PERMISSIONS_CACHE_TAG)->has($cacheKey)) {
            $permissions = $this->cacheStore->tags(self::PERMISSIONS_CACHE_TAG)->get($cacheKey);
            $this->classLogDebug(
                "Instructor permissions for instructorId '$instructorId' are cached in tag '"
                . self::PERMISSIONS_CACHE_TAG . "' with key '$cacheKey'", $permissions
            );

            return $permissions;
        } else {
            $permissions = $this->permissionsFinder->getInstructorPermissions($instructorId);
            $this->classLogDebug("Permissions for instructorId '$instructorId' were NOT cached", $permissions);
            $this->cacheStore->tags(self::PERMISSIONS_CACHE_TAG)->put(
                $cacheKey, $permissions, $this->config->get('cache.instructor_permissions_lifetime')
            );

            return $permissions;
        }
    }


    /**
     * @inheritdoc
     */
    public function getInstructorPermissionNames($instructorId)
    {
        $cacheKey = $this->generateCacheKey($instructorId);

        if ($this->cacheStore->tags(self::PERMISSION_NAMES_CACHE_TAG)->has($cacheKey)) {
            $permissionNames = $this->cacheStore->tags(self::PERMISSION_NAMES_CACHE_TAG)->get($cacheKey);
            $this->classLogDebug(
                "Permission names for instructorId '$instructorId' are cached in tag '"
                . self::PERMISSION_NAMES_CACHE_TAG ."' with key '$cacheKey'", $permissionNames
            );

            return $permissionNames;
        } else {
            $permissionNames = $this->permissionsFinder->getInstructorPermissionNames($instructorId);
            $this->classLogDebug("Permission names for instructorId '$instructorId' were NOT cached", $permissionNames);
            $this->cacheStore->tags(self::PERMISSION_NAMES_CACHE_TAG)->put(
                $cacheKey, $permissionNames, $this->config->get('cache.instructor_permission_names_lifetime')
            );

            return $permissionNames;
        }
    }


    /**
     * @inheritdoc
     */
    public function hasInstructorPermission($permissionName, array $instructorPermissions)
    {
        return $this->permissionsFinder->hasInstructorPermission($permissionName, $instructorPermissions);
    }


    /**
     * @param $instructorId
     *
     * @return string
     */
    private function generateCacheKey($instructorId)
    {
        /** @var User $user */
        $user = $this->authManager->guard()->user();
        $accessToken = $user !== null ? $user->getAccessToken() : '';

        return $cacheKey = "{$accessToken}_$instructorId";
    }
}