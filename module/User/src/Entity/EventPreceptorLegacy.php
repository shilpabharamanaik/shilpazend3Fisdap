<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;


/**
 * Legacy Entity class for event/preceptor associations.
 *
 * @Entity
 * @Table(name="EventPreceptorData")
 */
class EventPreceptorLegacy extends EntityBaseClass
{
	/**
	 * @Id
	 * @Column(name="EventPreceptor_id", type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @ManyToOne(targetEntity="EventLegacy")
	 * @JoinColumn(name="Event_id", referencedColumnName="Event_id")
	 */
	protected $event;
	
	/**
	 * @ManyToOne(targetEntity="PreceptorLegacy")
	 * @JoinColumn(name="Preceptor_id", referencedColumnName="Preceptor_id")
	 */
	protected $preceptor;
        
	public function set_preceptor($value)
	{
		$this->preceptor = self::id_or_entity_helper($value, 'PreceptorLegacy');
	}
	
	public function set_event($value)
	{
		$this->event = self::id_or_entity_helper($value, 'EventLegacy');
	}
}