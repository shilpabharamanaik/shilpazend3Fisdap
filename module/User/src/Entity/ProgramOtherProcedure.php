<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for assigning Other procedures to a program.
 *
 * @Entity(repositoryClass="Fisdap\Data\Program\Procedures\DoctrineProgramOtherProcedureRepository")
 * @Table(name="fisdap2_program_other_procedure")
 */
class ProgramOtherProcedure extends ProgramProcedure
{
    /**
     * @ManyToOne(targetEntity="OtherProcedure")
     */
    protected $other;

    /**
     * @ManyToOne(targetEntity="ProgramLegacy", inversedBy="other_procedures")
     * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     */
    protected $program;

    public static $procedureTypeVarName = "other";

    public static function programIncludesProcedure($programId, $procedureId)
    {
        return self::getProgramProcedureInclusion(get_class(), self::$procedureTypeVarName, $programId, $procedureId);
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'program_id' => $this->program->id,
            'other_id' => $this->other->id,
            'included' => $this->included
        ];
    }
}
