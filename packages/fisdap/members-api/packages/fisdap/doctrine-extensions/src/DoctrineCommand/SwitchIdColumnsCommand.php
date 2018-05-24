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

/**
 * Set a new column as the ID column for entities.
 *
 * @author Jesse Mortenson <jmortenson@fisdap.net>
 */
class SwitchIdColumnsCommand extends Command
{
    use EntityModificationUtilities;

    CONST BACKUP_PROPERTY_PREFIX = 'uuidbackup_';

    /**
     * @var array Array of target entities for filling UUID data
     */
    protected $targetEntities;

    /**
     * @var string Current name of the property that will be switched to replace the old ID property
     */
    protected $newIdPropertyName = 'uuid';

    /**
     * @var bool Should we find and fill columns that represent doctrine relationships to the target column?
     */
    protected $doRelationships = FALSE;

    /**
     * @var bool Should we actually write new entity files?
     */
    protected $fileChanges = TRUE;

    /**
     * @var string Any migration code generated during entity modification for *applying* the migration
     */
    protected $migrationUpCode = '';

    /**
     * @var string Any migration code generated during entity modification for *UNapplying* the migration
     */
    protected $migrationDownCode = '';

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
            ->setName('doctrine-extensions:switch-id-columns')
            ->setDescription('Set a new column as the ID column for entities')
            ->setDefinition(array(
                new InputArgument('new-id-property-name', InputArgument::OPTIONAL, 'The name of the property on the entity that represents the new ID column'),
                new InputArgument('entities', InputArgument::IS_ARRAY, 'List of full entity names in quotes, ie: "Fisdap\\Entity\\ShiftLegacy".'),
            ))
            ->addOption('do-relationships', null, InputOption::VALUE_NONE, 'Should we find and switch ID columns for entities that are related to the target entities?')
            ->addOption('no-file-changes', null, InputOption::VALUE_NONE, 'If set, do not actually make file changes. Useful for testing')
            ->setHelp(<<<EOT
Set a new column as the ID column for entities
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
        if ($input->getArgument('new-id-property-name') !== null) {
            $this->newIdPropertyName = $input->getArgument('new-id-property-name');
        }
        if ($input->getOption('no-file-changes')) {
            $this->fileChanges = FALSE; // do not make file changes, ie testing mode
        }
        if ($input->getOption('do-relationships')) {
            $this->doRelationships = TRUE;
        }

        // CONSTRUCTION STUFF
        // Get the entity manager and available entity class names
        $this->em = $this->getHelper('em')->getEntityManager();

        // this is a flat array of values like 'Fisdap\\Entity\\Window'
        $entityClassNames = $this->em->getConfiguration()
            ->getMetadataDriverImpl()
            ->getAllClassNames();

        // Get metadata for each target entity
        foreach ($entityClassNames as $class) {
            $this->loadMetaDataForEntityClass($class);
        }

        // load the database connection (really only supporting MySQL in this tool)
        $this->dbConnection = $this->em->getConnection();


        // DO WORK
        foreach($this->targetEntities as $entityClass) {
            // Load the entity's class file
            $entityReflection = new \ReflectionClass($entityClass);
            $entityCodeString = $this->readEntityFileFromReflection($entityReflection);

            // Figure out the ID property: array('idPropertyName' => '', 'idColumnName' => '')
            $identityInfo = $this->getEntityIdentityInfo($entityClass);

            // add up and down code for switching primary key on the target entity
            $tableName = $this->allMetadata[$entityClass]->getTableName();
            $constraints = $this->getForeignKeyConstraintsByReference($tableName, $identityInfo['idColumnName']);
            $this->generateTargetSchemaChangeMigrationCode($constraints, $tableName, $identityInfo['idColumnName']);

            // Add @id annotation to new ID property
            $entityCodeString = $this->addIdAnnotationToNewIdProperty($entityCodeString);

            // Remove the existing ID property
            $entityCodeString = $this->removeOldIdProperty(
                $identityInfo['idPropertyName'],
                $entityCodeString
            );

            // Change property name of new ID property to the name of the prior ID column
            $entityCodeString = $this->renameNewIdPropertyToOld(
                $identityInfo['idPropertyName'],
                $identityInfo['idColumnName'],
                $entityClass,
                $entityCodeString
            );

            // Change pre-persist method to point at the newly-renamed ID property
            $entityCodeString = $this->updatePrePersistMethodToUseNewIdProperty($identityInfo['idPropertyName'], $entityCodeString);

            // Do relationships
            if ($this->doRelationships) {
                // find all associations (list-associations command)
                $idPropertyName = $identityInfo['idPropertyName'];
                $relatedEntities = $this->getAssociationsOnEntityProperty($entityClass, $idPropertyName);

                foreach ($relatedEntities as $relatedEntityClass => $properties) {
                    $this->loadMetaDataForEntityClass($relatedEntityClass);

                    foreach ($properties as $property) {

                        // MYSQL: Make migration to rename columns\
                        $relatedTableName = $this->allMetadata[$relatedEntityClass]->getTableName();
                        // need: the *column name* of the *Existing* relationship ID property, ie shift_id
                        $propertyColumnName = $this->getPropertyColumnName($property, $relatedEntityClass, TRUE);
                        $constraints = $this->getForeignKeyConstraintsDirectly($relatedTableName, $propertyColumnName);
                        $this->generateRelatedSchemaChangeMigrationCode($constraints, $relatedTableName, $propertyColumnName, $property . '_', FALSE);

                        // MODIFY ENTITY CODE
                        $relatedEntityReflection = new \ReflectionClass($relatedEntityClass);
                        $relatedEntityCodeString = $this->readEntityFileFromReflection($relatedEntityReflection);

                        // doctrine code: remove $obj->related_uuid property
                        $relatedIDProperty = $property . '_' . $this->newIdPropertyName;
                        $relatedEntityCodeString = $this->removeOldIdProperty($relatedIDProperty, $relatedEntityCodeString);

                        // Write the file?
                        if ($relatedEntityCodeString && $this->fileChanges) {
                            file_put_contents($relatedEntityReflection->getFileName(), $relatedEntityCodeString);
                        } else {
                            //$output->writeln($relatedEntityCodeString);
                        }

                    }
                }
            }

            // Write the file?
            if ($this->fileChanges) {
                file_put_contents($entityReflection->getFileName(), $entityCodeString);
            } else {
                //$output->writeln($entityCodeString);
            }

            // Generate migration code to change primary key
            $this->createDoctrineMigrationFile(
                $this->migrationUpCode,
                $this->migrationDownCode,
                FALSE /// do not use percona-online-schema-change
            );
        }
    }

