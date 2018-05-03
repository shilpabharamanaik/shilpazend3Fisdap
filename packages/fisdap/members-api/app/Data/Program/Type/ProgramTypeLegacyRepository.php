<?php namespace Fisdap\Data\Program\Type;

use Fisdap\Data\Repository\Repository;
use Fisdap\Entity\ProgramTypeLegacy;


/**
 * Interface ProgramTypeLegacyRepository
 *
 * @package Fisdap\Data\Program\Type
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface ProgramTypeLegacyRepository extends Repository
{
    /**
     * @param string $name
     *
     * @return ProgramTypeLegacy
     */
    public function getOneByName($name);
}