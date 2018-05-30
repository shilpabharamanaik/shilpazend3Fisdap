<?php namespace Fisdap\Data\Role;

use Fisdap\Data\Repository\DoctrineRepository;
use Fisdap\Data\Repository\RetrievesByName;

/**
 * Class DoctrineRoleRepository
 *
 * @package Fisdap\Data\Role
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class DoctrineRoleRepository extends DoctrineRepository implements RoleRepository
{
    use RetrievesByName;
}