    /**
     * Remove the old, sequential ID property from the entity class
     *
     * @param $oldPropertyName string Name of the property on the entity that is getting removed
     * @param $entityCodeString string PHP code representing the entity class
     * @return string the modified Entity code
     */
    private function removeOldIdProperty($oldPropertyName, $entityCodeString) {
        // Find where the property is in the string
        $oldPropertyString = 'protected $' . $oldPropertyName . ';';
        $propertyPosition = stripos($entityCodeString, $oldPropertyString);
        //fwrite(STDOUT, $propertyPosition . PHP_EOL);

        // Find the start of the first comment before that
        $propertyCommentPosition = strrpos($entityCodeString, '/**', (-1 * (strlen($entityCodeString) - $propertyPosition)));
        //fwrite(STDOUT, $propertyCommentPosition . PHP_EOL);
        $newlineOfPropertyCommentPosition = strrpos($entityCodeString, "\n", (-1 * (strlen($entityCodeString) - $propertyCommentPosition)));
        //fwrite(STDOUT, $newlineOfPropertyCommentPosition . PHP_EOL);

        // replace that stretch with empty
        $entityCodeString = substr_replace(
            $entityCodeString,
            "",
            $newlineOfPropertyCommentPosition,
            ($propertyPosition + strlen($oldPropertyString) - $newlineOfPropertyCommentPosition)
        );
        //fwrite(STDOUT, "({$propertyPosition} + " . strlen($oldPropertyString) . " - {$newlineOfPropertyCommentPosition}" . PHP_EOL);

        return $entityCodeString;
    }


