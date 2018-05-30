<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * Program Type Legacy
 *
 * @Entity(repositoryClass="Fisdap\Data\Program\Type\DoctrineProgramTypeLegacyRepository")
 * @Table(name="ProgramTypeTable")
 */
class ProgramTypeLegacy extends Enumerated
{
    /**
     * @var integer
     * @Id
     * @Column(name="ProgramType_id", type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var string
     * @Column(name="ProgramType_desc", type="string")
     */
    protected $name;
}
