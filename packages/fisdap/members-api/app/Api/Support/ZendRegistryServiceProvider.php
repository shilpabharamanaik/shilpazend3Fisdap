<?php namespace Fisdap\Api\Support;

use Doctrine\ORM\EntityManager;
use Illuminate\Support\ServiceProvider;
use Zend_Registry;


/**
 * Mimics behavior of ZF1-Doctrine2 integration ("Bisna")
 * @codeCoverageIgnore
 */
class DoctrineContainer
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager() {
        return $this->em;
    }
}


/**
 * Provides compatibility with Zend Framework 1 ("Members") application and legacy classes such as Fisdap\EntityUtils
 *
 * @package Fisdap\Api\Support
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
class ZendRegistryServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot()
    {
        /*
         * Support EntityBaseClass dependence on EntityManager stored in Zend Registry
         *
         * This MUST always be set explicitly, otherwise integration tests will fail due to duplicate
         * EntityManagers.
         */
        Zend_Registry::set('doctrine', new DoctrineContainer($this->app->make(EntityManager::class)));

        Zend_Registry::set('logger', \Log::getMonolog());
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
    }
}