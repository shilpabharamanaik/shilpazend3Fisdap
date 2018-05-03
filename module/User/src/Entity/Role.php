<?php namespace User\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;


/**
 * Role Entity
 *
 * @Entity(repositoryClass="Fisdap\Data\Role\DoctrineRoleRepository")
 * @Table(name="fisdap2_roles")
 */
class Role extends EntityBaseClass
{
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $name;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $entity_name;


    /**
     * @ManyToMany(targetEntity="Permission", mappedBy="roles")
     * @JoinTable(name="fisdap2_permission_role",
     *  joinColumns={@JoinColumn(name="role_id", referencedColumnName="id")},
     *  inverseJoinColumns={@JoinColumn(name="permission_id",referencedColumnName="id")})
     */
    protected $permissions;


    public function __construct()
    {
        $this->permissions = new ArrayCollection();
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
     * @return string
     */
    public function getEntityName()
    {
        return $this->entity_name;
    }


    /**
     * @param string $entity_name
     */
    public function setEntityName($entity_name)
    {
        $this->entity_name = $entity_name;
    }


    /**
     * @return string
     * @todo Fully-qualified entity name should really be stored in database
     */
    public function getFullEntityClassName()
    {
        return 'Fisdap\Entity\\' . $this->entity_name;
    }
}
