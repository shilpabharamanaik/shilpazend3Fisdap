<?php
namespace Fisdap\Doctrine\Extensions\DoctrineCommand;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Doctrine\ORM;

trait EntityModificationUtilities
{
    /**
     * Write a new Doctrine Migration class/file by calling the migrations:generate command and then
     * altering the resulting file with the migration code already generated.
     *
     * @param $upCode string The UP migration code
     * @param $downCode string the DOWN migration code
     * @param $usePerconaOnlineSchemaChange boolean Should we use the Percona Online Schema Change trait w/ the migration class?
     * @throws \Exception
     */
    protected function createDoctrineMigrationFile($upCode, $downCode, $usePerconaOnlineSchemaChange = false)
    {
        // Run the generate migration command (\Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand())
        $generateCommand = $this->getApplication()->find('migrations:generate');
        $generateCommandOutput = new BufferedOutput();
        $generateCommandInput = new ArrayInput(array(
            'command' => 'migrations:generate',
        ));
        $resultCode = $generateCommand->run($generateCommandInput, $generateCommandOutput);
        if ($resultCode == 0) {
            $generateMigrationCommandOutput = $generateCommandOutput->fetch();
            // Generated new migration class to "/vagrant/app/src/Data/DoctrineMigrations/Version20150423172736.php"
            $migrationFilename = trim(str_replace(
                ['"', 'Generated new migration class to '],
                ['', ''],
                $generateMigrationCommandOutput
            ));

            // Modify the Migration file with our migration code
            $migrationCodeString = file_get_contents($migrationFilename);

            // if using percona online schema change, we need to make sure migration uses that trait
            if ($usePerconaOnlineSchemaChange) {
                $traitCode = <<<EOT

    use PerconaOnlineSchemaChange;

EOT;
                $classPos = stripos($migrationCodeString, "\nclass");
                $bracketPosAfterClassPos = stripos($migrationCodeString, "{", $classPos);
                $migrationCodeString = substr_replace($migrationCodeString, $traitCode, ($bracketPosAfterClassPos + 1), 0);
            }

            // put in UP code
            $migrationCodeString = str_replace(
                '// this up() migration is auto-generated, please modify it to your needs',
                $upCode,
                $migrationCodeString
            );

            // put in DOWN code
            $migrationCodeString = str_replace(
                '// this down() migration is auto-generated, please modify it to your needs',
                $downCode,
                $migrationCodeString
            );

            // Write the migration file
            file_put_contents($migrationFilename, $migrationCodeString);
        } else {
            throw new \Exception("Unexpected code returned from migrations:generate command. Could not generate migration file.");
        }
    }


    /**
     * Load the metadata for an entity class, if missing. Adds to $this->allMetaData. Depends also on $this->em (entity manager)
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
     * @param $entityReflection
     * @return string
     */
    private function readEntityFileFromReflection($entityReflection)
    {
        $entityFile = fopen($entityReflection->getFileName(), 'r');
        $entityCodeString = fread($entityFile, 1000000);
        fclose($entityFile);
        return $entityCodeString;
    }

    private function getEntityIdentityInfo($entityClass)
    {
        // Find ID Property for entity
        $metadata = $this->allMetadata[$entityClass];
        $identifiers = $metadata->getIdentifierFieldNames();
        if (count($identifiers) > 1) {
            throw new \Exception("Entity {$entityClass} has more than one identifer field. Cannot handle relationships for it.");
        } else {
            $idProperty = current($identifiers);
        }

        // Get the field mapping for this field
        $idColumnName = $this->getPropertyColumnName($idProperty, $entityClass);

        return array(
            'idPropertyName' => $idProperty,
            'idColumnName' => $idColumnName,
        );
    }

    private function getPropertyColumnName($property, $entityClass, $isAssociationField = false)
    {
        if ($isAssociationField) {
            $mapping = $this->allMetadata[$entityClass]->getAssociationMapping($property);
            return $mapping['joinColumns'][0]['name'];
        } else {
            $mapping = $this->allMetadata[$entityClass]->getFieldMapping($property);
            return $mapping['columnName'];
        }
    }
}
