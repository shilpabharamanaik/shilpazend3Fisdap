<?php namespace User\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;

/**
 * Class defining entities representing roles in the National EMS Memorial Bike Ride
 * @Entity
 * @Table(name="fisdap2_bike_ride_roles")
 */
class BikeRideRole extends Enumerated
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
    protected $name;
    
    /**
     * @Column(type="decimal")
     */
    protected $total_price;
    
    /**
     * @Column(type="decimal")
     */
    protected $price_per_day;
    
    /**
     * @ManyToMany(targetEntity="BikeRideEvent", mappedBy="roles")
     */
    protected $events;
    
    public function init()
    {
        $this->events = new ArrayCollection();
    }
    
    public function getSummary()
    {
        if ($this->total_price == $this->price_per_day) {
            return $this->name . " -- $" . $this->total_price . " total.";
        } else {
            return $this->name . " -- $" . $this->total_price . " total " . "or $" . $this->price_per_day . "/day.";
        }
    }
    
    public static function getFormOptions($na = false, $sort=true, $displayName = "name")
    {
        $options = array();
        $repo = EntityUtils::getEntityManager()->getRepository(get_called_class());
        $results = $repo->findAll();
        
        foreach ($results as $result) {
            if ($result->id != -1) {
                $tempOptions[$result->id] = $result->getSummary();
            }
        }
        
        if ($sort) {
            asort($tempOptions);
        }
        
        if ($na) {
            $options[0] = "N/A";
            foreach ($tempOptions as $id => $name) {
                $options[$id] = $name;
            }
        } else {
            $options = $tempOptions;
        }

        return $options;
    }
}
