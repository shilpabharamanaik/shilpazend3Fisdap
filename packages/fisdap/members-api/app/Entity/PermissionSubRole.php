<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Permission SubRoles
 *
 * @Entity
 * @Table(name="fisdap2_permission_sub_role")
 */
class PermissionSubRole extends Enumerated
{
    /**
     * @ManyToOne(targetEntity="Role")
     */
    protected $role;
    
    /**
     * @Column(type="integer")
     */
    protected $permission_configuration;
}
