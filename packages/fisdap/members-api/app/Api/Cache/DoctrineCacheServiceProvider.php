<?php namespace Fisdap\Api\Cache;

use Couchbase;
use Doctrine\Common\Cache\CouchbaseCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\ORM\EntityManager;
use Illuminate\Support\ServiceProvider;


/**
 * Configures Doctrine Cache
 *
 * @package Fisdap\Api\Cache
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class DoctrineCacheServiceProvider extends ServiceProvider
{
    public function boot(EntityManager $em)
    {
        if (env('MRAPI_DOCTRINE_DEVMODE') === true) return;

        // use redis for metadata cache
        $redisConfig = $this->app['config']['doctrine.cache.redis'];
        
        $redis = new \Redis();
        $redis->connect($redisConfig['host'], $redisConfig['port']);
        $redis->select($redisConfig['database']);

        $redisCache = new RedisCache();
        $redisCache->setRedis($redis);
        
        $em->getMetadataFactory()->setCacheDriver($redisCache);
        $em->getConfiguration()->setMetadataCacheImpl($redisCache);
        
        // use Couchbase for query cache
        $couchbaseConfig = $this->app['config']['doctrine.cache.couchbase'];
		//print_r($couchbaseConfig);exit;
        /* $couchbase = new Couchbase(
            $couchbaseConfig['hosts'],
            $couchbaseConfig['user'],
            $couchbaseConfig['password'],
            $couchbaseConfig['bucket'],
            $couchbaseConfig['persistent']
        ); */
		
		$cluster = new \CouchbaseCluster($couchbaseConfig['hosts'][0]);
		$authenticator = new \Couchbase\PasswordAuthenticator();
		$authenticator->username($couchbaseConfig['user'])->password($couchbaseConfig['password']);
		$cluster->authenticate($authenticator);
		$bucket = $cluster->openBucket($couchbaseConfig['bucket']);
		
		// Retrieve a document
	
	    $couchbaseCache = new CouchbaseCache();
        $couchbaseCache->setCouchbase($bucket);
        
        $em->getConfiguration()->setQueryCacheImpl($couchbaseCache);
    }

    
    /**
     * @inheritdoc
     */
    public function register()
    {
    }
}