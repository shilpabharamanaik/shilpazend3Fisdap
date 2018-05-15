<?php namespace Fisdap\Data\Role;

use Fisdap\Data\Repository\Repository;
use Fisdap\Entity\Role;

/**
 * Interface RoleRepository
 *
 * @package Fisdap\Data\Role
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface RoleRepository extends Repository
{
    /**
     * @param string $name
     *
     * @return Role
     */
    public function getOneByName($name);
}
