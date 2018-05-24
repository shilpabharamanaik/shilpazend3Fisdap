<?php namespace Fisdap\Data\Ethnicity;

use Fisdap\Data\Repository\Repository;
use Fisdap\Entity\Ethnicity;


/**
 * Interface EthnicityRepository
 *
 * @package Fisdap\Data\Ethnicity
 */
interface EthnicityRepository extends Repository
{
    /**
     * @param mixed $id
     *
     * @return Ethnicity|null
     */
    public function getOneById($id);


    /**
     * @param string $name
     *
     * @return Ethnicity
     */
    public function getOneByName($name);
}