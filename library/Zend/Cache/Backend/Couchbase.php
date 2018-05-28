<?php
// Zend cache backend for Couchbase
// Writter by: Mark Austin <ganthore@gmail.com>
// This was modified based off the Libmemcached.php file

/**
 * @see Zend_Cache_Backend_Interface
 */
require_once 'Zend/Cache/Backend/ExtendedInterface.php';

/**
 * @see Zend_Cache_Backend
 */
require_once 'Zend/Cache/Backend.php';

class Zend_Cache_Backend_Couchbase extends Zend_Cache_Backend implements Zend_Cache_Backend_ExtendedInterface
{
    /**
     * Default Server Values
     */
    const DEFAULT_HOST = 'localhost';
    const DEFAULT_PORT =  8091;
    const DEFAULT_USER = 'admin';
    const DEFAULT_PASSWORD = 'password';
    const DEFAULT_BUCKET  = 'default';

    /**
     * Log message
     */
    const TAGS_UNSUPPORTED_BY_CLEAN_OF_COUCHBASE_BACKEND = 'Zend_Cache_Backend_Couchbase::clean() : tags are unsupported by the Couchbase backend';
    const TAGS_UNSUPPORTED_BY_SAVE_OF_COUCHBASE_BACKEND =  'Zend_Cache_Backend_Couchbase::save() : tags are unsupported by the Couchbase backend';

    /**
     * Available options
     *
     * @var array available options
     */
    protected $_options = array(
        'user'   => self::DEFAULT_USER,
        'password'   => self::DEFAULT_PASSWORD,
        'bucket' => self::DEFAULT_BUCKET,
        'servers' => array(array(
            'host'   => self::DEFAULT_HOST,
            'port'   => self::DEFAULT_PORT,
        )),
        'client' => array()
    );

    /**
     * Couchbase object
     *
     * @var mixed couchbase object
     */
    protected $_couchbase = null;

    /**
     * Constructor
     *
     * @param array $options associative array of options
     * @throws Zend_Cache_Exception
     * @return void
     */
    public function __construct(array $options = array())
    {
        if (!extension_loaded('couchbase')) {
            Zend_Cache::throwException('The couchbase extension must be loaded for using this backend !');
        }

        parent::__construct($options);

        if (isset($this->_options['servers'])) {
            $value = $this->_options['servers'];
            if (isset($value['host'])) {
                // in this case, $value seems to be a simple associative array (one server only)
                $value = array(0 => $value); // let's transform it into a classical array of associative arrays
            }
            $this->setOption('servers', $value);
        }
        
        // setup couchbase client options
        foreach ($this->_options['client'] as $name => $value) {
            $optId = null;
            if (is_int($name)) {
                $optId = $name;
            } else {
                $optConst = 'Couchbase::OPT_' . strtoupper($name);

                if (defined($optConst)) {
                    $optId = constant($optConst);
                } else {
                    $this->_log("Unknown couchbase client option '{$name}' ({$optConst})");
                }
            }
            if ($optId) {
                if (!$this->_couchbase->setOption($optId, $value)) {
                    $this->_log("Setting couchbase client option '{$optId}' failed");
                }
            }
        }

        // setup couchbase servers
        $password = (isset($this->_options['password'])) ? $this->_options['password'] : self::DEFAULT_PASSWORD;
        $username = (isset($this->_options['user'])) ? $this->_options['user'] : self::DEFAULT_USER;
        $bucket = (isset($this->_options['bucket'])) ? $this->_options['bucket'] : self::DEFAULT_BUCKET;
        $hosts = array();
        foreach ($this->_options['servers'] as $server) {
            if ($server['host']) {
                if (!array_key_exists('host', $server)) {
                    $server['host'] = self::DEFAULT_PORT;
                }
                if (!array_key_exists('port', $server)) {
                    $server['port'] = self::DEFAULT_PORT;
                }
                $hosts[] = $server['host'] . ":" . $server['port'];
            }
        }

        // Setting persistent flag explicitly just to make this more obvious to developers
        // apache may need to be restarted when couchbase cluster undergoes weird states
        // it defaults to TRUE in couchbase API anyway: http://www.couchbase.com/autodocs/couchbase-php-client-1.1.5/classes/Couchbase.html#method___construct
        $persistent = TRUE;
        
        // This initiates the connection with all the needed variables.
        $this->_couchbase = new \Couchbase($hosts, $username, $password, $bucket, $persistent);
    }

    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * @param  string  $id                     Cache id
     * @param  boolean $doNotTestCacheValidity If set to true, the cache validity won't be tested
     * @return string|false cached datas
     */
    public function load($id, $doNotTestCacheValidity = false)
    {
        $tmp = $this->_couchbase->get($id);
        if ($tmp) {
            return $tmp;
        }
        return false;
    }

