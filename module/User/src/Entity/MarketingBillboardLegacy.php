<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;


/**
 * Entity class for Legacy Marketing Billboards.
 * 
 * @Entity
 * @Table(name="mktg_Billboard_Table")
 */
class MarketingBillboardLegacy extends EntityBaseClass
{
	/**
	 * @Id
	 * @Column(name="Billboard_id", type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @OneToMany(targetEntity="MarketingCampaignBillboardLegacy", mappedBy="billboard")
	 * @JoinColumn(name="Billboard_id", referencedColumnName="Billboard_id")
	 */
	protected $campaign_billboards;
    
    /**
     * @Column(name="Location", type="string")
     */
    protected $location;
    
    /**
     * @Column(name="Width", type="integer")
     */
    protected $width;
    
    /**
     * @Column(name="Max_Height", type="integer")
     */
    protected $max_height;
    
    /**
     * @Column(name="Unique_Name", type="string")
     */
    protected $unique_name;
}