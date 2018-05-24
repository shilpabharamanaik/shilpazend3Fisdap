<?php

namespace Fisdap\Doctrine\Extensions\DoctrineCommand;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\ORM;

/**
 * Task for listing the associations to/from an entity on the basis
 * of a specified entity and entity's property
 *
 * @author Jesse Mortenson <jmortenson@fisdap.net>
 */
class ListAssociationsCommand extends Command
{
    /**
     * @var array Array of entity classes as keys, values are either NULL or the metadata, if loaded
     */
    protected $allMetadata;

    /**
     * @var object Doctrine entity manager
     */
    protected $em;

    /**
     * @var array Array of associations that we found
     */
    protected $foundAssociations = array();

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('doctrine-extensions:list-associations')
            ->setDescription('Lists associations for a given entity and property name')
            ->setDefinition(array(
                new InputArgument('entity', InputArgument::REQUIRED, 'The full entity name, ie: Fisdap\\Entity\\ShiftLegacy.'),
                new InputArgument('property', InputArgument::REQUIRED, 'The property name on that entity, ie: id.'),
            ))
            ->setHelp(<<<EOT
Lists associations for a given entity and property name.
EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // check if we have necessary arguments
        if (($entityName = $input->getArgument('entity')) === null) {
            throw new \RuntimeException("Argument 'entity' is required in order to execute this command correctly.");
        }
        if (($propertyName = $input->getArgument('property')) === null) {
            throw new \RuntimeException("Argument 'property' is required in order to execute this command correctly.");
        }

        // CONSTRUCTION STUFF
        // Get the entity manager and available entity class names
        $this->em = $this->getHelper('em')->getEntityManager();

        // this is a flat array of values like 'Fisdap\\Entity\\Window'
        $entityClassNames = $this->em->getConfiguration()
            ->getMetadataDriverImpl()
            ->getAllClassNames();
        // Make an array we can fill with class metadata
        $this->allMetadata = array();
        foreach ($entityClassNames as $class) {
            $this->allMetadata[$class] = NULL;
        }

        // Empty array for holding our associations
        $this->foundAssociations = array(
            'onEntity' => array(),
            'onOthers' => array(),
        );


        // DO WORK
        // Get the entity's associations
        $entityClassMetadata = $this->em->getClassMetadata($entityName);
        if (!$entityClassMetadata instanceof ORM\Mapping\ClassMetadata) {
            throw new \RuntimeException("Could not load class metadata for supplied entity name. Perhaps typo in entity name?");
        }

        // Get the entity's column name for the specified property
        $entityColumnName = $entityClassMetadata->fieldMappings[$propertyName]['columnName'];

        // check all the entity's associations
        foreach ($entityClassMetadata->getAssociationMappings() as $field => $mapping) {
            $otherEntityField = $mapping['mappedBy'];
            if ($otherEntityField) {

                $this->loadMetaDataForEntityClass($mapping['targetEntity']);

                $otherEntityMappings = $this->allMetadata[$mapping['targetEntity']]->getAssociationMappings();
                if (isset($otherEntityMappings[$otherEntityField]['joinColumns'])
                    && $this->checkIfMappingJoinColumnMatches($entityColumnName, $otherEntityMappings[$otherEntityField]['joinColumns'])
                ) {
                    $this->foundAssociations['onEntity'][$mapping['targetEntity']][] = $otherEntityField;
                } else {
                    throw new \RuntimeException("joinColumns index missing for " . $mapping['targetEntity']
                        . ":" . $otherEntityField . ", "
                        . " shows instead: " . print_r($otherEntityMappings, TRUE));
                }
            }
        }

        // now let's check every other entity to see if it has associations to this entity
        foreach ($this->allMetadata as $class => $otherMetadata) {
            $this->loadMetaDataForEntityClass($class);

            foreach ($this->allMetadata[$class]->getAssociationMappings() as $field => $mapping) {
                if ($mapping['targetEntity'] == $entityName) {
                    // found an association with our entity
                    // make sure we haven't already accounted for it
                    // and that it matches our entity/property criteria
                    if (!$this->checkIfAssociationAlreadyFoundOnEntity($class, $field)
                        && isset($mapping['joinColumns'])
                        && $this->checkIfMappingJoinColumnMatches($entityColumnName, $mapping['joinColumns'])) {
                        $this->foundAssociations['onOthers'][$class][] = $field;
                    }
                }
            }
        }

        // write output
        $output->write(json_encode($this->foundAssociations));
    }

    /**
     * See if the join column on an other entity mapping matches our target entity/property's column name
     *
     * @param $entityColumnName string Actual column name for the property we're looking for
     * @param $joinColumns array The 'joinColumns' array from a metadata mapping
     * @return bool
     */
    private function checkIfMappingJoinColumnMatches($entityColumnName, $joinColumns) {
        if (!is_array($joinColumns)) {
            return FALSE;
        }
        foreach ($joinColumns as $joinColumn) {
            if ($joinColumn['referencedColumnName'] == $entityColumnName) {
                return TRUE;
            }
        }

        return FALSE;
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
     * See if we already found this entity/field association referenced from our target entity (onEntity)
     *
     * @param $class string class name of the other entity
     * @param $field string field name of the other entity's associative property
     * @return bool
     */
    private function checkIfAssociationAlreadyFoundOnEntity($class, $field)
    {
        if (!isset($this->foundAssociations['onEntity'][$class])) {
            return FALSE;
        } else if (in_array($field, $this->foundAssociations['onEntity'][$class])) {
            return TRUE;
        }

        return FALSE;
    }
}
