<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for assigning Iv procedures to a program.
 *
 * @Entity(repositoryClass="Fisdap\Data\Program\Procedures\DoctrineProgramIvProcedureRepository")
 * @Table(name="fisdap2_program_iv_procedure")
 */
class ProgramIvProcedure extends ProgramProcedure
{
    /**
     * @ManyToOne(targetEntity="IvProcedure")
     */
    protected $iv;

    /**
     * @ManyToOne(targetEntity="ProgramLegacy", inversedBy="iv_procedures")
     * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     */
    protected $program;

    public static $procedureTypeVarName = "iv";

    public static function programIncludesProcedure($programId, $procedureId)
    {
        return self::getProgramProcedureInclusion(get_class(), self::$procedureTypeVarName, $programId, $procedureId);
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'program_id' => $this->program->id,
            'iv_id' => $this->iv->id,
            'included' => $this->included
        ];
    }
}
