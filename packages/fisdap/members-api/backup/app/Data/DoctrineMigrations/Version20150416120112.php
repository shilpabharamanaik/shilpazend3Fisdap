<?php

namespace Fisdap\Data\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;


/**
 * Migrates a table for Laravel queue job failures
 *
 * @package Fisdap\Data\DoctrineMigrations
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class Version20150416120112 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $table = $schema->createTable('FailedJobs');
        $table->addColumn('id', 'integer')->setAutoincrement(true);
        $table->addColumn('connection', 'text');
        $table->addColumn('queue', 'text');
        $table->addColumn('payload', 'text');
        $table->addColumn('failed_at', 'datetime');
        $table->setPrimaryKey(['id']);
    }


    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('FailedJobs');
    }
}
