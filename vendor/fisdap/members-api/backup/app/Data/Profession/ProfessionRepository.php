<?php namespace Fisdap\Data\Profession;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\Repository;


/**
 * Interface ProfessionRepository
 *
 * @package Fisdap\Data\Profession
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
interface ProfessionRepository extends Repository {

    /**
     * Use Doctrine query builder to pull all rows for Profession and return them in an array to be used
     * by getFormOptions()
     * @return array
     */
    public function getAllProfessionInfo($orderBy);

    /**
     * Organize array returned by getAllProfessionInfo() into a new array which can be used in a Zend form.
     * @return array
     */
    public function getFormOptions($orderBy);
}