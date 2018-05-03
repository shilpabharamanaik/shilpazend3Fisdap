<?php namespace Fisdap\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Fisdap\EntityUtils;


/**
 *  
 * @Entity(repositoryClass="Fisdap\Data\Window\DoctrineWindowRepository")
 * @Table(name="fisdap2_windows")
 */
class Window extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(name="id", type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @var ProgramLegacy
     * @ManyToOne(targetEntity="ProgramLegacy")
     * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     */
    protected $program;

    /**
     * @var Slot
     * @ManyToOne(targetEntity="Slot", inversedBy="windows")
     */
    protected $slot;

    /**
     * @var string is this window active
     * @Column(type="boolean")
     */
    protected $active = true;
    
    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $start_date;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $end_date;

    /**
     * @var OffsetType
     * @ManyToOne(targetEntity="OffsetType")
     * @JoinColumn(name="offset_type_start", referencedColumnName="id")
     */
    protected $offset_type_start;
    
    /**
     * @var array
     * @Column(type="array")
     */
    protected $offset_value_start;
    
    /**
     * @var OffsetType
     * @ManyToOne(targetEntity="OffsetType")
     * @JoinColumn(name="offset_type_end", referencedColumnName="id")
     */
    protected $offset_type_end;
    
    /**
     * @var array
     * @Column(type="array")
     */
    protected $offset_value_end;

    /**
     * @var boolean is this window a default? It will behave a bit differently, and will not be associated with a slot
     * @Column(type="boolean")
     */
    protected $default_window = false;
    
    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="WindowConstraint", mappedBy="window", cascade={"persist","remove"})
     */
    protected $constraints;

    public function init()
    {
        $this->constraints = new ArrayCollection;
    }
    
    public function set_start_date($datetime)
    {
        // make sure hte datetime is at midnight the day of
        if(is_string($datetime)){
            $datetime = new \DateTime($datetime);
        }
        
        $start_date = new \DateTime($datetime->format("m/d/Y") . ' 00:00:00');
        $this->start_date = self::string_or_datetime_helper($start_date);
    }
    
    public function set_end_date($datetime)
    {
        // make sure the datetime is at 23:59:59 the day of
        if(is_string($datetime)){
            $datetime = new \DateTime($datetime);
        }
        
        $end_date = new \DateTime($datetime->format("m/d/Y") . ' 23:59:59');
        $this->end_date = self::string_or_datetime_helper($end_date);
    }
    
    public function set_offset_type_start($value)
    {
        $this->offset_type_start = self::id_or_entity_helper($value, 'OffsetType');
    }
    
    public function set_offset_type_end($value)
    {
        $this->offset_type_end = self::id_or_entity_helper($value, 'OffsetType');
    }

    /**
     * Add association between Window and Constraint
     *
     * @param \Fisdap\Entity\WindowConstraint $constraint
     */
    public function addConstraint(WindowConstraint $constraint)
    {
        $this->constraints->add($constraint);
        $constraint->window = $this;
    }
    
    public function getWhenDescription()
    {
        $today = new \DateTime();
        if($this->start_date > $today){
            $when = "Sign up opens on " . $this->start_date->format("M j, Y") . ".";
		}
        else {
            if($this->end_date < $today){
                $when = "Sign up closed on " . $this->end_date->format("M j, Y") . ".";
            }
            else {
				$when = ($this->end_date->format("j") == $today->format("j")) ? "Sign up by today." : "Sign up by " . $this->end_date->format("M j, Y") . ".";
            }
        }
        
        return $when;
    }
    
    public function getStatus()
    {
        $today = new \DateTime();
        if($this->start_date > $today){ $stat = "not-open-yet"; }
        else { $stat = ($this->end_date < $today) ? "closed" : "open";}
        return $stat;
    }
    
    public function getWhoDescription()
    {
		$certLevels = array();
		$certIds = array();
        $classSections = array();
		$groupsIds = array();
        
        foreach($this->constraints as $constraint){
            $entityName = $constraint->constraint_type->entity_name;
            foreach($constraint->values as $val){
              //  $valueEntity = \Fisdap\EntityUtils::getEntity($entityName, $val->value);
                if($entityName == "CertificationLevel"){
					//$certLevels[] = $valueEntity->description . "s";
					$certIds[] = $val->value;
				}
                else {
					//$classSections[] = $valueEntity->name;
					$groupsIds[] = $val->value;
				}
            }
        }
        
        $numOfCertLevels = count($certLevels);
        $descript  = "";
		$descript .= (($numOfCertLevels == 3) || ($numOfCertLevels == 0)) ? "All students " : $this->getConstraintDescription($certLevels, null);
        $descript .= $this->getConstraintDescription($classSections, " in ");

		$who = array();
		$who['description'] = $descript;
		$who['certs'] = $certIds;
		$who['groups'] =  $groupsIds;
		
        return $who;
    }
    
    public function getConstraintDescription($collection, $phrase)
    {
        if($collection){
            $description = $phrase;
            $count = 0;
            foreach($collection as $item){
				$description .= ($count != 0) ? " or " . $item : $item;
                $count++;
            }
        }
        return $description;
    }
    
    public function clearConstraints($flush = true)
    {
        if($this->constraints){
            foreach($this->constraints as $constraint){
                
                if($constraint->values){
                    foreach($constraint->values as $constraint_val){
                        $constraint->values->removeElement($constraint_val);
                        $constraint_val->delete($flush);
                    }
                }
                
                // now remove the constraint itself
                $this->constraints->removeElement($constraint);
                $constraint->delete($flush);
                $this->save($flush);
            }
        }
    }
    
    public function addConstraintsFromArray($constraint_type_id, $constraint_vals)
    {
        if($constraint_vals){
            $constraint = EntityUtils::getEntity('WindowConstraint');
            $constraint->set_constraint_type($constraint_type_id);
            
            foreach($constraint_vals as $id => $description){
                $constraintValue = EntityUtils::getEntity('WindowConstraintValue');
                $constraintValue->value = $id;
                $constraintValue->description = $description;
                $constraint->addValue($constraintValue);
            }
            
            $this->addConstraint($constraint);
        }
    }

    public function calculateOffsetDate($type_id, $offset_value, $event_start)
    {
        if($type_id == 1){
            // static!
            $date = $offset_value[0];
        }
        else if($type_id == 2){
            // interval!
            $date = date("Y-m-d", strtotime("-" . $offset_value[0] . " " . $offset_value[1], strtotime($event_start->format("Y-m-d"))));
        }
        else if($type_id == 3){
            // previous month!
            $month_before = date("Y-m-d", strtotime("-1 month", strtotime($event_start->format("Y-m-d"))));
            $month_date = new \DateTime($month_before);
            $date = new \DateTime($month_date->format("m") . "/" . $offset_value[0] . "/" . $month_date->format("Y"));
        }
        else if($type_id == 4){
            // today!
            $date = new \DateTime();
        }

        return $date;
    }

}
