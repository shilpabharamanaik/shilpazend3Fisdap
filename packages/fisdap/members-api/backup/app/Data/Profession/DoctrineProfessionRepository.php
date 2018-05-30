<?php namespace Fisdap\Data\Profession;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrineProfessionRepository
 *
 * @package Fisdap\Data\Profession
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineProfessionRepository extends DoctrineRepository implements ProfessionRepository
{
    /**
     * Use Doctrine query builder to pull all rows for Profession and return them in an array to be used
     * by getFormOptions()
     * @return array
     */
    public function getAllProfessionInfo($orderBy)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('prof.id, prof.name')
            ->from('\Fisdap\Entity\Profession', 'prof');

        $qb->orderBy($orderBy, 'ASC');

        $results = $qb->getQuery()->getResult();

        return $results;
    }

    /**
     * Organize array returned by getAllProfessionInfo() into a new array which can be used in a Zend form.
     * @return array
     */
    public function getFormOptions($orderBy = "prof.id")
    {
        $rawProfs = $this->getAllProfessionInfo($orderBy);
        $formOptions = array();
        foreach ($rawProfs as $prof) {
            $formOptions[$prof['id']] = $prof['name'];
        }
        return $formOptions;
    }
}