    /**
     * Rename the temporary UUID property on the entity class to the name previously held by the old, sequential ID property
     *
     * @param $oldPropertyName string Name of the old ID property on the entity
     * @param $oldColumnName string the name of the old column, that this proprety must now match
     * @param $entityClass string the class of the entityClass
     * @param $entityCodeString string PHP code representing the entity class
     *
     * @throws \Exception
     *
     * @return string the modified Entity code
     */
    private function renameNewIdPropertyToOld($oldPropertyName, $oldColumnName, $entityClass, $entityCodeString) {
        // Change name of the property
        $newPropertyString = 'protected $' . $this->newIdPropertyName . ';';
        $renamedNewPropertyString = 'protected $' . $oldPropertyName . ';';
        $count = 1;
        $entityCodeString = str_replace($newPropertyString, $renamedNewPropertyString, $entityCodeString, $count);

        // Change the column name to match the old property's column name
        $propertyPosition = stripos($entityCodeString, $renamedNewPropertyString);

        // find the first start of a doc block above that. This is the start of our annotation space
        $propertyCommentPosition = strrpos($entityCodeString, '/**', (-1 * (strlen($entityCodeString) - $propertyPosition)));
        //fwrite(STDOUT, $propertyCommentPosition . PHP_EOL); // 1026

        // find the first instance of the @Column annotation, make sure it is in the anotation space
        $columnAnnotationPosition = strrpos($entityCodeString, '@Column', (-1 * (strlen($entityCodeString) - $propertyPosition)));
        //fwrite(STDOUT, $joinColumnAnnotationPosition . PHP_EOL); // 1103
        if ($columnAnnotationPosition > $propertyCommentPosition && $columnAnnotationPosition < $propertyPosition) {
            // find the name=" string
            $nameAttributeString = 'name="';
            $nameAttributeStringPosition = stripos($entityCodeString, $nameAttributeString, $columnAnnotationPosition);
            //fwrite(STDOUT, $nameAttributeStringPosition . PHP_EOL);
            if ($nameAttributeStringPosition > $propertyCommentPosition && $nameAttributeStringPosition < $propertyPosition) {
                // great, find the next end quote
                $endQuotePosition = strpos($entityCodeString, '"', ($nameAttributeStringPosition + strlen($nameAttributeString)));

                // replace with new column name
                $entityCodeString = substr_replace(
                    $entityCodeString,
                    'name="' . $oldColumnName . '"',
                    $nameAttributeStringPosition,
                    $endQuotePosition - $nameAttributeStringPosition + 1
                );
            } else {
                throw new \Exception("Failed to find a name=\"something\" attribute on the entity: {$entityClass}, property: {$this->newIdPropertyName}");
            }
        } else {
            throw new \Exception("Failed to find a proper @Column annotation for the entity: {$entityClass}, property: {$this->newIdPropertyName}");
        }


        return $entityCodeString;
    }

    /**
     * Add the doctrine ID annotation to the newly-switched-to ID property
     *
     * @param $entityCodeString string PHP code representing the entity class
     * @return string the modified Entity code
     */
    private function addIdAnnotationToNewIdProperty($entityCodeString) {
        // find the position of the new ID property
        $newPropertyString = 'protected $' . $this->newIdPropertyName . ';';
        $newPropertyPos = stripos($entityCodeString, $newPropertyString);
        //fwrite(STDOUT, $newPropertyPos . PHP_EOL);

        // find the first instance of @Column above that
        $columnAnnotationPosition = strrpos($entityCodeString, "@Column", (-1 * (strlen($entityCodeString) - $newPropertyPos)));
        //fwrite(STDOUT, $columnAnnotationPosition . PHP_EOL);

        // find the newline before that
        $newlineColumnAnnotationPosition = strrpos($entityCodeString, "\n", (-1 * (strlen($entityCodeString) - $columnAnnotationPosition)));
        //fwrite(STDOUT, $newlineColumnAnnotationPosition . PHP_EOL);

        // insert the @id annotation above that
        $idAnnotation = "\n     * @Id";
        $entityCodeString = substr_replace(
            $entityCodeString,
            $idAnnotation,
            $newlineColumnAnnotationPosition,
            0
        );

        return $entityCodeString;
    }

