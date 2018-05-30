<?php

namespace Fisdap\Doctrine\Extensions\DoctrineCommand;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\ORM;
use Doctrine\DBAL\Types\Type;
use Fisdap\Doctrine\Extensions\ColumnType\UuidType;

/**
 * Task for fill UUID columns with generated UUID values
 *
 * @author Jesse Mortenson <jmortenson@fisdap.net>
 */
class FillUuidsCommand extends Command
{
    use EntityModificationUtilities;

    /**
     * @var array Array of target entities for filling UUID data
     */
    protected $targetEntities;

    /**
     * @var string Name of the property that needs data filled with UUIDs.
     */
    protected $propertyName = 'uuid';

    /**
     * @var int How many records should this tool attempt to update with new UUID values at a time?
     */
    protected $batchSize = 1000;

    /**
     * @var bool Should we find and fill columns that represent doctrine relationships to the target column?
     */
    protected $doRelationships = false;

    /**
     * @var array Array of entity classes as keys, values are either NULL or the metadata, if loaded
     */
    protected $allMetadata = [];

    /**
     * @var object Doctrine entity manager
     */
    protected $em;

    /**
     * @var object DB Connection object from the entity manager
     */
    protected $dbConnection;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('doctrine-extensions:fill-uuids')
            ->setDescription('Fills existing UUID columns with generated UUID values')
            ->setDefinition(array(
                new InputArgument('property-name', InputArgument::OPTIONAL, 'The name of the property on the entity that represents the column that should be filled, ie: uuid'),
                new InputArgument('entities', InputArgument::IS_ARRAY, 'List of full entity names in quotes, ie: "Fisdap\\Entity\\ShiftLegacy".'),
            ))
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Set the number of records the tool should attempt to update at any one time')
            ->addOption('do-relationships', null, InputOption::VALUE_NONE, 'Should we find and fill columns that represent doctrine relationships to the target column?')
            ->setHelp(
                <<<EOT
Fills existing UUID columns on entities with generated UUID values
EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // ARGUMENT CHECKS
        // check if we have necessary arguments
        if (($this->targetEntities = $input->getArgument('entities')) === null) {
            throw new \RuntimeException("Argument 'entities' is required in order to execute this command correctly.");
        }
        if ($input->getArgument('property-name') !== null) {
            $this->propertyName = $input->getArgument('property-name');
        }
        if ($input->getOption('batch-size') !== null && intval($input->getOption('batch-size')) > 0) {
            $this->batchSize = intval($input->getOption('batch-size'));
        }
        if ($input->getOption('do-relationships') !== null) {
            $this->doRelationships = true;
        }

        // CONSTRUCTION STUFF
        // Get the entity manager and available entity class names
        $this->em = $this->getHelper('em')->getEntityManager();

        // Get metadata for each target entity
        foreach ($this->targetEntities as $class) {
            $this->loadMetaDataForEntityClass($class);
        }

        // load the database connection (really only supporting MySQL in this tool)
        $this->dbConnection = $this->em->getConnection();
        $this->dbConnection->exec("SET SESSION wait_timeout = 28800");


        // DO WORK
        foreach ($this->targetEntities as $entityClass) {
            // Get table metadata
            $identityInfo = $this->getEntityIdentityInfo($entityClass);
            $tableName = $this->allMetadata[$entityClass]->getTableName();
            $uuidColumnName = $this->getPropertyColumnName($this->propertyName, $entityClass);

            // generate new UUIDs for the targeted column.
            $rowsAffectedTotal = $this->generateUuidsForColumn($tableName, $uuidColumnName, $identityInfo['idColumnName']);

            // Do relationships on that column?
            $relatedAffected = 0;
            if ($this->doRelationships) {
                // find all associations (list-associations command)
                $idPropertyName = $identityInfo['idPropertyName'];
                $relatedEntities = $this->getAssociationsOnEntityProperty($entityClass, $idPropertyName);

                foreach ($relatedEntities as $relatedEntityClass => $properties) {
                    foreach ($properties as $property) {
                        $relatedAffected += $this->fillRelatedEntityProperties($relatedEntityClass, $property, $tableName, $identityInfo, $uuidColumnName);
                    }
                }
            }

            // Write output
            $output->writeln($rowsAffectedTotal . ' rows updated');
            if ($this->doRelationships) {
                $output->writeln($relatedAffected . ' related table rows updated');
            }
        }
    }

    /**
     * Load the metadata for an entity class, if missing. Adds to $this->allMetaData
     *
     * @param $entityClass string full class name for the entity
     */
    private function loadMetaDataForEntityClass($entityClass)
    {
        if (!isset($this->allMetadata[$entityClass])) {
            $this->allMetadata[$entityClass] = $this->em->getClassMetadata($entityClass);
        }
    }


