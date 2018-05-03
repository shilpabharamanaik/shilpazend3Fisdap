<?php namespace Fisdap\Members\Queue;

use Doctrine\ORM\EntityManager;

/**
 * Class RefreshesDatabaseConnection
 *
 * @package Fisdap\Members\Queue
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait RefreshesDatabaseConnection
{
    /**
     * @var EntityManager
     */
    protected $entityManager;
    
    
    protected function refreshDbConnection()
    {
        // Check Doctrine DB connection
        $conn = $this->entityManager->getConnection();

        try {
            $conn->executeQuery('SELECT 1');
        } catch (\Exception $e) {
            // refresh Doctrine connection
            $this->entityManager->getConnection()->close();
            $this->entityManager->getConnection()->connect();
        }
    }
}