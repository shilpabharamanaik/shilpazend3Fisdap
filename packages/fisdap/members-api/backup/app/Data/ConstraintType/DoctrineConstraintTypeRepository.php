<?php namespace Fisdap\Data\ConstraintType;

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrineConstraintTypeRepository
 *
 * @package Fisdap\Data\ConstraintType
 */
class DoctrineConstraintTypeRepository extends DoctrineRepository implements ConstraintTypeRepository
{
    /**
     * @todo use $this->findAll() instead
     * @return array
     */
    public function getAll()
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('c')
           ->from('\Fisdap\Entity\ConstraintType', 'c');

        return $qb->getQuery()->getResult();
    }
}
