<?php namespace Fisdap\Members\Console\Database;

use Doctrine\ORM\Tools\SchemaTool;
use Illuminate\Console\Command;
use PDO;
use Zend_Registry;


/**
 * Class InitCommand
 *
 * @package Fisdap\Members\Console\Database
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class InitCommand extends Command
{
    protected $name = 'db:init';

    protected $description = 'Create/initialize a local development database';


    public function fire()
    {
        if ( ! preg_match('/development|testing/', APPLICATION_ENV)) {
            echo "This script can only be executed in the 'development' or 'testing' environment";
            exit(1);
        }

        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = Zend_Registry::get('doctrine')->getEntityManager();

        // validate database connection
        $connectionHost = $em->getConnection()->getHost();
        if ( ! preg_match('/^localhost$|^fisdapdb$/', $connectionHost)) {
            echo 'This script can only be used with a local or in memory database.  Please check your Doctrine/environment configuration and try again.';
            exit(1);
        }

        // make a separate PDO connection without the database name, so it can be recreated
        $connection = $em->getConnection();

        $dsn = $connection->getDatabasePlatform()->getName() . ':host=' . $connection->getHost()
            . ($connection->getPort() ? ';port=' . $connection->getPort() : null);

        $pdo = new PDO($dsn, $connection->getUsername(), $connection->getPassword());
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $dbName = $connection->getDatabase();

        echo "Creating database named '$dbName'..." . PHP_EOL;
        $pdo->query("DROP DATABASE IF EXISTS $dbName");
        $pdo->query("CREATE DATABASE $dbName");

        // reconnect EntityManager connection to prevent "database does not exist" error
        $connection->close();
        $connection->connect();

        echo "Adding tables to '$dbName' database..." . PHP_EOL;
        $schemaTool = new SchemaTool($em);

        $schemaTool->dropDatabase();

        $allEntityMetadata = $em->getMetadataFactory()->getAllMetadata();

        $schemaTool->createSchema($allEntityMetadata);
    }
}