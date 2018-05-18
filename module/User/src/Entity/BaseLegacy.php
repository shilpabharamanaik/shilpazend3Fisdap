<?php namespace User\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;


/**
 * Entity class for Legacy Bases.
 * 
 * @Entity(repositoryClass="Fisdap\Data\Base\DoctrineBaseLegacyRepository")
 * @Table(name="AmbServ_Bases")
 * @HasLifecycleCallbacks
 */
class BaseLegacy extends EntityBaseClass
{
	/**
	 * @Id
	 * @Column(name="Base_id", type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
    /**
	 * @ManyToOne(targetEntity="SiteLegacy", inversedBy="bases")
	 * @JoinColumn(name="AmbServ_id", referencedColumnName="AmbServ_id")
	 */
	protected $site;
    
    /**
     * @Column(name="BaseName", type="string")
     */
    protected $name;
    
    /**
     * @Column(name="AmbServAbrev", type="string", nullable=true)
     */
    protected $abbreviation;
    
    /**
     * @Column(name="City", type="string", nullable=true)
     */
    protected $city;
    
    /**
     * @Column(name="State", type="string", nullable=true)
     */
    protected $state;
	
	/**
     * @Column(name="Address", type="string", nullable=true)
     */
    protected $address;
    
    /**
     * @Column(name="PostalCode", type="string", nullable=true)
     */
    protected $zip;
    
    /**
     * @Column(name="IPAddress", type="string", nullable=true)
     */
    protected $ip_address;
    
    /**
     * @Column(name="Type", type="string")
     */
    protected $type = "field";
    
    /**
	 * @codeCoverageIgnore
     * @deprecated
     * @Column(name="Active", type="boolean")
     */
    protected $active = true;
    
    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="ProgramBaseLegacy", mappedBy="base", cascade={"persist"})
     * @JoinColumn(name="Base_id", referencedColumnName="Base_id")
     */
     protected $program_base_associations;
	
    
    public function init()
	{
		$this->program_base_associations = new ArrayCollection();
	}
	
	public function getBaseAssocationByProgram($program_id)
	{
		if($this->program_base_associations){
			foreach($this->program_base_associations as $assoc){
				if($assoc->program->id == $program_id){
					return $assoc;
				}
			}
		}
		
		return false;
	}

	public function getId() {
		return $this->id;
	}
	
	public static function getBases($siteId, $programId, $active = true, $friendlyNames = false)
	{
		$bases = array();
		
		$associations = EntityUtils::getRepository('BaseLegacy')->getBaseAssociationsByProgram($siteId, $programId, $active);
		foreach ($associations as $association) {
			if ($friendlyNames) {
				$name = $association->base->name;
				if($name == "CCL"){$name = "Cardiac Cath. Lab";}
				else if($name == "CCU"){$name = "Cardiac Care Unit";}
				else if($name == "IVTeam"){$name = "IV Team";}
				else if($name == "PreOp"){$name = "Pre Op";}
				else if($name == "PostOp"){$name = "Post Op";}
				else if($name == "Psych"){$name = "Psychiatric Unit";}
				else if($name == "Respiratory"){$name = "Respiratory Therapy";}
				else if($name == "Urgent"){$name = "Urgent Care";}
				else if($name == "NICU"){$name = "Neonatal ICU";}
				else if($name == "Burn"){$name = "Burn Unit";}
				else if($name == "Labor"){$name = "Labor & Delivery";}
				
				$bases[$association->base->id] = $name;
			} else {
				$bases[$association->base->id] = $association->base->name;
			}
		}
		
		return $bases;
	}
	
	public static function getFriendlyName($name){
		if($name == "CCL"){$name = "Cardiac Cath. Lab";}
		else if($name == "CCU"){$name = "Cardiac Care Unit";}
		else if($name == "IVTeam"){$name = "IV Team";}
		else if($name == "PreOp"){$name = "Pre Op";}
		else if($name == "PostOp"){$name = "Post Op";}
		else if($name == "Psych"){$name = "Psychiatric Unit";}
		else if($name == "Respiratory"){$name = "Respiratory Therapy";}
		else if($name == "Urgent"){$name = "Urgent Care";}
		else if($name == "NICU"){$name = "Neonatal ICU";}
		else if($name == "Burn"){$name = "Burn Unit";}
		else if($name == "Labor"){$name = "Labor & Delivery";}
		
		return $name;
	}
	
	public static function getAllDefaultDepartmentNames()
	{
		return array(
				"Anesthesia"	=> "Anesthesia",
				"Burn" 			=> "Burn Unit",
				"CCL" 			=> "Cardiac Cath. Lab",
				"CCU" 			=> "Cardiac Care Unit",
				"Clinic"		=> "Clinic",
				"ER" 			=> "ER",
				"ICU"			=> "ICU",
				"IVTeam"		=> "IV Team",
				"Labor"			=> "Labor & Delivery",
				"NICU"			=> "Neonatal ICU",
				"OR"			=> "OR",
				"PostOp"		=> "Post Op",
				"PreOp"			=> "Pre Op",
				"Psych"			=> "Psychiatric Unit",
				"Respiratory"	=> "Respiratory Therapy",
				"Triage"		=> "Triage",
				"Urgent"		=> "Urgent Care");
	}
	
	public function getAddressString()
	{
		$addressParts = array();
		
		if(trim($this->address) != ''){
			$addressParts[] = trim($this->address);
		}
		
		if(trim($this->city) != ''){
			$addressParts[] = trim($this->city);
		}
		
		if((trim($this->state) . " " . trim($this->zip)) != ' '){
			$addressParts[] = trim($this->state) . " " . trim($this->zip);
		}
		
		return implode(", ", $addressParts);
	}
	
	public function getMapAddress()
	{
		$loseSpaces = str_replace(", ", ",", $this->getAddressString());
		return str_replace(" ", "+", $loseSpaces);
	}
	
	public function hasValidMapAddress()
	{
		// If they have the address and any two other points, return true...
		if(trim($this->address) != ''){
			$totalPoints = 0;

			if(trim($this->city) != '') $totalPoints++;
			if(trim($this->state) != '') $totalPoints++;
			if(trim($this->zip) != '') $totalPoints++;

			if($totalPoints >= 2){
				return true;
			}
		// Otherwise, return false.  No use without an address.
		}

		return false;
	}
}