    /**
     * Generate new UUIDs and fill a column with them
     *
     * @param $tableName string Name of the target table
     * @param $uuidColumnName string Name of the uuid column we are going to fill
     * @param $idColumnName string Name of the existing identity column on the table, for ordering purposes
     * @return int Number of rows updated in the database
     */
    protected function generateUuidsForColumn($tableName, $uuidColumnName, $idColumnName)
    {
        // Drop indexes on the column
        $indexes = $this->dropIndexesOnColumn($tableName, $uuidColumnName);

        // Get the system node for UUID generation
        $node = self::getNodeFromSystem();

        // select a chunk of NULL data, ordered by ID
        $keepGoing = true;
        $rowsAffectedTotal = 0;
        while ($keepGoing) {
            $keepGoing = false; // set this to false in case no results from our select query
            $selectQuery = $this->dbConnection->executeQuery(
                "SELECT {$idColumnName} FROM {$tableName}
                      WHERE {$uuidColumnName} IS NULL
                      ORDER BY {$idColumnName} ASC
                      LIMIT :batchSize",
                array(
                    ':batchSize' => $this->batchSize,
                ),
                array(
                    ':batchSize' => 'integer',
                )
            );
            // Add UUIDs for that chunk
            // start transaction
            //$this->dbConnection->beginTransaction();
            $batchStartId = $batchStartTime = null;
            $rowsAffectedBatch = 0;
            $batchCaseStatement = '';
            $batchParams = $batchParamTypes = [];
            $batchIds = [];
            while ($id = $selectQuery->fetchColumn(0)) {
                // We got results, so the next iteration of the loop should continue
                $keepGoing = true;

                if ($batchStartId == null) {
                    // set the ID that this batch started at, and the current time
                    $batchStartId = $id;
                    $batchStartTime = time();

                    // report some info
                    fwrite(STDOUT, 'Batch started at ID: ' . $id
                        . ' at ' . date('g:i', $batchStartTime)
                        . PHP_EOL);
                }

                // add to CASE statement
                $batchCaseStatement .= " WHEN {$idColumnName} = {$id} THEN :uuid{$id}";
                $batchParams[":uuid{$id}"] = pack('H*', str_replace('-', '', UuidType::generateUuid($node)));
                $batchParamTypes[":uuid{$id}"] = 'binary';
                $batchIds[] = $id;
            }

            if (count($batchIds) > 0) {
                //$this->dbConnection->connect(); // make sure we are still connected
                $updateSql = "UPDATE {$tableName} SET {$uuidColumnName} = CASE{$batchCaseStatement} END
                WHERE {$idColumnName} IN (" . implode(',', $batchIds) . ")";
                //fwrite(STDOUT, $updateSql . PHP_EOL . 'params: ' . PHP_EOL . print_r($batchParams, TRUE) . PHP_EOL);
                $affected = $this->dbConnection->executeUpdate($updateSql, $batchParams, $batchParamTypes);

                $rowsAffectedTotal += $affected;
                $rowsAffectedBatch += $affected;

                // commit transaction
                //$this->dbConnection->commit();

                // output info on batch
                $batchDuration = (time() - $batchStartTime);
                if ($batchDuration == 0) {
                    fwrite(STDOUT, 'Batch completed at ID: ' . $id
                        . ' at ' . date('g:i', time())
                        . '. Completed in less than one second!'
                        . PHP_EOL);
                } else {
                    fwrite(STDOUT, 'Batch completed at ID: ' . $id
                        . ' at ' . date('g:i', time())
                        . '. Completed batch of ' . $rowsAffectedBatch . ' at rate of '
                        . ($rowsAffectedBatch / ($batchDuration / 60)) . ' per minute'
                        . PHP_EOL);
                }
            }
        }

        // Add index back to that column
        $this->restoreIndexes($tableName, $indexes);

        return $rowsAffectedTotal;
    }

    /**
     * @param $tableName
     * @param $uuidColumnName
     * @return array $indexes array of indexes that were dropped
     */
    private function dropIndexesOnColumn($tableName, $uuidColumnName)
    {
        // Drop index on the column
        $indexes = $this->dbConnection->fetchAll(
            "SHOW INDEX FROM {$tableName} WHERE Column_name = :uuidColumnName",
            array(
                ':uuidColumnName' => $uuidColumnName
            ),
            array(
                ':uuidColumnName' => 'string'
            )
        );
        foreach ($indexes as $index) {
            // @todo use pt-online-schema-change
            $this->dbConnection->executeQuery("DROP INDEX {$index['Key_name']} ON {$tableName}");
        }
        return $indexes;
    }

    /**
     * @param $tableName
     * @param $indexes array Array of indexes to restore (output from $this->dropIndexesOnColumn)
     */
    private function restoreIndexes($tableName, $indexes)
    {
        foreach ($indexes as $index) {
            if ($index['Non_unique']) {
                $uniqueString = '';
            } else {
                $uniqueString = ' UNIQUE ';
            }
            // @todo use pt-online-schema-change
            $this->dbConnection->executeQuery("CREATE {$uniqueString} INDEX {$index['Key_name']} ON {$tableName} ({$index['Column_name']})");
        }
    }

