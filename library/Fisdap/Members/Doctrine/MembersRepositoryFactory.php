<?php namespace Fisdap\Members\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\RepositoryFactory;
use Fisdap\Entity\ShiftLegacy;
use Fisdap\Entity\User;
use Fisdap\Members\Shifts\MembersShiftRepository;
use Fisdap\Members\Users\MembersUserRepository;


/**
 * Overrides repository classes specified in entity metadata
 *
 * @package Fisdap\Members\Doctrine
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class MembersRepositoryFactory implements RepositoryFactory
{
    private static $entityRepositoryMap = [
        User::class => MembersUserRepository::class,
        ShiftLegacy::class => MembersShiftRepository::class
    ];
    
    
    /**
     * The list of EntityRepository instances.
     *
     * @var \Doctrine\Common\Persistence\ObjectRepository[]
     */
    private $repositoryList = array();

    /**
     * {@inheritdoc}
     */
    public function getRepository(EntityManagerInterface $entityManager, $entityName)
    {
        $repositoryHash = $entityManager->getClassMetadata($entityName)->getName() . spl_object_hash($entityManager);

        if (isset($this->repositoryList[$repositoryHash])) {
            return $this->repositoryList[$repositoryHash];
        }

        return $this->repositoryList[$repositoryHash] = $this->createRepository($entityManager, $entityName);
    }

    /**
     * Create a new repository instance for an entity class.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager The EntityManager instance.
     * @param string                               $entityName    The name of the entity.
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    private function createRepository(EntityManagerInterface $entityManager, $entityName)
    {
        /* @var $metadata \Doctrine\ORM\Mapping\ClassMetadata */
        $metadata            = $entityManager->getClassMetadata($entityName);
        
        if (array_key_exists($entityName, self::$entityRepositoryMap)) {
            $repositoryClassName = self::$entityRepositoryMap[$entityName];
        } else {
            $repositoryClassName = $metadata->customRepositoryClassName
                ?: $entityManager->getConfiguration()->getDefaultRepositoryClassName();
        }

        return new $repositoryClassName($entityManager, $metadata);
    }
}
