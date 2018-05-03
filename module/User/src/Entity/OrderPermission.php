<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;


/**
 * List of the different ways programs are allowed to order accounts: can order, credit card only, cannot order
 * 
 * @Entity(repositoryClass="Fisdap\Data\Order\Permission\DoctrineOrderPermissionRepository")
 * @Table(name="fisdap2_order_permission")
 */
class OrderPermission extends Enumerated
{
}