<?php namespace Fisdap\Data\Medrill;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\Repository;

/**
 * Interface MedrillVideoRepository
 *
 * @package Fisdap\Data\Medrill
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
interface MedrillVideoRepository extends Repository
{
    /**
     * Get all the Medrill video entities for a given product
     *
     * @param int $product_id
     * @return array array of \Fisdap\Entity\MedrillVideo s
     */
    public function getVideosByProduct($product_id);

    /**
     * Get the Medrill video entities in a given category for a given product
     *
     * @param int $product_id
     * @param int $category_id
     * @return array array of \Fisdap\Entity\MedrillVideo
     */
    public function getVideosByProductAndCategory($product_id, $category_id);
}
