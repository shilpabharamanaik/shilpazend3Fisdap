<?php namespace Fisdap\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\Table;


/**
 * Class defining entities representing the National EMS Memorial Bike Ride
 * @Entity
 * @Table(name="fisdap2_bike_ride_events")
 */
class BikeRideEvent extends EntityBaseClass {
	
	/**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
	protected $id;
	
	/**
     * @Column(type="string")
     */
	protected $origin;
	
	/**
     * @Column(type="string")
     */
	protected $region;
	
	/**
     * @Column(type="string")
     */
	protected $destination;
	
	/**
     * @Column(type="date")
     */
	protected $start_date;
	
	/**
     * @Column(type="date")
     */
	protected $end_date;
	
	/**
     * @Column(type="string")
     */
	protected $notes;
        
     /**
     * @Column(type="string")
     */
	protected $housing;
	
     /**
     * @Column(type="string")
     */
	protected $transportation;   
     
     /**
     * @Column(type="string")
     */
	protected $jersey;
        
     /**
     * @Column(type="string")
     */
	protected $liability;
        
     /**
     * @Column(type="string")
     */
	protected $tshirt;
	
	/**
	 * @Column(type="text")
	 */
	protected $route_information;
	
	/**
	 * @Column(type="text", nullable=true)
	 */
	protected $email_list;
	
	/**
	 * @Column(type="string")
	 */
	protected $passcode;
    
	/**
     * @ManyToMany(targetEntity="BikeRideRole", inversedBy="roles")
     * @JoinTable(name="fisdap2_bike_events_roles",
     *  joinColumns={@JoinColumn(name="event_id", referencedColumnName="id")},
     *  inverseJoinColumns={@JoinColumn(name="role_id",referencedColumnName="id")})
     */
	protected $roles;
	
	public function init(){
		$this->roles = new ArrayCollection();
	}
	
	public function __clone()
	{
		if ($this->id) {
			//Reset the fields that shouldn't be duplicated
			$this->id = null;
			
			//Get roles before clearing
			$roles = $this->roles;
			
			//Re-initialize
			$this->init();
			
			//Readd roles
			$roleIds = array();
			foreach($roles as $role) {
				$roleIds[] = $role->id;
			}
			$this->setRolesIds($roleIds);
		}
	}
	
	/**
	* Get an array of role IDs
	*
	* @return array
	*/
	public function getRoleIds()
	{
		
		$roles = array();
		
		foreach($this->roles as $role)
		{
			$roles[] = $role->id;
		}
		
		return $roles;
	}
	
	public function setRolesIds($value)
	{
		
		if (is_null($value))
		{
			$value = array();
		}
		else if (!is_array($value))
		{
			$value = array($value);
		}
		
		$this->roles->clear();
		
		foreach($value as $id)
		{
			$role = self::id_or_entity_helper($id, 'BikeRideRole');
			$this->roles->add($role);
		}
	
	}
        
	
}