    /**
     * Change a target entity so that its prePersist method for generating UUID points to the newly renamed property.
     *
     * @param $oldPropertyName string Name of the property on the entity that is getting removed
     * @param $entityCodeString string PHP code representing the entity class
     * @return string the modified Entity code
     */
    private function updatePrePersistMethodToUseNewIdProperty($oldPropertyName, $entityCodeString) {
        // find location of generateUuidLifecycleCallback() method
        $prepersistFuncPosition = stripos($entityCodeString, "function generateUuidLifecycleCallback(");

        // find the first instance of setting the property after that position
        $setValueString = "\$this->{$this->newIdPropertyName} =";
        $setValuePosition = stripos($entityCodeString, $setValueString, $prepersistFuncPosition);

        // replace with the new
        $newSetValueString = "\$this->{$oldPropertyName} =";
        $entityCodeString = substr_replace($entityCodeString, "", $setValuePosition, strlen($setValueString));
        $entityCodeString = substr_replace($entityCodeString, $newSetValueString, $setValuePosition, 0);

        return $entityCodeString;
    }


    /**
     * Generate the doctrine migration code for a target entity
     *
     * @param $constraints array Nice constraints array
     * @param $tableName
     * @param $oldIdColumn
     * @param string $propertyPrefix
     */
    private function generateTargetSchemaChangeMigrationCode($constraints, $tableName, $oldIdColumn, $propertyPrefix = '') {
        $constraintCodes = $this->generateForeignKeyMigrationCode($constraints);
        $dropConstraintSQL = '';
        foreach ($constraintCodes['drop'] as $table => $statements) {
            foreach ($statements as $statement) {
                $dropConstraintSQL .= "\n" . $this->makeConventionalForeignKeyMigrationString($table, $statement);
            }
        }

        // Get the column definition code
        $newColumnDefinition = $this->getMySqlColumnDefinition($tableName, $propertyPrefix . $this->newIdPropertyName);
        $oldColumnDefinition = $this->getMySqlColumnDefinition($tableName, $oldIdColumn);

        $oldToBackupSQL = $this->makeConventionalForeignKeyMigrationString($tableName, "CHANGE " . $oldIdColumn . " " . $this::BACKUP_PROPERTY_PREFIX . $oldIdColumn . " " . $oldColumnDefinition);
        $newToOldSQL = $this->makeConventionalForeignKeyMigrationString($tableName, "CHANGE {$propertyPrefix}{$this->newIdPropertyName} {$oldIdColumn} {$newColumnDefinition}");
        $changePrimaryKeySQL = "// Change primary key\n" . $this->makeConventionalForeignKeyMigrationString($tableName, "DROP PRIMARY KEY, ADD PRIMARY KEY($oldIdColumn)");

        // drop/add the unique index on the target entity's supplemental UUID column
        $dropSupplementalUniqueIndex = $this->makeConventionalForeignKeyMigrationString($tableName, "DROP INDEX `uniq_{$this->newIdPropertyName}`");
        $addSupplementalUniqueIndex = $this->makeConventionalForeignKeyMigrationString($tableName, "ADD UNIQUE INDEX uniq_{$this->newIdPropertyName} ({$this->newIdPropertyName})");

        $this->migrationUpCode .= <<<EOT


        // SWITCH ID FOR {$tableName}
        // Rename column names (must drop foreign keys first))
        {$dropConstraintSQL}
{$oldToBackupSQL}
{$newToOldSQL}

        {$changePrimaryKeySQL}
        {$dropSupplementalUniqueIndex}
EOT;


        $newToTemporarySQL = $this->makeConventionalForeignKeyMigrationString($tableName, "CHANGE {$oldIdColumn} {$propertyPrefix}{$this->newIdPropertyName} {$newColumnDefinition}");
        $backupToOldSQL = $this->makeConventionalForeignKeyMigrationString($tableName, "CHANGE " . $this::BACKUP_PROPERTY_PREFIX . $oldIdColumn . " " . $oldIdColumn . " " . $oldColumnDefinition);
        $changePrimaryKeyBackSQL = "// Change primary key back\n" . $this->makeConventionalForeignKeyMigrationString($tableName, "DROP PRIMARY KEY, ADD PRIMARY KEY({$oldIdColumn})");

        $this->migrationDownCode .= <<<EOT


        // SWITCH ID FOR {$tableName}
        // Rename column names (must drop foreign keys first)
        {$dropConstraintSQL}
{$newToTemporarySQL}
{$backupToOldSQL}

        {$changePrimaryKeyBackSQL}
        {$addSupplementalUniqueIndex}
EOT;

        //fwrite(STDOUT, $this->migrationUpCode . PHP_EOL . PHP_EOL . $this->migrationDownCode . PHP_EOL); exit;
    }

