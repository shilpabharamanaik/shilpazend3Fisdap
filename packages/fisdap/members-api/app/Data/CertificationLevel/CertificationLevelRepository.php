<?php namespace Fisdap\Data\CertificationLevel;

use Fisdap\Data\Repository\Repository;
use Fisdap\Entity\CertificationLevel;


/**
 * Interface CertificationLevelRepository
 *
 * @package Fisdap\Data\CertificationLevel
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface CertificationLevelRepository extends Repository
{
    /**
     * @param string $name
     *
     * @return CertificationLevel
     */
    public function getOneByName($name);
} 