<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;

/**
 * Entity for a program's required shift-level evaluations
 *
 * @Entity(repositoryClass="Fisdap\Data\Program\RequiredShiftEvaluations\DoctrineProgramRequiredShiftEvaluationsRepository")
 * @Table(name="fisdap2_program_required_shift_evaluations")
 */
class ProgramRequiredShiftEvaluations
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    private $id;

    /**
     * @var ProgramLegacy
     * @OneToOne(targetEntity="ProgramLegacy")
     * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     */
    private $program;

    /**
     * @var EvalDefLegacy
     * @OneToOne(targetEntity="EvalDefLegacy")
     * @joinColumn(name="eval_def_id", referencedColumnName="EvalDef_id")
     */
    private $eval_def;

    /**
     * @Column(name="shift_type", type="string")
     */
    private $shift_type;

    /**
     * ProgramRequiredShiftEvaluations constructor.
     * @param ProgramLegacy $program
     * @param EvalDefLegacy $eval_def
     * @param $shift_type
     */
    public function __construct(ProgramLegacy $program, EvalDefLegacy $eval_def, $shift_type)
    {
        $this->program = $program;
        $this->eval_def = $eval_def;
        $this->shift_type = $shift_type;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ProgramLegacy
     */
    public function getProgram()
    {
        return $this->program;
    }

    /**
     * @param ProgramLegacy $program
     */
    public function setProgram($program)
    {
        $this->program = $program;
    }

    /**
     * @return EvalDefLegacy
     */
    public function getEvalDef()
    {
        return $this->eval_def;
    }

    /**
     * @param EvalDefLegacy $eval_def
     */
    public function setEvalDef($eval_def)
    {
        $this->eval_def = $eval_def;
    }

    /**
     * @return mixed
     */
    public function getShiftType()
    {
        return $this->shift_type;
    }

    /**
     * @param mixed $shift_type
     */
    public function setShiftType($shift_type)
    {
        $this->shift_type = $shift_type;
    }
    
}