    /**
     * Use the Couchbase getMulti method to retrieve multiple documents at once
     * http://www.couchbase.com/autodocs/couchbase-php-client-1.1.5/classes/Couchbase.html#method_getMulti
     *
     * @param array $ids Array of cache ID strings
     * @param  boolean $doNotTestCacheValidity If set to true, the cache validity won't be tested
     * @param array $cas Optional: an array to store the cas identifiers of the documents
     * @return array An array containing the documents
     */
    public function loadMultiple(array $ids, $doNotTestCacheValidity = false, $cas = array()) {
        return $this->_couchbase->getMulti($ids, $cas);
    }

    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param  string $id Cache id
     * @return bool whether the cache is available or not
     */
    public function test($id)
    {
        $tmp = $this->_couchbase->get($id);
        if ($tmp) {
            return TRUE;
        }
        return false;
    }

    /**
     * Save some string datas into a cache record
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param  string $data             Datas to cache
     * @param  string $id               Cache id
     * @param  array  $tags             Array of strings, the cache record will be tagged by each string entry
     * @param  mixed    $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @return boolean True if no problem
     */
    public function save($data, $id, $tags = array(), $specificLifetime = false)
    {
        $lifetime = $this->getLifetime($specificLifetime);

        // ZF-8856: using set because add needs a second request if item already exists
        $result = @$this->_couchbase->set($id, $data, $lifetime);
        if ($result === false) {
            $rsCode = $this->_couchbase->getResultCode();
            $rsMsg  = $this->_couchbase->getResultMessage();
            $this->_log("Couchbase::set() failed: [{$rsCode}] {$rsMsg}");
        }

        if (count($tags) > 0) {
            $this->_log(self::TAGS_UNSUPPORTED_BY_SAVE_OF_COUCHBASE_BACKEND);
        }

        return $result;
    }

    /**
     * Save multiple documents into cache records
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param array $documents Array of cache ID => document mappings for data that should be cached
     * @param  array  $tags This backend does not actually support $tags
     * @param mixed $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     */
    public function saveMultiple(array $documents, $tags = array(), $specificLifetime = false) {
        $lifetime = $this->getLifetime($specificLifetime);

        $result = @$this->_couchbase->setMulti($documents, $lifetime);

        if ($result === false) {
            $rsCode = $this->_couchbase->getResultCode();
            $rsMsg  = $this->_couchbase->getResultMessage();
            $this->_log("Couchbase::set() failed: [{$rsCode}] {$rsMsg}");
        }

        if (count($tags) > 0) {
            $this->_log(self::TAGS_UNSUPPORTED_BY_SAVE_OF_COUCHBASE_BACKEND);
        }

        return $result;
    }

    /**
     * Remove a cache record
     *
     * @param  string $id Cache id
     * @return boolean True if no problem
     */
    public function remove($id)
    {
        return $this->_couchbase->delete($id);
    }

