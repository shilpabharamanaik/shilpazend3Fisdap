<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;


/**
 * Entity class for the transition course map's states.
 * 
 * @Entity(repositoryClass="Fisdap\Data\TCMapState\DoctrineTCMapStatesRepository")
 * @Table(name="fisdap2_tc_map_states")
 */
class TCMapState extends EntityBaseClass
{
	/**
	 * @Id
	 * @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id;

    /**
     * @Column(name="name", type="string")
     */
    protected $name;
	
	/**
     * @Column(name="abbreviation", type="string")
     */
    protected $abbreviation;
	
	/**
     * @Column(name="status", type="string")
    */
    protected $status;
	
	/**
     * @Column(name="color", type="string")
    */
    protected $color;
	
	/**
     * @Column(name="note", type="string")
    */
    protected $note;

    
    public function init()
	{
		
	}
	
}