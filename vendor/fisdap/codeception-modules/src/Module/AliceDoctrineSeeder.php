<?php namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Module;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Nelmio\Alice\Loader\Yaml as YamlLoader;


/**
 * Adds support for the AliceDoctrineSeeder fixture library.
 *
 * ## Config
 * * fixturePath: `string`, default `tests/fixtures` - path to Alice fixtures, relative to the project root as determined by Codeception
 *
 *  ### Example (`functional.suite.yml`)
 *
 *      modules:
 *         enabled: [AliceDoctrineSeeder]
 *         config:
 *            AliceDoctrineSeeder:
 *               fixturePath: tests/fixtures
 *
 * @author bgetsug
 */
class AliceDoctrineSeeder extends Module
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var YamlLoader
     */
    public $fixtureLoader;

    /**
     * @var EntityManager
     */
    public static $em;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var SchemaTool
     */
    protected $schemaTool;

    /**
     * @var array
     */
    public $allEntityMetadata;


    public function __construct($config = null)
    {
        $this->config = array_merge(
            array(
                'fixturePath' => 'tests/fixtures',
            ),
            (array)$config
        );

        parent::__construct();
    }


    public function _initialize()
    {
        $this->fixtureLoader = new YamlLoader();
    }


    public function _beforeSuite()
    {
        if (! $this->hasModule('Laravel4')) {
            throw new \Exception('Laravel4 module unavailable');
        }

        /** @var Laravel4 $laravel */
        $laravel = $this->getModule('Laravel4');

        $laravel->app->boot();

        if (! $laravel->app->bound('doctrine')) {
            throw new \Exception("'doctrine' not found in Laravel IoC/Service Container");
        }

        self::$em = $laravel->grabService('doctrine');

        $this->logger = $laravel->grabService('log');

        self::$em->clear();

        $this->schemaTool = new SchemaTool(self::$em);

        $this->dropDatabase();
        $this->createSchema();
    }


    public function _before()
    {
        /** @var Laravel4 $laravel */
        $laravel = $this->getModule('Laravel4');

        $laravel->app->instance('doctrine', self::$em);

        self::$em->getConnection()->close();
        self::$em->getConnection()->connect();
    }


    protected function createSchema()
    {
        echo 'Creating schema...' . PHP_EOL;

        $allEntityMetadata = self::$em->getMetadataFactory()->getAllMetadata();

        $this->schemaTool->createSchema($allEntityMetadata);

        $tables = self::$em->getConnection()->getSchemaManager()->listTables();

        foreach ($tables as $table) {
            $this->logger->debug('Created table named: ' . $table->getName());
        }
    }


    protected function dropDatabase()
    {
        echo 'Dropping schema...' . PHP_EOL;

        $this->schemaTool->dropDatabase();
    }

    protected function getFixturePath()
    {
        return Configuration::projectDir() . $this->config['fixturePath'] . DIRECTORY_SEPARATOR;
    }


    /**
     * @param string|array $fixtureFilenames
     *
     * @return array
     * @throws \Exception
     */
    public function loadFixtures($fixtureFilenames)
    {
        // prepend fixture directory to filename(s)
        $fixturePath = $this->getFixturePath();

        if (is_array($fixtureFilenames)) {
            array_walk($fixtureFilenames, function(&$fixtureFilename) use ($fixturePath) {
                $fixtureFilename = $fixturePath . $fixtureFilename;
            });
        } else {
            $fixtureFilenames = [$fixturePath . $fixtureFilenames];
        }

        // concatenate fixture files together for loading as one
        $tempFixturesFilename = tempnam('/tmp', 'fixtures');

        foreach ($fixtureFilenames as $fixtureFilename) {
            file_put_contents($tempFixturesFilename, PHP_EOL . PHP_EOL . file_get_contents($fixtureFilename), FILE_APPEND);
        }

        $fixtures = $this->fixtureLoader->load($tempFixturesFilename, self::$em);

        unlink($tempFixturesFilename);

        return $fixtures;
    }


    /**
     * @param array $fixtures
     */
    public function seed(array $fixtures)
    {
        $loader = new Loader;
        $loader->addFixture(new LoadAliceFixture($fixtures));

        $purger = new ORMPurger();

        $executor = new ORMExecutor(self::$em, $purger);

        $logger = $this->logger;
        $executor->setLogger(function($message) use ($logger) {
            $logger->debug($message);
        });

        $executor->execute($loader->getFixtures());
    }


    /**
     * @param $entityName
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository($entityName)
    {
        return self::$em->getRepository($entityName);
    }


    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return self::$em;
    }
}