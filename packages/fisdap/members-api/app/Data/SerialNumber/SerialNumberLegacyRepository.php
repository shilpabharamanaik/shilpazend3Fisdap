<?php namespace Fisdap\Data\SerialNumber;

use Fisdap\Data\Repository\Repository;
use Fisdap\Entity\SerialNumberLegacy;


/**
 * Interface SerialNumberLegacyRepository
 *
 * @package Fisdap\Data\SerialNumber
 */
interface SerialNumberLegacyRepository extends Repository
{
    /**
     * @param string $number
     *
     * @return SerialNumberLegacy|null
     */
    public function getOneByNumber($number);
}