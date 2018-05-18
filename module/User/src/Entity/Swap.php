<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;


/**
 * Swap
 * 
 * @Entity
 * @Table(name="fisdap2_swaps")
 */
class Swap extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @var ShiftRequest
     * @ManyToOne(targetEntity="ShiftRequest", inversedBy="swaps")
     */
    protected $request;
    
    /**
     * @var SlotAssignment
     * @ManyToOne(targetEntity="SlotAssignment")
     */
    protected $offer;
    
    /**
     * @Column(type="datetime")
     */
    protected $sent;
        
    /**
     * @var RequestState
     * @ManyToOne(targetEntity="RequestState")
     */
    protected $accepted;
    
    public function set_offer($value)
    {
	$this->offer = self::id_or_entity_helper($value, 'SlotAssignment');
    }
    
    public function set_accepted($value)
    {
	$this->accepted = self::id_or_entity_helper($value, 'RequestState');
    }
    
    /**
     * Determines whether or not this swap is pending 
     *
     */
    public function isPending()
    {
        if ($this->offer->slot->event->start_datetime->format('Y-m-d') <= date('Y-m-d')) {
	    if (($this->request->accepted->name == 'unset' && $this->request->approved->name != 'expired') ||
                ($this->request->accepted->name == 'accepted' && $this->request->approved->name == 'unset')) {
		$this->set_accepted(6);
                $this->request->set_accepted(1);
		$this->request->save();
	    }
            
	}
	if ($this->accepted->name == 'unset') {
	    return true;
	} else {
	    return false;
	}	
    }
    
    public function getStatus() {		
	if ($this->accepted->name == 'unset') {
	    return 'Pending';
	}
	
        return ucfirst($this->accepted->name);
		
    }
    
}
