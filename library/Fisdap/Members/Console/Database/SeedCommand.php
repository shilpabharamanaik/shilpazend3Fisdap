<?php namespace Fisdap\Members\Console\Database;

use Codeception\Module\LoadAliceFixture;
use DirectoryIterator;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Illuminate\Console\Command;
use Nelmio\Alice\Fixtures;
use Zend_Registry;
use Doctrine\Common\DataFixtures\Loader as DoctrineLoader;
use Nelmio\Alice\Fixtures\Loader as AliceLoader;


/**
 * Class SeedCommand
 *
 * @package Fisdap\Members\Console\Database
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class SeedCommand extends Command
{
    protected $name = 'db:seed';

    protected $description = 'Seed a (local) development or testing database';


    public function fire()
    {
        if ( ! preg_match('/development|testing/', APPLICATION_ENV)) {
            $this->error("This script can only be executed in the 'development' or 'testing' environment");
            exit(1);
        }

        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = Zend_Registry::get('doctrine')->getEntityManager();

        // validate database connection
        $connectionHost = $em->getConnection()->getHost();
        if ( ! preg_match('/^localhost$|^fisdapdb$/', $connectionHost)) {
            $this->error('This script can only be used with a local or in memory database.  Please check your Doctrine/environment configuration and try again.');
            exit(1);
        }


        // load fixtures
        $aliceLoader = new AliceLoader;

        $fixtures = $aliceLoader->load(base_path('/vendor/fisdap/members-api/database/seeds/fixtures/') . 'UserContexts.yml' );

        //// enumerated data
        $enumsDir = new DirectoryIterator(base_path('/vendor/fisdap/members-api/database/seeds/fixtures/enums'));

        foreach ($enumsDir as $fileinfo) {
            if (!$fileinfo->isDot() ) {
                $filename = $fileinfo->getRealPath();
                $enumFixtures = $aliceLoader->load($filename);
                $fixtures = array_merge($fixtures, $enumFixtures);
            }
        }

        //// requirements-related data
        $reqFixtures = $aliceLoader->load(base_path('/vendor/fisdap/members-api/database/seeds/fixtures/requirements/') . 'Requirement.yml');
        $fixtures = array_merge($fixtures, $reqFixtures);

        $reqHisChgFixtures = $aliceLoader->load(base_path('/vendor/fisdap/members-api/database/seeds/fixtures/requirements/') . 'RequirementHistoryChange.yml');
        $fixtures = array_merge($fixtures, $reqHisChgFixtures);

        //// reports-related date
        $repFixtures = $aliceLoader->load(base_path('/vendor/fisdap/members-api/database/seeds/fixtures/reports/') . 'Reports.yml');
        $fixtures = array_merge($fixtures, $repFixtures);

        // seed
        $this->info('Deleting previously seeded data and persisting new data...');

        $doctrineLoader = new DoctrineLoader;
        $doctrineLoader->addFixture(new LoadAliceFixture($fixtures));

        $purger = new ORMPurger();
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_DELETE);

        $executor = new ORMExecutor($em, $purger);

//        $logger = $this->logger;
//        $executor->setLogger(
//            function ($message) use ($logger) {
//                $logger->debug($message);
//            }
//        );

        $executor->execute($doctrineLoader->getFixtures());
    }
}