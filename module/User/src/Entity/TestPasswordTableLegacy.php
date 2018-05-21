<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for the legacy TestPasswordTable table.
 *
 * @Entity
 * @Table(name="TestPasswordTable")
 */
class TestPasswordTableLegacy extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="test_password_id", type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @Column(name="password", type="string")
     */
    protected $password;
}
