<?php
/**
 * Created by PhpStorm.
 * User: jmortenson
 * Date: 8/26/14
 * Time: 3:56 PM
 */

/**
 * Extending Zend Framework's Zend_Cache Core.php
 */
class Fisdap_Cache_Core extends Zend_Cache_Core
{

    /**
     * Save multiple documents to the cache backend in one call, if supported
     *
     * @param array $documents Array of cache ID => document mappings for data that should be cached
     * @param array $tags Array of strings, the cache record will be tagged by each string entry
     * @param bool $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @param int $priority integer between 0 (very low priority) and 10 (maximum priority) used by some particular backends
     * @return bool True if no problem
     * @throws Zend_Cache_Exception
     */
    public function saveMultiple(array $documents, $tags = array(), $specificLifetime = false, $priority = 8)
    {
        if (!$this->_options['caching']) {
            return true;
        }

        // if the backend does not support saving multiple, then just iterate to do it the old way
        if (!isset($this->_backendCapabilities['saveMultiple']) || !$this->_backendCapabilities['saveMultiple']) {
            $allSuccess = true;
            foreach ($documents as $id => $data) {
                $result = $this->save($data, $id, $tags, $specificLifetime, $priority);
                if (!$result) {
                    $allSuccess = false;
                }
            }

            return $allSuccess;
        } else {
            // actually go ahead and use the saveMultiple method on the backend

            self::_validateTagsArray($tags);

            // check IDs and serialize data (if necessary)
            foreach ($documents as $id => $data) {
                self::_validateIdOrTag($id);
                if ($this->_options['automatic_serialization']) {
                    // we need to serialize datas before storing them
                    $documents[$id] = serialize($data);
                } else {
                    if (!is_string($data)) {
                        Zend_Cache::throwException("Datas must be string or set automatic_serialization = true");
                    }
                }
            }

            // automatic cleaning
            if ($this->_options['automatic_cleaning_factor'] > 0) {
                $rand = rand(1, $this->_options['automatic_cleaning_factor']);
                if ($rand == 1) {
                    //  new way                 || deprecated way
                    if ($this->_extendedBackend || method_exists($this->_backend, 'isAutomaticCleaningAvailable')) {
                        $this->_log("Zend_Cache_Core::save(): automatic cleaning running", 7);
                        $this->clean(Zend_Cache::CLEANING_MODE_OLD);
                    } else {
                        $this->_log("Zend_Cache_Core::save(): automatic cleaning is not available/necessary with current backend", 4);
                    }
                }
            }

            $this->_log("Zend_Cache_Core: save multiple items: " . implode(', ', array_keys($documents)), 7);
            if ($this->_options['ignore_user_abort']) {
                $abort = ignore_user_abort(true);
            }
            if (($this->_extendedBackend) && ($this->_backendCapabilities['priority'])) {
                $result = $this->_backend->saveMultiple($documents, $tags, $specificLifetime, $priority);
            } else {
                $result = $this->_backend->saveMultiple($documents, $tags, $specificLifetime);
            }
            if ($this->_options['ignore_user_abort']) {
                ignore_user_abort($abort);
            }

            if (!$result) {
                return false;
            }
        }

        return true;
    }


    /**
     * @param $ids Array of cache IDs for documents you want to retrieve
     * @param bool $doNotTestCacheValidity If set to true, the cache validity won't be tested
     * @param bool $doNotUnserialize  Do not serialize (even if automatic_serialization is true) => for internal use
     * @return array Array of id => document results
     */
    public function loadMultiple($ids, $doNotTestCacheValidity = false, $doNotUnserialize = false)
    {
        // if the backend does not support saving multiple, then just iterate to do it the old way
        if (!isset($this->_backendCapabilities['loadMultiple']) || !$this->_backendCapabilities['loadMultiple']) {
            $data = array();
            foreach ($ids as $id) {
                $data[$id] = $this->load($id, $doNotTestCacheValidity, $doNotUnserialize);
            }
        } else {
            if (!$this->_options['caching']) {
                return false;
            }

            foreach ($ids as $key => $id) {
                $ids[$key] = $this->_id($id); // cache id may need prefix

                self::_validateIdOrTag($id);
            }

            $this->_log("Zend_Cache_Core: load items: " . implode(', ', $ids), 7);
            $data = $this->_backend->loadMultiple($ids, $doNotTestCacheValidity);
            if ($data === false) {
                // no cache available
                return false;
            }
            if ((!$doNotUnserialize) && $this->_options['automatic_serialization']) {
                // we need to unserialize before sending the result
                foreach ($data as $key => $value) {
                    if ($value) {
                        $data[$key] = unserialize($value);
                    }
                }
            }
        }

        return $data;
    }
}
