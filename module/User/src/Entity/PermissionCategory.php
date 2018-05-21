<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Permission Categories.
 *
 * @Entity
 * @Table(name="fisdap2_permission_category")
 */
class PermissionCategory extends Enumerated
{
}