    /**
     * Generate the Doctrine migration code for a related entity.
     *
     * @param $constraints array Nice constraints array
     * @param $tableName
     * @param $oldIdColumn
     * @param string $propertyPrefix
     */
    private function generateRelatedSchemaChangeMigrationCode($constraints, $tableName, $oldIdColumn, $propertyPrefix = '') {
        $constraintCodes = $this->generateForeignKeyMigrationCode($constraints);
        $addConstraintsSQL = '';
        foreach ($constraintCodes['add'] as $table => $statements) {
            foreach ($statements as $statement) {
                $addConstraintsSQL .= "\n" . $this->makeConventionalForeignKeyMigrationString($table, $statement);
            }
        }

        // Get the column definition code
        $newColumnDefinition = $this->getMySqlColumnDefinition($tableName, $propertyPrefix . $this->newIdPropertyName);
        $oldColumnDefinition = $this->getMySqlColumnDefinition($tableName, $oldIdColumn);

        $oldToBackupSQL = $this->makeConventionalForeignKeyMigrationString($tableName, "CHANGE " . $oldIdColumn . " " . $this::BACKUP_PROPERTY_PREFIX . $oldIdColumn . " " . $oldColumnDefinition);
        $newToOldSQL = $this->makeConventionalForeignKeyMigrationString($tableName, "CHANGE {$propertyPrefix}{$this->newIdPropertyName} {$oldIdColumn} {$newColumnDefinition}");

        $this->migrationUpCode .= <<<EOT


        // SWITCH ID FOR {$tableName}
        // Rename column names (must drop foreign keys first))
{$oldToBackupSQL}
{$newToOldSQL}
        {$addConstraintsSQL}

EOT;


        $newToTemporarySQL = $this->makeConventionalForeignKeyMigrationString($tableName, "CHANGE {$oldIdColumn} {$propertyPrefix}{$this->newIdPropertyName} {$newColumnDefinition}");
        $backupToOldSQL = $this->makeConventionalForeignKeyMigrationString($tableName, "CHANGE " . $this::BACKUP_PROPERTY_PREFIX . $oldIdColumn . " " . $oldIdColumn . " " . $oldColumnDefinition);

        $this->migrationDownCode .= <<<EOT


        // SWITCH ID FOR {$tableName}
        // Rename column names (must drop foreign keys first)
{$newToTemporarySQL}
{$backupToOldSQL}
        {$addConstraintsSQL}

EOT;

        //fwrite(STDOUT, $this->migrationUpCode . PHP_EOL . PHP_EOL . $this->migrationDownCode . PHP_EOL); exit;
    }


