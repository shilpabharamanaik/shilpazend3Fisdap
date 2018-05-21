<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;
use Happyr\DoctrineSpecification\Exception\InvalidArgumentException;

/**
 * Iv
 *
 * @Entity(repositoryClass="Fisdap\Data\Skill\DoctrineIvRepository")
 * @Table(name="fisdap2_ivs")
 * @HasLifecycleCallbacks
 */
class Iv extends Skill
{
    const viewScriptName = "iv";
    
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @ManyToOne(targetEntity="IvProcedure")
     */
    protected $procedure;
    
    /**
     * @Column(type="integer", nullable=true)
     */
    protected $gauge;
    
    /**
     * @ManyToOne(targetEntity="IvSite")
     */
    protected $site;
    
    /**
     * @ManyToOne(targetEntity="IvFluid")
     */
    protected $fluid;
    
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
    
    
    public function init()
    {
        $this->subject = EntityUtils::getEntity('Subject', 1);
    }

    public function set_site($value)
    {
        $this->site = self::id_or_entity_helper($value, "IvSite");
    }

    public function set_procedure($value)
    {
        $this->procedure = self::id_or_entity_helper($value, "IvProcedure");
    }

    public function set_fluid($value)
    {
        $this->fluid = self::id_or_entity_helper($value, "IvFluid");
    }

    public function setSite(IvSite $ivSite)
    {
        $this->site = $ivSite;
    }

    public function getSite()
    {
        return $this->site;
    }

    public function setProcedure(IvProcedure $ivProcedure)
    {
        $this->procedure = $ivProcedure;
    }

    public function getProcedure()
    {
        return $this->procedure;
    }

    public function setFluid(IvFluid $ivFluid)
    {
        $this->fluid = $ivFluid;
    }

    public function getFluid()
    {
        return $this->fluid;
    }

    public function setSize($size)
    {
        if ($this->getProcedure()) {
            if ($this->getProcedure()->id === 1 || $this->getProcedure()->id === 3 || $this->getProcedure()->id === 8) {
                $eo = 'even';
                $tmpSize = $size;
                $string = '14-24';
                if ($tmpSize % 2 || $tmpSize < 14 || $tmpSize > 24) {
                    throw new InvalidArgumentException('IV gauge size must be an ' . $eo . ' integer between ' . $string . ' Given size is ' . $size);
                }
            } elseif ($this->getProcedure()->id === 2) {
                if ($size != 15 && $size != 25 && $size != 45) {
                    throw new InvalidArgumentException('IV gauge size must be either 15, 25, or 45. Given size is '.$size);
                }
            }
        }

        return $this->gauge = $size;
    }

    public function setGauge($size)
    {
        $this->setSize($size);
    }

    public function getViewScriptName()
    {
        return self::viewScriptName;
    }
    
    public function getProcedureText($html=true)
    {
        if ($this->success !== null) {
            $successText = ($this->success)?'Successful ':'Unsuccessful ';
        } else {
            $successText = "";
        }
        $performedText = ($this->performed_by)?'Performed':'Observed';

        $lineTwoArray = array();
        if ($this->gauge) {
            $lineTwoArray[] = $this->gauge . " gauge";
        }
        if ($this->site->name) {
            $lineTwoArray[] = $this->site->name . " " . $this->site->side;
        }
        if ($this->attempts) {
            $lineTwoArray[] = $this->attempts . " attempts";
        }

        if (!is_null($this->procedure) && !is_null($this->procedure->name)) {
            $procedureName = $this->procedure->name;
        } else {
            $procedureName = "";
        }

        $shiftType = "";
        if (!is_null($this->shift) && !is_null($this->shift->type)) {
            $shiftType = $this->shift->type;
        }

        if ($html) {
            $line1 = "<span class='summary-header {$shiftType}'>$successText{$procedureName} ($performedText)</span><br />";
            $line2 = "<span class='summary-details'>" . implode("; ", $lineTwoArray) . "</span>";

            return $line1 . $line2;
        } else {
            $line1 = "$successText{$procedureName} ($performedText)\n";
            $line2 = implode("; ", $lineTwoArray);

            return ucwords(self::viewScriptName) . "\n" . $line1 . $line2 . "\n\n";
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
    
    public static function countsTowardGoalSQL($iv, $dataReqs)
    {
        if (!$iv['success']) {
            return 0;
        } else {
            return parent::countsTowardGoalSQL($iv, $dataReqs);
        }
    }
    
    public static function getAllByShiftSQL($shiftId)
    {
        $query = "SELECT * FROM fisdap2_ivs WHERE shift_id = " . $shiftId;
        return \Zend_Registry::get('db')->query($query)->fetchAll();
    }
    
    public function getHookIds()
    {
        switch ($this->shift->type) {
            case "field":
                return array(26, 27);
            case "clinical":
                return array(56, 57);
            case "lab":
                return array(79, 80);
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
        $skills['size'] = $skills['gauge'];
        $skills['ivSiteId'] = $this->getSite() ? $this->getSite()->id : null;
        $skills['fluidId'] = $this->getFluid() ? $this->getFluid()->id : null;

        unset(
            $skills['gauge']
        );

        return $skills;
    }
}
