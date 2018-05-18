<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for assigning Cardiac procedures to a program.
 *
 * @Entity(repositoryClass="Fisdap\Data\Program\Procedures\DoctrineProgramCardiacProcedureRepository")
 * @Table(name="fisdap2_program_cardiac_procedure")
 */
class ProgramCardiacProcedure extends ProgramProcedure
{
    /**
     * @ManyToOne(targetEntity="CardiacProcedure")
     */
    protected $cardiac;

    /**
     * @ManyToOne(targetEntity="ProgramLegacy", inversedBy="cardiac_procedures")
     * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     */
    protected $program;

    public static $procedureTypeVarName = "cardiac";

    public static function programIncludesProcedure($programId, $procedureId)
    {
        return self::getProgramProcedureInclusion(get_class(), self::$procedureTypeVarName, $programId, $procedureId);
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'program_id' => $this->program->id,
            'cardiac_id' => $this->cardiac->id,
            'included' => $this->included
        ];
    }
}