    /**
     * Clean some cache records
     *
     * Available modes are :
     * 'all' (default)  => remove all cache entries ($tags is not used)
     * 'old'            => unsupported
     * 'matchingTag'    => unsupported
     * 'notMatchingTag' => unsupported
     * 'matchingAnyTag' => unsupported
     *
     * @param  string $mode Clean mode
     * @param  array  $tags Array of tags
     * @throws Zend_Cache_Exception
     * @return boolean True if no problem
     */
    public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array())
    {
        switch ($mode) {
            case Zend_Cache::CLEANING_MODE_ALL:
                return $this->_couchbase->flush();
                break;
            case Zend_Cache::CLEANING_MODE_OLD:
                $this->_log("Zend_Cache_Backend_Couchbase::clean() : CLEANING_MODE_OLD is unsupported by the Couchbase backend");
                break;
            case Zend_Cache::CLEANING_MODE_MATCHING_TAG:
            case Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG:
            case Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG:
                $this->_log(self::TAGS_UNSUPPORTED_BY_CLEAN_OF_COUCHBASE_BACKEND);
                break;
               default:
                Zend_Cache::throwException('Invalid mode for clean() method');
                   break;
        }
    }

    /**
     * Return true if the automatic cleaning is available for the backend
     *
     * @return boolean
     */
    public function isAutomaticCleaningAvailable()
    {
        return false;
    }

    /**
     * Set the frontend directives
     *
     * @param  array $directives Assoc of directives
     * @throws Zend_Cache_Exception
     * @return void
     */
    public function setDirectives($directives)
    {
        parent::setDirectives($directives);
        $lifetime = $this->getLifetime(false);
        if ($lifetime > 2592000) {
            // #ZF-3490 : For the couchbase backend, there is a lifetime limit of 30 days (2592000 seconds)
            $this->_log('couchbase backend has a limit of 30 days (2592000 seconds) for the lifetime');
        }
        if ($lifetime === null) {
            // #ZF-4614 : we tranform null to zero to get the maximal lifetime
            parent::setDirectives(array('lifetime' => 0));
        }
    }

    /**
     * Return an array of stored cache ids
     *
     * @return array array of stored cache ids (string)
     */
    public function getIds()
    {
        $this->_log("Zend_Cache_Backend_Couchbase::save() : getting the list of cache ids is unsupported by the Couchbase backend");
        return array();
    }

    /**
     * Return an array of stored tags
     *
     * @return array array of stored tags (string)
     */
    public function getTags()
    {
        $this->_log(self::TAGS_UNSUPPORTED_BY_SAVE_OF_COUCHBASE_BACKEND);
        return array();
    }

    /**
     * Return an array of stored cache ids which match given tags
     *
     * In case of multiple tags, a logical AND is made between tags
     *
     * @param array $tags array of tags
     * @return array array of matching cache ids (string)
     */
    public function getIdsMatchingTags($tags = array())
    {
        $this->_log(self::TAGS_UNSUPPORTED_BY_SAVE_OF_COUCHBASE_BACKEND);
        return array();
    }

    /**
     * Return an array of stored cache ids which don't match given tags
     *
     * In case of multiple tags, a logical OR is made between tags
     *
     * @param array $tags array of tags
     * @return array array of not matching cache ids (string)
     */
    public function getIdsNotMatchingTags($tags = array())
    {
        $this->_log(self::TAGS_UNSUPPORTED_BY_SAVE_OF_COUCHBASE_BACKEND);
        return array();
    }

    /**
     * Return an array of stored cache ids which match any given tags
     *
     * In case of multiple tags, a logical AND is made between tags
     *
     * @param array $tags array of tags
     * @return array array of any matching cache ids (string)
     */
    public function getIdsMatchingAnyTags($tags = array())
    {
        $this->_log(self::TAGS_UNSUPPORTED_BY_SAVE_OF_COUCHBASE_BACKEND);
        return array();
    }

    /**
     * Return the filling percentage of the backend storage
     *
     * @throws Zend_Cache_Exception
     * @return int integer between 0 and 100
     */
    public function getFillingPercentage()
    {
        $mems = $this->_couchbase->getStats();
        if ($mems === false) {
            return 0;
        }

        $memSize = null;
        $memUsed = null;
        foreach ($mems as $key => $mem) {
            if ($mem === false) {
                $this->_log('can\'t get stat from ' . $key);
                continue;
            }

            $eachSize = $mem['limit_maxbytes'];
            $eachUsed = $mem['bytes'];
            if ($eachUsed > $eachSize) {
                $eachUsed = $eachSize;
            }

            $memSize += $eachSize;
            $memUsed += $eachUsed;
        }

        if ($memSize === null || $memUsed === null) {
            Zend_Cache::throwException('Can\'t get filling percentage');
        }

        return ((int) (100. * ($memUsed / $memSize)));
    }

    /**
     * Couchbase Does not support retrieving metadata directly for a key
     * If we need this later we'll have to implement couchbase views to get it
     *
     * @param string $id cache id
     * @return boolean ALWAYS RETURNS FALSE
     */
    public function getMetadatas($id)
    {

        return false;
    }

    /**
     * Give (if possible) an extra lifetime to the given cache id
     *
     * @param string $id cache id
     * @param int $lifetime New TTL/lifetime/expiry value
     * @return boolean true if ok
     */
    public function touch($id, $lifetime)
    {
        $tmp = $this->_couchbase->touch($id, $lifetime);

        return $tmp;
    }

    /**
     * Return an associative array of capabilities (booleans) of the backend
     *
     * The array must include these keys :
     * - automatic_cleaning (is automating cleaning necessary)
     * - tags (are tags supported)
     * - expired_read (is it possible to read expired cache records
     *                 (for doNotTestCacheValidity option for example))
     * - priority does the backend deal with priority when saving
     * - infinite_lifetime (is infinite lifetime can work with this backend)
     * - get_list (is it possible to get the list of cache ids and the complete list of tags)
     *
     * @return array associative of with capabilities
     */
    public function getCapabilities()
    {
        return array(
            'automatic_cleaning' => false,
            'tags' => false,
            'expired_read' => false,
            'priority' => false,
            'infinite_lifetime' => false,
            'get_list' => false,
            'loadMultiple' => true,
            'saveMultiple' => true
        );
    }

}
