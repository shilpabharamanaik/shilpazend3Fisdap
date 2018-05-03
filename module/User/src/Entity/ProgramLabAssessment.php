<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for assigning Lab Assessments to a program.
 *
 * @Entity(repositoryClass="Fisdap\Data\Program\Procedures\DoctrineProgramLabAssessmentRepository")
 * @Table(name="fisdap2_program_lab_assessment")
 */
class ProgramLabAssessment extends ProgramProcedure
{
    /**
     * @ManyToOne(targetEntity="LabAssessment")
     */
    protected $lab;

    /**
     * @ManyToOne(targetEntity="ProgramLegacy", inversedBy="lab_assessments")
     * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     */
    protected $program;

    public static $procedureTypeVarName = "lab";

    public static function programIncludesProcedure($programId, $procedureId)
    {
        return self::getProgramProcedureInclusion(get_class(), self::$procedureTypeVarName, $programId, $procedureId);
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'program_id' => $this->program->id,
            'lab_id' => $this->lab->id,
            'included' => $this->included
        ];
    }
}
