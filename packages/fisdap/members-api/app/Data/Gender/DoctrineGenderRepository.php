<?php namespace Fisdap\Data\Gender;

use Fisdap\Data\Repository\DoctrineRepository;
use Fisdap\Data\Repository\RetrievesByName;

/**
 * Class DoctrineGenderRepository
 *
 * @package Fisdap\Data\Gender
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class DoctrineGenderRepository extends DoctrineRepository implements GenderRepository
{
    use RetrievesByName;
}
