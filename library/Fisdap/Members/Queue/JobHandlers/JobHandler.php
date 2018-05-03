<?php namespace Fisdap\Members\Queue\JobHandlers;

use Doctrine\ORM\EntityManager;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Jobs\RedisJob;
use Zend_Cache_Core;
use Zend_Cache_Manager;
use Zend_Db_Adapter_Abstract;


/**
 * Template for a job handler
 *
 * @package Fisdap\Members\Queue\JobHandlers
 * @author  jmortenson
 * @author  smcintyre
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
abstract class JobHandler implements HandlesJob
{
    use JobHandlerLoggingHelpers;


    /**
     * @var Job|RedisJob
     */
    protected $job;

    /**
     * @var Zend_Cache_Core
     */
    protected $cache;

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $db;

    /**
     * @var EntityManager
     */
    protected $em;


    public function __construct() {
        // get the cache
        /** @var Zend_Cache_Manager $cacheManager */
        $cacheManager = \Zend_Registry::get('zendCacheManager');
        $this->cache = $cacheManager->getCache('default');

        // get the db
        $this->db = \Zend_Registry::get('db');

        // get the loggers
        $this->logger = \Zend_Registry::get('logger');

        // get entity manager
        $this->em = \Fisdap\EntityUtils::getEntityManager();
    }


    /**
     * Make sure we still have database connections open and active
     */
    protected function reopenDBConnections() {

        if ( ! isset($this->em)) {
            $this->em = \Fisdap\EntityUtils::getEntityManager();
        }

        // Check Doctrine DB connection
        $conn = $this->em->getConnection();

        try {
            $conn->executeQuery('SELECT 1');
        } catch (\Exception $e) {
            // refresh Doctrine connection
            $this->em->getConnection()->close();
            $this->em->getConnection()->connect();
        }

        // Check Zend DB connection
        try {
            $this->db->query('SELECT 1');
        } catch (\Exception $e) {
            // refresh Zend connection
            $this->db->closeConnection();
            $this->db->getConnection();
            \Zend_Registry::set('db', $this->db);
        }
    }
}