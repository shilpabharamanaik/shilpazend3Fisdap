<?php namespace Fisdap\Data\Gender;

use Fisdap\Data\Repository\Repository;
use Fisdap\Entity\Gender;

/**
 * Interface GenderRepository
 *
 * @package Fisdap\Data\Gender
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface GenderRepository extends Repository
{
    /**
     * @param $name
     *
     * @return Gender|null
     */
    public function getOneByName($name);
}
