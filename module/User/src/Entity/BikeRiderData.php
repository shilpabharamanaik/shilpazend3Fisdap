<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Class defining entities representing Biker Data in the National EMS Memorial Bike Ride
 * @Entity
 * @Table(name="fisdap2_bike_rider_data")
 */
class BikeRiderData extends EntityBaseClass
{
    
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @Column(type="string")
     */
    protected $first_name;
    
    /**
     * @Column(type="string")
     */
    protected $last_name;
    
    /**
     * @Column(type="string")
     */
    protected $address1;
    
    /**
     * @Column(type="string")
     */
    protected $address2;
    
    /**
     * @Column(type="string")
     */
    protected $city;
    
    /**
     * @Column(type="string")
     */
    protected $state;
    
    /**
     * @Column(type="string")
     */
    protected $zipcode;
    
    /**
     * @Column(type="string")
     */
    protected $email;
    
    /**
     * @Column(type="string")
     */
    protected $home_phone;
    
    /**
     * @Column(type="string")
     */
    protected $work_phone;
    
    /**
     * @Column(type="string")
     */
    protected $cell_phone;
    
    /**
     * @Column(type="string")
     */
    protected $emergency_contact;
    
    /**
     * @Column(type="string")
     */
    protected $emergency_relation;
    
    /**
     * @Column(type="string")
     */
    protected $emergency_phone;
    
    /**
     * @Column(type="string")
     */
    protected $days;
    
    /**
     * @Column(type="string")
     */
    protected $jersey_size;
    
    /**
     * @Column(type="string")
     */
    protected $shirt_size;
    
    /**
     * @Column(type="text")
     */
    protected $special_needs;
    
    /**
     * @Column(type="text")
     */
    protected $why_ride;
    
    /**
     * @Column(type="text")
     */
    protected $suggestions;
    
    /**
     * @Column(type="boolean")
     */
    protected $liability;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $transaction_id;
    
    /**
     * @Column(type="boolean")
     */
    protected $paid = 0;
    
    /**
     * @Column(type="integer")
     */
    protected $estimate_guest_count;
    
    /**
     *@ManyToOne(targetEntity="BikeRideEvent")
     */
    protected $event;
    
    /**
     * @ManyToOne(targetEntity="BikeRideRole")
     */
    protected $role;
    
    public function set_role($value)
    {
        $this->role = self::id_or_entity_helper($value, "BikeRideRole");
    }
    
    public function set_event($value)
    {
        $this->event = self::id_or_entity_helper($value, "BikeRideEvent");
    }
    
    public function getTotalCost()
    {
        $cost = $this->role->price_per_day * count(explode(",", $this->days));
        $maxcost = $this->role->total_price;
        
        if ($maxcost < $cost) {
            return $maxcost;
        } else {
            return $cost;
        }
    }
}
