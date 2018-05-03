<?php namespace Fisdap\Attachments\Categories\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * Template for attachment category Entities
 *
 * @package Fisdap\Attachments\Categories\Entity
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *
 * @Entity(repositoryClass="Fisdap\Attachments\Categories\Repository\DoctrineAttachmentCategoriesRepository")
 * @Table(
 *  name="AttachmentCategories",
 *  uniqueConstraints={@UniqueConstraint(name="unique_name", columns={"name", "type"})}
 * )
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="type", type="string")
 */
class AttachmentCategory
{
    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="integer", unique=true)
     */
    protected $id;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $name;


    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }


    /**
     * @param string $name
     *
     * @return static
     */
    public static function create($name)
    {
        return new static($name);
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
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
