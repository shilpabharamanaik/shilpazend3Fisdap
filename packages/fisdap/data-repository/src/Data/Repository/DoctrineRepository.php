<?php namespace Fisdap\Data\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Happyr\DoctrineSpecification\EntitySpecificationRepositoryTrait;
use Happyr\DoctrineSpecification\Result\ResultModifier;
use Happyr\DoctrineSpecification\Specification\Specification;
use Psr\Log\LoggerInterface;

/**
 * Class DoctrineRepository
 *
 * @package Fisdap\Data\Repository
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class DoctrineRepository extends EntityRepository implements Repository
{
    use EntitySpecificationRepositoryTrait;


    /**
     * @var LoggerInterface
     */
    protected $logger = null;


    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    /**
     * @inheritdoc
     */
    public function getOneById($id)
    {
        return $this->find($id);
    }


    /**
     * @inheritdoc
     */
    public function getById(array $ids)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->findById($ids);
    }


    /**
     * @inheritdoc
     */
    public function store($entity)
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }


    /**
     * @inheritdoc
     */
    public function storeCollection(array $entities)
    {
        foreach ($entities as $entity) {
            $this->_em->persist($entity);
        }

        $this->_em->flush();
    }


    /**
     * @inheritdoc
     */
    public function update($entity)
    {
        // all that's necessary here is a flush()...Doctrine takes care of the rest
        $this->_em->flush();
    }


    /**
     * @inheritdoc
     */
    public function updateCollection(array $entities)
    {
        // all that's necessary here is a flush()...Doctrine takes care of the rest
        $this->_em->flush();
    }


    /**
     * @inheritdoc
     */
    public function destroy($entity)
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }


    /**
     * @inheritdoc
     */
    public function destroyCollection(array $ids)
    {
        $queryBuilder = $this->createQueryBuilder($this->alias);
        $idField = $this->getClassMetadata()->getSingleIdentifierColumnName();
        $queryBuilder->delete()
            ->where($queryBuilder->expr()->in("{$this->alias}.$idField", ':ids'))
            ->setParameter('ids', $ids);
        return $queryBuilder->getQuery()->execute();
    }


    /**
     * Get result when you match with a Specification
     *
     * @param Specification  $specification
     * @param ResultModifier $modifier
     *
     * @param int           $firstResult
     * @param int           $maxResults
     *
     * @return mixed
     */
    public function match(
        Specification $specification,
        ResultModifier $modifier = null,
        $firstResult = null,
        $maxResults = null
    ) {
        $query = $this->getQuery($specification, $modifier);

        if ($firstResult !== null && $maxResults !== null) {
            $query->setFirstResult($firstResult)->setMaxResults($maxResults);

            $paginator = new Paginator($query, $fetchJoinCollection = true);

            if ($this->logger !== null) {
                $this->logger->debug(
                    'DoctrineRepository::match Paginator DQL: ' . $paginator->getQuery()->getDQL(),
                    $this->getDebugContext()
                );
            }

            return $paginator->getIterator()->getArrayCopy();
        }

        if ($this->logger !== null) {
            $this->logger->debug(
                'DoctrineRepository::match DQL: ' . $query->getDQL(),
                $this->getDebugContext()
            );
        }

        return $query->execute();
    }


    /**
     * @return array
     */
    protected function getDebugContext()
    {
        $debugBacktrace = debug_backtrace();

        $callingClass = &$debugBacktrace[2]['class'];
        $callingMethod = &$debugBacktrace[2]['function'];

        return ['calledBy' => "{$callingClass}::{$callingMethod}()"];
    }
}