    /**
     * Get MySQL's column type definition for a table and column.
     *
     * @param $table
     * @param $column
     * @return string
     */
    private function getMySqlColumnDefinition($table, $column) {
        $sql = "SELECT COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'FISDAP'
AND TABLE_NAME = '{$table}'
AND COLUMN_NAME = '{$column}'";
        $resultSet = $this->dbConnection->executeQuery($sql);
        while ($columnDef = $resultSet->fetch(\PDO::FETCH_ASSOC)) {
            $definition = "{$columnDef['COLUMN_TYPE']} ";
            if ($columnDef['IS_NULLABLE'] == 'YES') {
                $definition .= " NULL DEFAULT NULL";
            } else {
                $definition .= " NOT NULL DEFAULT 0";
            }
            return $definition;
        }
    }


    /**
     * Generate add constraint/drop constraint migration strings based on constraints array, return in an array
     *
     * @param $constraints array Nice constraints array
     * @return array
     */
    private function generateForeignKeyMigrationCode($constraints) {
        $code = ['drop' => [], 'add' => []];
        foreach($constraints as $constraintTable => $constraintProperties) {
            foreach($constraintProperties as $foreignKeyInfo) {
                $code['drop'][$constraintTable][] = "DROP FOREIGN KEY {$foreignKeyInfo['constraintName']}";
                $code['add'][$constraintTable][] = "ADD CONSTRAINT {$foreignKeyInfo['constraintName']} FOREIGN KEY ({$foreignKeyInfo['columnName']}) REFERENCES {$foreignKeyInfo['referencedTable']}({$foreignKeyInfo['referencedColumn']})";
            }
        }

        return $code;
    }

    /**
     * Return Doctrine migration ALTER code
     *
     * @param $table
     * @param $alter
     * @return string
     */
    private function makeConventionalForeignKeyMigrationString($table, $alter) {
        return "        \$this->addSql('ALTER TABLE {$table} {$alter}');";
    }

    /**
     * Get MySQL constraints that are currently reference a particular table and column
     *
     * @param $table
     * @param $column
     * @return array
     */
    private function getForeignKeyConstraintsByReference($table, $column)
    {
        $sql = "SELECT
        TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE REFERENCED_COLUMN_NAME = '{$column}' AND  REFERENCED_TABLE_NAME = '{$table}';";
        $resultSet = $this->dbConnection->executeQuery($sql);

        $constraintTables = $this->processConstraintResultSet($resultSet);

        return $constraintTables;
    }

    /**
     * Get MySQL constraints that are currently placed on a specified table and column
     *
     * @param $table
     * @param $column
     * @return array
     */
    private function getForeignKeyConstraintsDirectly($table, $column)
    {
        $sql = "SELECT
        TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE COLUMN_NAME = '{$column}' AND TABLE_NAME = '{$table}' AND REFERENCED_TABLE_NAME IS NOT NULL";
        $resultSet = $this->dbConnection->executeQuery($sql);

        $constraintTables = $this->processConstraintResultSet($resultSet);

        return $constraintTables;
    }

    /**
     * Get associations on a property for an entity class
     *
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
     * Go through results of a query for column constraints and process into an array we can use
     *
     * @param $resultSet
     * @return array
     */
    private function processConstraintResultSet($resultSet)
    {
        $constraintTables = [];
        while ($constraint = $resultSet->fetch(\PDO::FETCH_ASSOC)) {
            $constraintTables[$constraint['TABLE_NAME']][] = [
                'columnName' => $constraint['COLUMN_NAME'],
                'constraintName' => $constraint['CONSTRAINT_NAME'],
                'referencedTable' => $constraint['REFERENCED_TABLE_NAME'],
                'referencedColumn' => $constraint['REFERENCED_COLUMN_NAME'],
            ];
        }
        return $constraintTables;
    }
}
