<?php namespace Fisdap\Api\Cache;

use Couchbase;
use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\Cache\Store;

/**
 * Couchbase storage driver for Laravel cache facility
 *
 * @package Fisdap\Api\Cache
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @todo extract to package
 */
class CouchbaseStore extends TaggableStore implements Store
{
    /**
     * @var Couchbase
     */
    protected $couchbase;

    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected $prefix;


    /**
     * @param Couchbase $couchbase
     * @param string     $prefix
     */
    public function __construct(Couchbase $couchbase, $prefix = '')
    {
        $this->couchbase = $couchbase;
        $this->prefix = strlen($prefix) > 0 ? $prefix.':' : '';
    }


    /**
     * @inheritdoc
     */
    public function get($key)
    {
        $value = $this->couchbase->get($this->prefix.$key);

        if ($this->couchbase->getResultCode() != COUCHBASE_KEY_ENOENT) {
            return $value;
        }
    }

    
    /**
     * Retrieve multiple items from the cache by key.
     *
     * Items not found in the cache will have a null value.
     *
     * @param  array  $keys
     * @return array
     */
    public function many(array $keys)
    {
        $prefixedKeys = array_map(function ($key) {
            return $this->prefix.$key;
        }, $keys);

        $values = $this->couchbase->getMulti($prefixedKeys, null, COUCHBASE_PRESERVE_ORDER);

        if ($this->couchbase->getResultCode() != COUCHBASE_KEY_ENOENT) {
            return array_fill_keys($keys, null);
        }

        return array_combine($keys, $values);
    }

    /**
     * @inheritdoc
     */
    public function put($key, $value, $minutes)
    {
        $this->couchbase->set($this->prefix.$key, $value, $minutes * 60);
    }


    /**
     * Store multiple items in the cache for a given number of minutes.
     *
     * @param  array  $values
     * @param  int  $minutes
     * @return void
     */
    public function putMany(array $values, $minutes)
    {
        $prefixedValues = [];

        foreach ($values as $key => $value) {
            $prefixedValues[$this->prefix.$key] = $value;
        }

        $this->couchbase->setMulti($prefixedValues, $minutes * 60);
    }
    

    /**
     * @inheritdoc
     */
    public function increment($key, $value = 1)
    {
        return $this->couchbase->increment($this->prefix.$key, $value);
    }


    /**
     * @inheritdoc
     */
    public function decrement($key, $value = 1)
    {
        return $this->couchbase->decrement($this->prefix.$key, $value);
    }


    /**
     * @inheritdoc
     */
    public function forever($key, $value)
    {
        $this->put($key, $value, 0);
    }


    /**
     * @inheritdoc
     */
    public function forget($key)
    {
        $this->couchbase->delete($this->prefix.$key);
    }


    /**
     * @inheritdoc
     */
    public function flush()
    {
        $this->couchbase->flush();
    }


    /**
     * Get the underlying Couchbase connection.
     *
     * @return Couchbase
     */
    public function getCouchbase()
    {
        return $this->couchbase;
    }


    /**
     * @inheritdoc
     */
    public function getPrefix()
    {
        return $this->prefix;
    }
}
