<?php namespace Fisdap\AliceFixtureGenerator;

use Doctrine\ORM\EntityManager;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Yaml\Yaml;


/**
 * Class GenerateCommand
 *
 * @package Fisdap\AliceFixtureGenerator
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class GenerateCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected $name = 'fixtures:generate';

    /**
     * @inheritdoc
     */
    protected $description = 'Generates Alice fixtures from hydrated Doctrine entities';

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var string
     */
    protected $fixturePath;

    /**
     * @var array
     */
    protected $fixtureFileContents = [];


    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct();

        $this->em = $em;
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $entityName = $this->argument('entityName');
        $startEntityId = $this->argument('startEntityId');
        $endEntityId = $this->argument('endEntityId');
        $depth = $this->argument('depth');

        $this->fixturePath = $this->option('outputPath');

        $this->info("Generating fixtures for $entityName in {$this->fixturePath}...");
        
        $this->generateFixtures($entityName, $startEntityId, $endEntityId, $depth);
    }


    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['entityName', InputArgument::REQUIRED, 'The fully-qualified class name of the Entity.'],
            ['startEntityId', InputArgument::REQUIRED, 'The first Entity ID.'],
            ['endEntityId', InputArgument::REQUIRED, 'The last Entity ID.'],
            ['depth', InputArgument::OPTIONAL, 'Number of levels to iterate through to retrieve associations.', 0]
        ];
    }


    /**
     * Get the console command options.
     *
     * @return array
     * @todo add blacklist for fields
     */
    protected function getOptions()
    {
        return [
            ['outputPath', 'p', InputOption::VALUE_OPTIONAL, 'The path to save fixtures.', getcwd()],
        ];
    }


    /**
     * @param string $entityName
     * @param int    $startEntityId
     * @param int    $endEntityId
     * @param int    $depth
     */
    protected function generateFixtures($entityName, $startEntityId, $endEntityId, $depth = 0)
    {
        $startEntityClass = $entityName;

        foreach (range($startEntityId, $endEntityId) as $id) {
            $this->populateFixtureFiles($startEntityClass, $id, $depth);
        }

        // strip the namespace
        $shortName = explode('\\', $entityName);
        $fileName = array_pop($shortName);
        $this->writeFixtures($this->fixturePath, $fileName);
    }


    /**
     * @param $path
     * @param $fileNameBase
     */
    protected function writeFixtures($path, $fileNameBase)
    {
        $fixtureCount = count($this->fixtureFileContents);

        $filePath = $path . "/" . $fileNameBase . ".yml";

        // Make the dir for these fixtures...  Suppress warnings if dir already exists.
        @mkdir($path);

        $fixtures = Yaml::dump($this->fixtureFileContents, 3, 2, false, true);

        file_put_contents($filePath, $fixtures);

        echo "$fixtureCount fixtures written to $filePath." . PHP_EOL;
    }


    /**
     * @param string    $entityClass
     * @param int       $id
     * @param int       $depth
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    protected function populateFixtureFiles($entityClass, $id, $depth = 0)
    {
        if ( ! class_exists($entityClass)) {
            return;
        }

        // get entity class metadata
        /** @var \Doctrine\ORM\Mapping\ClassMetadata $metadata */
        $metadata = $this->em->getClassMetadata($entityClass);

        // get entity
        $entity = $this->em->find($entityClass, $id);

        if ($entity === null) {
            return;
        }

        $classParts = explode('\\', $entityClass);
        $entityShortName = array_pop($classParts);

        $fields = $metadata->getFieldNames();

        if ( ! array_key_exists($entityClass, $this->fixtureFileContents)) {
            $this->fixtureFileContents[$entityClass] = [];
        }

        $entityKey = $entityShortName . '_' . $entity->id;

        $this->fixtureFileContents[$entityClass][$entityKey] = [];

        foreach ($fields as $field) {

            // set created/updated timestamps
            switch($field) {
                case 'created':
                    $value = '<dateTimeBetween(\'-1 year\', \'now\')>';
                    break;
                case 'updated':
                    $value = '<dateTimeBetween($created, \'now\')>';
                    break;
                default:
                    $value = $entity->$field;
                    break;
            }

            // convert existing DateTime values to unix timestamps
            if ($value instanceof \DateTime) {
                $value = $value->getTimestamp();
                // handle bad(?) dates
                if ($value < 0) {
                    $value = '<DateTime()>';
                }
            }

            // handle getters that return entities without proper association metadata
            if (is_object($value)) {
                $value = '@' . $entityShortName . '_' . $value->id;
            }

//        if (is_array($value)) {
//            $value = json_encode($value);
//        }

            // Default values
            if ($value == null && !$metadata->isNullable($field)) {
                switch ($metadata->getTypeOfField($field)) {
                    case 'boolean':
                        $value = false;
                        break;
                    case 'datetime':
                        $value = '<DateTime()>'; //date('Y-m-d H:i:s');
                        break;
                }
            }

            $this->fixtureFileContents[$entityClass][$entityKey][$field] = $value;
        }

        $associations = $metadata->getAssociationMappings();

        /* Go through the associations.  If an association references a field not shown in $fields,
           add it to the entity metadata.  Otherwise, if our depth > 0, add the child fixture. */
        foreach ($associations as $associationName => $association) {
            $fieldName = $association['fieldName'];
            $targetEntity = $association['targetEntity'];
            $classParts = explode('\\', $targetEntity);
            $targetEntityShortName = array_pop($classParts);

            if ($entity->$fieldName == null) {
                continue;
            }

            if (is_a($entity->$fieldName, 'Doctrine\ORM\PersistentCollection')) {
                if ($depth > 0) {
                    foreach ($entity->$fieldName as $collectionEntity) {
                        $this->populateFixtureFiles($targetEntity, $collectionEntity->id, $depth - 1);
                    }
                }
            } else {
                if ( ! in_array($fieldName, $fields)) {
                    // Quick check to see if the associated entity even exists in the DB...
                    try {
                        if ( ! is_object($entity->$fieldName)) continue;
                        $this->fixtureFileContents[$entityClass][$entityKey][$fieldName] = '@' . $targetEntityShortName . '_' . $entity->$fieldName->id;
                    } catch (\Exception $e) {
                        echo 'Caught ' . get_class($e) . ': '. $e->getMessage() . PHP_EOL;
                        continue;
                    }
                }

                if ($depth > 0) {
                    $this->populateFixtureFiles($targetEntity, $entity->$fieldName->id, $depth - 1);
                }
            }
        }
    }
}
