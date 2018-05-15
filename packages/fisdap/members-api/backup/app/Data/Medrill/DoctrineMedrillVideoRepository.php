<?php namespace Fisdap\Data\Medrill;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrineMedrillVideoRepository
 *
 * @package Fisdap\Data\Medrill
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineMedrillVideoRepository extends DoctrineRepository implements MedrillVideoRepository
{
    /**
     * Get all the Medrill video entities for a given product
     *
     * @param int $product_id
     * @return array array of \Fisdap\Entity\MedrillVideo s
     */
    public function getVideosByProduct($product_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('mv')
            ->from('\Fisdap\Entity\MedrillVideo', 'mv')
            ->join("mv.products", "p")
            ->where("p.id = ?1")
            ->setParameter(1, $product_id)
            ->orderBy('mv.title', 'ASC');

        $results = $qb->getQuery()->getResult();

        // re-key the array so it's keyed by category
        $categorized_videos = array();
        foreach ($results as $video) {
            $categorized_videos[$video->category->name][] = $video;
        }

        ksort($categorized_videos);
        return $categorized_videos;
    }

    /**
     * Get the Medrill video entities in a given category for a given product
     *
     * @param int $product_id
     * @param int $category_id
     * @return array array of \Fisdap\Entity\MedrillVideo s
     */
    public function getVideosByProductAndCategory($product_id, $category_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('mv')
            ->from('\Fisdap\Entity\MedrillVideo', 'mv')
            ->join("mv.products", "p")
            ->where("p.id = ?1")
            ->andWhere("mv.category = ?2")
            ->setParameter(1, $product_id)
            ->setParameter(2, $category_id)
            ->orderBy('mv.title', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
