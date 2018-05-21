<?php namespace User\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;

/**
 * Cardiac Intervention
 *
 * @Entity(repositoryClass="Fisdap\Data\Skill\DoctrineCardiacInterventionRepository")
 * @Table(name="fisdap2_cardiac_interventions")
 * @HasLifecycleCallbacks
 */
class CardiacIntervention extends Skill
{
    const viewScriptName = "cardiac";
    
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @ManyToOne(targetEntity="CardiacProcedure")
     */
    protected $procedure;
    
    /**
     * @ManyToOne(targetEntity="CardiacPacingMethod")
     */
    protected $pacing_method;
    
    /**
     * @ManyToOne(targetEntity="RhythmType")
     */
    protected $rhythm_type;
    
    /**
     * @ManyToOne(targetEntity="CardiacProcedureMethod")
     */
    protected $procedure_method;
    
    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $rhythm_performed_by;
    
    /**
     * @var boolean
     * @Column(type="boolean", nullable=true)
     */
    protected $twelve_lead;
    
    /**
     * @ManyToMany(targetEntity="CardiacEctopy")
     * @JoinTable(name="fisdap2_cardiac_interventions_ectopy",
     *  joinColumns={@JoinColumn(name="cardiac_intervention_id", referencedColumnName="id")},
     *  inverseJoinColumns={@JoinColumn(name="ectopy_id",referencedColumnName="id")})
     */
    protected $ectopies;
    
    public function init()
    {
        $this->ectopies = new ArrayCollection();
        $this->subject = EntityUtils::getEntity('Subject', 1);
    }

    public function set_procedure($value)
    {
        $this->procedure = self::id_or_entity_helper($value, "CardiacProcedure");
    }

    public function set_rhythm_type($value)
    {
        $this->rhythm_type = self::id_or_entity_helper($value, "RhythmType");
    }

    public function set_pacing_method($value)
    {
        $this->pacing_method = self::id_or_entity_helper($value, "CardiacPacingMethod");
    }

    public function set_procedure_method($value)
    {
        $this->procedure_method = self::id_or_entity_helper($value, "CardiacProcedureMethod");
    }

    public function setProcedure(CardiacProcedure $cardiacProcedure = null)
    {
        $this->procedure = $cardiacProcedure;
    }

    public function getProcedure()
    {
        return $this->procedure;
    }

    public function setRhythmType(RhythmType $rhythmType = null)
    {
        $this->rhythm_type = $rhythmType;
    }

    public function getRhythmType()
    {
        return $this->rhythm_type;
    }

    public function setPacingMethod(CardiacPacingMethod $cardiacPacingMethod = null)
    {
        $this->pacing_method = $cardiacPacingMethod;
    }

    public function getPacingMethod()
    {
        return $this->pacing_method;
    }

    public function setProcedureMethod(CardiacProcedureMethod $cardiacProcedureMethod = null)
    {
        $this->procedure_method = $cardiacProcedureMethod;
    }

    public function setRhythmPerformedBy($bool)
    {
        $this->rhythm_performed_by = boolval($bool);
    }

    public function setTwelveLead($bool)
    {
        $this->twelve_lead = boolval($bool);
    }

    public function getViewScriptName()
    {
        return self::viewScriptName;
    }

    public function set_ectopies($value)
    {
        if (is_null($value)) {
            $value = array();
        } elseif (!is_array($value)) {
            $value = array($value);
        }

        $this->ectopies->clear();

        foreach ($value as $id) {
            $ectopy = self::id_or_entity_helper($id, 'CardiacEctopy');
            $this->ectopies->add($ectopy);
        }
    }

    /**
     * Get an array of cardiac ectopy IDs
     *
     * @return array
     */
    public function get_ectopies()
    {
        $ectopies = array();

        foreach ($this->ectopies as $ectopy) {
            $ectopies[] = $ectopy->id;
        }

        return $ectopies;
    }

    public function getProcedureText($html=true)
    {
        $foundEctos = array();
        foreach ($this->ectopies as $ecto) {
            $foundEctos[] = $ecto->name;
        }

        $ectosText = implode(', ', $foundEctos);

        $lineOneText = "";
        $lineTwoText = "";

        $lineOnePieces = array();

        $lineOnePieces[] = $this->rhythm_type->name;

        if ($ectosText != '') {
            $lineOnePieces[] = "$ectosText";
        }

        if ($this->rhythm_performed_by) {
            $lineOnePieces[] = "(Interpreted)";
        }

        $lineOneText .= implode(' ', $lineOnePieces) . "; ";

        if ($this->procedure && $this->procedure->name) {
            $lineOneText .= "{$this->procedure->name} ({$this->getPerformedByText()})";
        }

        if ($this->twelve_lead === true) {
            $lineTwoText .= "12 Lead";
        }

        $shiftType = "";
        if (!is_null($this->shift) && !is_null($this->shift->type)) {
            $shiftType = $this->shift->type;
        }

        if ($html) {
            $line1 = "<span class='summary-header {$shiftType}'>" . $lineOneText . "</span><br />";
            $line2 = "<span class='summary-details'>" . $lineTwoText .  "</span>";
        } else {
            $line1 = ucwords(self::viewScriptName) . " \n" . $lineOneText . "\n";
            $line2 = $lineTwoText;
        }

        return $line1 . $line2 . "\n";
    }

    /**
     * This function determines if the specified student has legacy data.  Needed
     * for a specific report in the portfolio.
     */
    public static function hasLegacyData($studentId)
    {
        $em = EntityUtils::getEntityManager();
        $count = $em->createQuery("SELECT count(s) FROM \Fisdap\Entity\CardiacIntervention s WHERE s.rhythm_type IS null AND s.twelve_lead = 1 AND s.student = ?1")->setParameter(1, $studentId)->getSingleScalarResult();

        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getHookIds()
    {
        switch ($this->shift->type) {
            case "field":
                return array(38);
            case "clinical":
                return array(46);
            case "lab":
                return array(78);
            default:
                return array();
        }
    }

    /**
     * Used by Goals system for caclculating various skill based goals
     *
     * @param $shiftId
     * @return mixed
     */
    public static function getAllByShiftSQL($shiftId)
    {
        $query = "SELECT * FROM fisdap2_cardiac_interventions WHERE shift_id = " . $shiftId;
        return \Zend_Registry::get('db')->query($query)->fetchAll();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $skills = parent::toArray();
        $skills['cardiacId'] = $this->id;
        $skills['rhythmPerformed'] = $skills['rhythm_performed_by'];
        $skills['twelveLead'] = $skills['twelve_lead'];
        $skills['procedureId'] = $this->getProcedure() ? $this->getProcedure()->id : null;
        $skills['pacingMethodId'] = $this->getPacingMethod() ? $this->getPacingMethod()->id : null;
        $skills['rhythmTypeId'] = $this->getRhythmType() ? $this->getRhythmType()->id : null;
        $skills['procedureMethodId'] = $this->procedure_method ? $this->procedure_method->id : null;
        $skills['ectopyIds'] = $this->get_ectopies();

        unset(
            $skills['rhythm_performed_by'],
            $skills['twelve_lead']
        );

        return $skills;
    }
}
