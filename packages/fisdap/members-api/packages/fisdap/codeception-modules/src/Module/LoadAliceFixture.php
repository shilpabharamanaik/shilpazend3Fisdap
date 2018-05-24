<?php namespace Codeception\Module;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;


class LoadAliceFixture implements FixtureInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var array Array of Lifecycle Callbacks collected from Entity metadata, keyed by entity class
     */
    protected $lifecycleCallbacks = array();

    /**
     * @param array $fixtures
     */
    public function __construct(array $fixtures)
    {
        $this->fixtures = $fixtures;
    }


    public function load(ObjectManager $manager)
    {
        $this->em = $manager;

        $this->modifyFixtureClassMetadata($this->fixtures);

        foreach ($this->fixtures as $fixture) {
            $manager->persist($fixture);
        }

        $manager->flush();

        // re-add lifecycle callbacks
        $this->restoreLifecycleCallbacksBacks();
    }


    /**
     * @param array $fixtures
     *
     * @return array
     */
    protected function modifyFixtureClassMetadata(array $fixtures)
    {
        foreach ($fixtures as $fixture) {
            $classMetadata = $this->em->getClassMetadata(get_class($fixture));

            /*
             * Clear Doctrine lifecycle callbacks, to prevent them from being called during entity changes
             * ...these were primarily causing problems when trying to set updated/created timestamps
             */
            // but before we do, grab and store a copy of any callbacks that existed for later restoration
            if (!empty($classMetadata->lifecycleCallbacks)) {
                $this->lifecycleCallbacks[get_class($fixture)] = $classMetadata->lifecycleCallbacks; // store for re-adding
            }
            $classMetadata->setLifecycleCallbacks([]);

            /**
             * Allow explicit setting of ids (instead of auto incrementing)
             *
             * @see http://stackoverflow.com/questions/5301285/explicitly-set-id-with-doctrine-when-using-auto-strategy
             */
            $classMetadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        }
    }

    /**
     * Use the callback data collected in $this->lifecycleCallbacks to restore callbacks
     * to doctrine metadata. Should restore Doctrine lifecycle callback system to original state
     * (after modifyFixtureClassMetadata wipes out all lifecycle callbacks).
     */
    protected function restoreLifecycleCallbacksBacks() {
        foreach ($this->lifecycleCallbacks as $entityClass => $callbacks) {
            $classMetadata = $this->em->getClassMetadata($entityClass);
            $classMetadata->setLifecycleCallbacks($callbacks);
        }
    }
}