<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for assigning med type to a program.
 *
 * @Entity(repositoryClass="Fisdap\Data\Program\Procedures\DoctrineProgramMedTypeRepository")
 * @Table(name="fisdap2_program_med_type")
 */
class ProgramMedType
{
	
	 /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="MedType")
     */
    protected $med_type;

    /**
     * @ManyToOne(targetEntity="ProgramLegacy", inversedBy="med_types")
     * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     */
    protected $program;

    public static $procedureTypeVarName = "med_type";

    public static function programIncludesProcedure($programId, $procedureId)
    {
        return self::getProgramProcedureInclusion(get_class(), self::$procedureTypeVarName, $programId, $procedureId);
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'program_id' => $this->program->id,
            'med_type_id' => $this->med_type->id,
            'included' => $this->included
        ];
    }
}
