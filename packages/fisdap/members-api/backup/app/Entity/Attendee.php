<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Class defining entities representing Attendees for workshop events
 *
 * @Entity
 * @Table(name="fisdap2_ws_attendees")
 */
class Attendee extends EntityBaseClass
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
      * @Column(type="boolean")
      */
    protected $emailed = 0;
    
    /**
     * @Column(type="string")
     */
    protected $user_name;
    
    /**
     * @Column(type="integer")
     */
    protected $cert_lvl;
    
    /**
     * @Column(type="integer")
     */
    protected $cert_lvl_taught;
    
    /**
     * @Column(type="string")
     */
    protected $cert_num;
    
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
    protected $organization;
    
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
    protected $phone;

    /**
     *@ManyToOne(targetEntity="Workshop", inversedBy="attendees")
     */
    protected $workshop;
    

    public function set_workshop($value)
    {
        $this->workshop = self::id_or_entity_helper($value, "Workshop");
    }
}
