<?php namespace Fisdap\Data\Program\Type;

use Fisdap\Data\Repository\DoctrineRepository;
use Fisdap\Data\Repository\RetrievesByName;


/**
 * Class DoctrineProgramTypeLegacyRepository
 *
 * @package Fisdap\Data\Program\Type
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class DoctrineProgramTypeLegacyRepository extends DoctrineRepository implements ProgramTypeLegacyRepository
{
    use RetrievesByName;
}