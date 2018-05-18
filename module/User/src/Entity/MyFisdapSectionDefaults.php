<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;


/**
 * MyFisdapWidgetData
 * 
 * @Entity
 * @Table(name="fisdap2_my_fisdap_section_defaults")
 * @HasLifecycleCallbacks
 */
class MyFisdapSectionDefaults extends EntityBaseClass
{
	/**
	 * @var integer
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @ManyToOne(targetEntity="MyFisdapWidgetDefinition", inversedBy="portfolioOptions")
	 */
	protected $widget;
	
	/**
	 * @Column(type="string")
	 */
	protected $section;
	
	/**
	 * @Column(type="boolean")
	 */
	protected $is_required;
	
	/**
	 * @Column(type="integer")
	 */
	protected $column_position;
}