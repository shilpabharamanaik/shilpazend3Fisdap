<?php namespace Fisdap\Api\ServiceAccounts\Entities;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;


/**
 * Class ServiceAccountPermission
 *
 * @package Fisdap\Api\ServiceAccounts\Entities
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *          
 * @Table(name="ServiceAccountPermission")
 * @Entity(repositoryClass="Fisdap\Api\ServiceAccounts\Permissions\Repository\DoctrineServiceAccountPermissionsRepository")
 */
class ServiceAccountPermission
{
    /**
     * @var int
     * @Column(type="integer")
     * @Id
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @var string
     * @Column(type="string", unique=true)
     */
    protected $routeName;


    /**
     * ServiceAccountPermission constructor.
     *
     * @param $routeName
     */
    public function __construct($routeName)
    {
        $this->routeName = $routeName;
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }


    /**
     * @param string $routeName
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }
}