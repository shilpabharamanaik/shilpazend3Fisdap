<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;

/**
 * Other Intervention
 *
 * @Entity(repositoryClass="Fisdap\Data\Skill\DoctrineOtherInterventionRepository")
 * @Table(name="fisdap2_other_interventions")
 * @HasLifecycleCallbacks
 */
class OtherIntervention extends Skill
{
	const viewScriptName = "other";
	
    /**
	 * @var integer
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;
    
    /**
     * @ManyToOne(targetEntity="OtherProcedure")
     */
    protected $procedure;
    
    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $success;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $size;
    
    /**
     * @Column(type="integer", nullable=true)
     */
    protected $attempts;
	
	/**
     * @Column(type="integer", nullable=true)
     */
    protected $skill_order;
	
	
	public function init()
	{
		$this->subject = EntityUtils::getEntity('Subject', 1);
	}
	
	public function set_procedure($value)
	{
		$this->procedure = self::id_or_entity_helper($value, 'OtherProcedure');
	}
	
	public function setProcedure(OtherProcedure $otherProcedure)
	{
		$this->procedure = $otherProcedure;
	}

	public function getProcedure()
	{
		return $this->procedure;
	}

	public function getViewScriptName()
	{
		return self::viewScriptName;
	}
	
	public function getProcedureText($html=true){
	    $line2 = "";
		if ($this->success !== null) {
			$successText = ($this->success)?'Successful ':'Unsuccessful ';			
		} else {
            $successText = "";
        }
		$performedText = ($this->performed_by)?'Performed':'Observed';
		
		$details = array();
		
		if (isset($this->size)) {
			$details[] = "Size: " . $this->size;
		}
		
		if (isset($this->attempts)) {
			$details[] = "Attempts: " . $this->attempts;
		}

        $procedureName = "";
        if (!is_null($this->procedure) && !is_null($this->procedure->name)) {
            $procedureName = $this->procedure->name;
        }

        $shiftType = "";
        if (!is_null($this->shift) && !is_null($this->shift->type)) {
            $shiftType = $this->shift->type;
        }
		
		if ($html) {
			$line1 = "<span class='summary-header {$shiftType}'>$successText{$procedureName} ($performedText)</span><br />";
			$line2 = "<span class='summary-details'>" . implode("; ", $details) . "</span>";
			
			return $line1 . $line2;
		} else {
			$line1 = "$successText{$procedureName} ($performedText)\n";
			if (count($details)) {
				$line2 = implode("; ", $details) . "\n";
			} else {
			    $line2 = "";
            }
			
			return ucwords(self::viewScriptName) . "\n" . $line1 . $line2 . "\n";
		}
	}
	
	public function getHookIds()
	{
		return array();
	}
	
	/**
	 * @return array
	 */
	public function toArray()
	{
		$skills = parent::toArray();
        $skills['otherInterventionId'] = $this->id;
		$skills['procedureId'] = $this->getProcedure() ? $this->getProcedure()->id : null;

		return $skills;
	}
}
