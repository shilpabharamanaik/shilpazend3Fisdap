<?php namespace Fisdap\Api\ServiceAccounts\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\Table;


/**
 * Class ServiceAccount
 *
 * @package Fisdap\Api\ServiceAccounts\Entities
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *
 * @Table(name="ServiceAccount")
 * @Entity(repositoryClass="Fisdap\Api\ServiceAccounts\Repository\DoctrineServiceAccountsRepository")
 */
class ServiceAccount
{
    /**
     * @var string
     * @Id
     * @Column(type="string")
     */
    protected $id;

    /**
     * @var string
     * @Column(type="string", unique=true)
     */
    protected $name;
    
    /**
     * @var ArrayCollection|ServiceAccountPermission[]
     * @ManyToMany(targetEntity="ServiceAccountPermission", fetch="EAGER")
     * @JoinTable(name="ServiceAccountPermissions",
     *     joinColumns={@JoinColumn(name="serviceAccountClientId", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@JoinColumn(name="serviceAccountPermissionId", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $permissions;


    /**
     * ServiceAccount constructor.
     *
     * @param string $oauth2ClientId
     * @param string $name
     */
    public function __construct($oauth2ClientId, $name)
    {
        $this->id = $oauth2ClientId;
        $this->name = $name;
        $this->permissions = new ArrayCollection;
    }


    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    
    /**
     * @return string
     */
    public function getOauth2ClientId()
    {
        return $this->id;
    }


    /**
     * @param string $id
     */
    public function setOauth2ClientId($id)
    {
        $this->id = $id;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }


    /**
     * @return ArrayCollection|ServiceAccountPermission[]
     */
    public function getPermissions()
    {
        return $this->permissions;
    }


    /**
     * @param ServiceAccountPermission $permission
     */
    public function addPermission(ServiceAccountPermission $permission)
    {
        $this->permissions->add($permission);
    }


    /**
     * @param ServiceAccountPermission[] $permissions
     */
    public function addPermissions(array $permissions)
    {
        foreach ($permissions as $permission) {
            if ( ! $permission instanceof ServiceAccountPermission) continue;
            
            $this->addPermission($permission);
        }
    }
    

    /**
     * @param ServiceAccountPermission $permission
     */
    public function removePermission(ServiceAccountPermission $permission)
    {
        $this->permissions->removeElement($permission);
    }


    /**
     * @param ServiceAccountPermission[] $permissions
     */
    public function removePermissions(array $permissions)
    {
        foreach ($permissions as $permission) {
            if ( ! $permission instanceof ServiceAccountPermission) continue;

            $this->removePermission($permission);
        }
    }
    
    
    public function clearPermissions()
    {
        $this->permissions->clear();
    }


    /**
     * @param string $routeName
     *
     * @return bool
     */
    public function hasPermission($routeName)
    {
        $permissionCriteria = Criteria::create();
        $permissionCriteria->where(Criteria::expr()->eq('routeName', $routeName));
        
        return $this->permissions->matching($permissionCriteria)->count() > 0;
    }
}