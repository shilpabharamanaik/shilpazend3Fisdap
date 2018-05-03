<?php

use Codeception\TestCase\Test;
use Fisdap\Attachments\Associations\Entities\HasAttachments;
use Fisdap\Attachments\Associations\Repositories\RepositoryAttachmentsSupport;
use Fisdap\Attachments\Associations\Repositories\StoresAttachments;
use Fisdap\Data\Repository\Repository;


class RepositoryAttachmentsSupportTest extends Test implements Repository, StoresAttachments
{
    use RepositoryAttachmentsSupport;


    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var bool
     */
    private $failMode = false;


    protected function _before()
    {
    }

    protected function _after()
    {
        $this->failMode = false;
    }


    /** @test */
    public function it_can_find_the_associated_entity_by_id()
    {
        // act
        $associatedEntity = $this->findAssociatedEntity('foo', 12345);

        // assert
        $this->assertInstanceOf(HasAttachments::class, $associatedEntity);
    }


    /**
     * @test
     * @expectedException Fisdap\Attachments\Associations\Entities\AssociatedEntityNotFound
     */
    public function it_throws_an_exception_when_an_associated_enitity_cannot_be_found()
    {
        $this->failMode = true;
        $this->findAssociatedEntity('foo', 12345);
    }


    /*
     * Fake/Test Repository Implementation
     */

    /**
     * @param mixed $id
     *
     * @return object|null
     */
    public function getOneById($id)
    {
        if ($this->failMode === true) {
            return null;
        }

        return Mockery::mock(HasAttachments::class);
    }


    /**
     * @param array $ids
     *
     * @return object|object[]|null
     */
    public function getById(array $ids)
    {
    }


    /**
     * @param object $entity
     */
    public function store($entity)
    {
    }


    /**
     * @param object[] $entities
     */
    public function storeCollection(array $entities)
    {
    }


    /**
     * @param object $entity
     */
    public function update($entity)
    {
    }


    /**
     * @param object[] $entities
     */
    public function updateCollection(array $entities)
    {
    }


    /**
     * @param object $entity
     */
    public function destroy($entity)
    {
        // TODO: Implement destroy() method.
    }


    /**
     * @param int[] $ids
     *
     * @return int Number of deleted entities
     */
    public function destroyCollection(array $ids)
    {
    }


    /**
     * @param \Happyr\DoctrineSpecification\Specification\Specification $specification
     * @param \Happyr\DoctrineSpecification\Result\ResultModifier $modifier
     *
     * @param int $firstResult
     * @param int $maxResults
     *
     * @return mixed
     */
    public function match(
        \Happyr\DoctrineSpecification\Specification\Specification $specification,
        \Happyr\DoctrineSpecification\Result\ResultModifier $modifier = null,
        $firstResult = null,
        $maxResults = null
    ) {
    }


    /**
     * Finds an object by its primary key / identifier.
     *
     * @param mixed $id The identifier.
     *
     * @return object The object.
     */
    public function find($id)
    {
        // TODO: Implement find() method.
    }


    /**
     * Finds all objects in the repository.
     *
     * @return array The objects.
     */
    public function findAll()
    {
        // TODO: Implement findAll() method.
    }


    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array The objects.
     *
     * @throws \UnexpectedValueException
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        // TODO: Implement findBy() method.
    }


    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria The criteria.
     *
     * @return object The object.
     */
    public function findOneBy(array $criteria)
    {
        // TODO: Implement findOneBy() method.
    }


    /**
     * Returns the class name of the object managed by the repository.
     *
     * @return string
     */
    public function getClassName()
    {
        // TODO: Implement getClassName() method.
    }


    /**
     * Selects all elements from a selectable that match the expression and
     * returns a new collection containing these elements.
     *
     * @param \Doctrine\Common\Collections\Criteria $criteria
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function matching(\Doctrine\Common\Collections\Criteria $criteria)
    {
        // TODO: Implement matching() method.
    }
}