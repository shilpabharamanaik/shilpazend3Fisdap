<?php namespace Fisdap\Data\Repository;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Happyr\DoctrineSpecification\Specification\Specification;
use Happyr\DoctrineSpecification\Result\ResultModifier;

/**
 * Interface Repository
 *
 * @package Fisdap\Data\Repository
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface Repository extends ObjectRepository, Selectable
{
    /**
     * @param mixed $id
     *
     * @return object|null
     */
    public function getOneById($id);


    /**
     * @param array $ids
     *
     * @return object|object[]|null
     */
    public function getById(array $ids);


    /**
     * @param object $entity
     */
    public function store($entity);


    /**
     * @param object[] $entities
     */
    public function storeCollection(array $entities);


    /**
     * @param object $entity
     */
    public function update($entity);


    /**
     * @param object[] $entities
     */
    public function updateCollection(array $entities);


    /**
     * @param object $entity
     */
    public function destroy($entity);


    /**
     * @param int[] $ids
     * @return int Number of deleted entities
     */
    public function destroyCollection(array $ids);


    /**
     * @param Specification  $specification
     * @param ResultModifier $modifier
     *
     * @param int           $firstResult
     * @param int           $maxResults
     *
     * @return mixed
     */
    public function match(Specification $specification, ResultModifier $modifier = null, $firstResult = null, $maxResults = null);
}
