<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for assigning Airway procedures to a program.
 *
 * @Entity(repositoryClass="Fisdap\Data\Program\Procedures\DoctrineProgramAirwayProcedureRepository")
 * @Table(name="fisdap2_program_airway_procedure")
 */
class ProgramAirwayProcedure extends ProgramProcedure
{
    /**
     * @ManyToOne(targetEntity="AirwayProcedure")
     */
    protected $airway;

    /**
     * @ManyToOne(targetEntity="ProgramLegacy", inversedBy="airway_procedures")
     * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     */
    protected $program;

    public static $procedureTypeVarName = "airway";

    public static function programIncludesProcedure($programId, $procedureId)
    {
        return self::getProgramProcedureInclusion(get_class(), self::$procedureTypeVarName, $programId, $procedureId);
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'program_id' => $this->program->id,
            'airway_id' => $this->airway->id,
            'included' => $this->included
        ];
    }
}