    /**
     * @param $entityClass
     * @param $idPropertyName
     * @return array
     * @throws \Exception
     */
    protected function getAssociationsOnEntityProperty($entityClass, $idPropertyName)
    {
        // Run the generate migration command (\Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand())
        $listAssociationsCommand = $this->getApplication()->find('doctrine-extensions:list-associations');
        $listAssociationsCommandOutput = new BufferedOutput();
        $listAssociationsCommandInput = new ArrayInput(array(
            'command' => 'doctrine-extensions:list-associations',
            'entity' => $entityClass,
            'property' => $idPropertyName,
        ));
        $resultCode = $listAssociationsCommand->run($listAssociationsCommandInput, $listAssociationsCommandOutput);
        $relatedEntities = [];
        if ($resultCode == 0) {
            $listAssociationsCommandOutput = json_decode($listAssociationsCommandOutput->fetch());
            foreach ($listAssociationsCommandOutput->onEntity as $relatedEntity => $properties) {
                $relatedEntities[$relatedEntity] = $properties;
            }
            foreach ($listAssociationsCommandOutput->onOthers as $relatedEntity => $properties) {
                $relatedEntities[$relatedEntity] = $properties;
            }
        }

        return $relatedEntities;
    }

    /**
     * @param $relatedTableName
     * @param $tableName
     * @param $propertyColumnName
     * @param $identityInfo
     * @param $relatedUuidColumnName
     * @param $uuidColumnName
     * @return mixed
     */
    private function runRelationshipUpdateSql($relatedTableName, $tableName, $propertyColumnName, $identityInfo, $relatedUuidColumnName, $uuidColumnName)
    {
        $sql = "UPDATE {$relatedTableName} related
                        INNER JOIN {$tableName} target ON related.{$propertyColumnName} = target.{$identityInfo['idColumnName']}
                        SET related.{$relatedUuidColumnName} = target.{$uuidColumnName}
                        WHERE related.{$relatedUuidColumnName} IS NULL";

        //fwrite(STDOUT, $sql . PHP_EOL);
        return $this->dbConnection->executeUpdate($sql);
    }

    /**
     * @param $relatedEntityClass
     * @param $property
     * @param $tableName
     * @param $identityInfo
     * @param $uuidColumnName
     * @return mixed
     */
    protected function fillRelatedEntityProperties($relatedEntityClass, $property, $tableName, $identityInfo, $uuidColumnName)
    {
        $this->loadMetaDataForEntityClass($relatedEntityClass);
        $propertyPrefix = $property . '_';
        $propertyColumnName = $this->getPropertyColumnName($property, $relatedEntityClass, true);
        $relatedUuidColumnName = $propertyPrefix . $this->propertyName;
        $this->loadMetaDataForEntityClass($relatedEntityClass);
        $relatedTableName = $this->allMetadata[$relatedEntityClass]->getTableName();

        //fwrite(STDOUT, $relatedEntityClass . ': uuidcol: ' . $relatedUuidColumnName . ', table name: ' . $relatedTableName . PHP_EOL);

        // Remove indexes from this column
        $indexes = $this->dropIndexesOnColumn($relatedTableName, $relatedUuidColumnName);

        $relatedAffected = $this->runRelationshipUpdateSql($relatedTableName, $tableName, $propertyColumnName, $identityInfo, $relatedUuidColumnName, $uuidColumnName);

        // Add index back to that column
        $this->restoreIndexes($relatedTableName, $indexes);

        return $relatedAffected;
    }

    /**
     * Copied from the Rhumsaa UUID library. Their methods are protected so I can't just access them :(
     * But we need to generate the "node" value ONCE for speed reasons. Othewrise we're discovering mac
     * address millions of times in a row for the purpose of this tool.
     *
     * @return mixed|null
     */
    private static function getNodeFromSystem()
    {
        $node = null;
        $pattern = '/[^:]([0-9A-Fa-f]{2}([:-])[0-9A-Fa-f]{2}(\2[0-9A-Fa-f]{2}){4})[^:]/';
        $matches = array();

        // Search the ifconfig output for all MAC addresses and return
        // the first one found
        if (preg_match_all($pattern, self::getIfconfig(), $matches, PREG_PATTERN_ORDER)) {
            $node = $matches[1][0];
            $node = str_replace(':', '', $node);
            $node = str_replace('-', '', $node);
        }

        return $node;
    }

    /**
     * Copied from the Rhumsaa UUID library. See notes on getNodeFromSystem()
     * @return mixed
     */
    private static function getIfconfig()
    {
        switch (strtoupper(substr(php_uname('a'), 0, 3))) {
            case 'WIN':
                $ifconfig = `ipconfig /all 2>&1`;
                break;
            case 'DAR':
                $ifconfig = `ifconfig 2>&1`;
                break;
            case 'LIN':
            default:
                $ifconfig = `netstat -ie 2>&1`;
                break;
        }

        return $ifconfig;
    }
}
