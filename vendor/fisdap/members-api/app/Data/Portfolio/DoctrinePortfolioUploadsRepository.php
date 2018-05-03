<?php namespace Fisdap\Data\Portfolio;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrinePortfolioUploadsRepository
 *
 * @package Fisdap\Data\Portfolio
 * @copyright 1996-2015 Headwaters Software, Inc.
 */

class DoctrinePortfolioUploadsRepository extends DoctrineRepository implements PortfolioUploadsRepository
{

    /**
     * @inheritdoc
     */
    public function getUploadedFilesForStudent($studentid)
    {
        $qb = $this->createQueryBuilder('u');

        $qb->select('partial u.{id, original_name, created, description}, partial user.{id, first_name, last_name}')
            ->join('u.student', 's')
            ->join('u.uploader', 'user')
            ->where('s.id = ?1')
            ->setParameter(1, $studentid)
            ->orderBy('u.created', 'DESC');

        $uploads = $qb->getQuery()->getResult();

        return $uploads;
    }
}