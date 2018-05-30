<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Fisdap\EntityUtils;

/**
 * Airway
 *
 * @Entity(repositoryClass="Fisdap\Data\Skill\DoctrineAirwayRepository")
 * @Table(name="fisdap2_airways")
 * @HasLifecycleCallbacks
 */
class Airway extends Skill
{
    const viewScriptName = "airway";
    
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @ManyToOne(targetEntity="AirwayProcedure")
     */
    protected $procedure;
    
    /**
     * @Column(type="decimal", scale=2, precision=4, nullable=true)
     */
    protected $size;
    
    /**
     * @Column(type="integer", nullable=true)
     */
    protected $attempts;
    
    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $success;
    
    /**
    * @Column(type="integer", nullable=true)
    */
    protected $skill_order;
    
    /**
     * @var \Fisdap\Entity\AirwayManagement
     * @OneToOne(targetEntity="AirwayManagement", mappedBy="airway", cascade={"persist","remove"})
     */
    protected $airway_management;
    
    public function init()
    {
        $this->subject = EntityUtils::getEntity('Subject', 1);
    }

    public function setAirwayManagement($airwayManagement)
    {
        $this->airway_management = $airwayManagement;
    }

    public function getAirwayManagement()
    {
        return $this->airway_management;
    }

    public function set_procedure($value)
    {
        $this->procedure = self::id_or_entity_helper($value, 'AirwayProcedure');
    }

    public function setProcedure(AirwayProcedure $airwayProcedure)
    {
        $this->procedure = $airwayProcedure;
    }

    public function getProcedure()
    {
        return $this->procedure;
    }

    public function getViewScriptName()
    {
        return self::viewScriptName;
    }
    
    public function getProcedureText($html=true)
    {
        $line2 = "";
        if ($this->success !== null) {
            $successText = ($this->success)?'Successful ':'Unsuccessful ';
        } else {
            $successText = "";
        }
        $performedText = ($this->performed_by)?'Performed':'Observed';
        
        if ($this->size != '') {
            $line2 .= "Size: {$this->size};";
        }
        
        if ($this->attempts != '') {
            $line2 .= " {$this->attempts} attempts";
        }

        if (!is_null($this->procedure) && !is_null($this->procedure->name)) {
            $procedureName = $this->procedure->name;
        } else {
            $procedureName = "";
        }

        if ($html) {
            $line1 = "<span class='summary-header {$this->shift->type}'>$successText{$procedureName} ($performedText)</span><br />";

            $line2 = "<span class='summary-details'>$line2</span>";
        
            return $line1 . $line2;
        } else {
            $line1 = "$successText{$procedureName} ($performedText)\n";
        
            return ucwords(self::viewScriptName) . "\n" . $line1 . $line2 . "\n";
        }
    }
    
    public function countsTowardGoal($dataReqs)
    {
        if (!$this->success) {
            return 0;
        } else {
            return parent::countsTowardGoal($dataReqs);
        }
    }
    
    public static function countsTowardGoalSQL($airway, $dataReqs)
    {
        if (!$airway['success']) {
            return 0;
        } else {
            return parent::countsTowardGoalSQL($airway, $dataReqs);
        }
    }
    
    public static function getAllByShiftSQL($shiftId)
    {
        $query = "SELECT * FROM fisdap2_airways WHERE shift_id = " . $shiftId;
        return \Zend_Registry::get('db')->query($query)->fetchAll();
    }
    
    public function getHookIds()
    {
        switch ($this->shift->type) {
            case "field":
                return array(36);
            case "clinical":
                return array(59);
            case "lab":
                return array(82);
            default:
                return array();
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $skills = parent::toArray();
        $skills['procedureId'] = $this->getProcedure() ? $this->getProcedure()->id : null;
        $skills['size'] = floatval($skills['size']);
        $skills['airwayManagement'] = ($this->getAirwayManagement() ? $this->getAirwayManagement()->toArray() : null);
        
        return $skills;
    }
}
