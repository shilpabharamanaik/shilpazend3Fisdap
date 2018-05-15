<?php namespace Fisdap\Data\Ethnicity;

use Fisdap\Data\Repository\DoctrineRepository;
use Fisdap\Data\Repository\RetrievesByName;

/**
 * Class DoctrineEthnicityRepository
 *
 * @package Fisdap\Data\Ethnicity
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class DoctrineEthnicityRepository extends DoctrineRepository implements EthnicityRepository
{
    use RetrievesByName;
}
