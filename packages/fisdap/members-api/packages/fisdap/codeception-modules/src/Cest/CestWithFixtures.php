<?php

use Nelmio\Alice\Fixtures;


/**
 * Class CestWithFixtures
 *
 * Provides support for seeding with Alice fixtures
 *
 * @author bgetsug
 */
abstract class CestWithFixtures
{
    /**
     * @var string Fully-qualified Entity class name
     */
    protected static $defaultEntityName = null;

    /**
     * @var array
     */
    protected static $defaultFixtureFilenames = null;

    /**
     * @var \Doctrine\ORM\EntityRepository;
     */
    protected $repo;


    /**
     * @var array
     */
    protected $defaultFixtures = [];


    /**
     * @throws Exception
     */
    public function __construct()
    {
        if (is_null(static::$defaultEntityName)) throw new \Exception("The 'defaultEntityName' property must be set on the Cest");
        if (is_null(static::$defaultFixtureFilenames)) throw new \Exception("The 'defaultFixtureFilenames' property must be set on the Cest");
    }


    /**
     * @param ApiTester|FunctionalTester $I
     */
    public function _before($I)
    {
        $this->repo = $I->getRepository(static::$defaultEntityName);
        $this->defaultFixtures = $I->loadFixtures(static::$defaultFixtureFilenames);
        $I->seed($this->defaultFixtures);
    }


    protected function validateJsonResponse(ApiTester $I, $responseCode = 200)
    {
        $I->seeResponseCodeIs($responseCode);
        $I->seeHttpHeader('Content-type', 'application/json');
        $I->seeResponseIsJson();
    }


    protected function dumpJsonResponse(ApiTester $I)
    {
        $response = json_decode($I->grabResponse(), true);
        die(PHP_EOL . var_export($response) . PHP_EOL);
    }
} 