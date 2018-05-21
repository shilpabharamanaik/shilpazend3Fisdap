<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Legacy Marketing Billboards.
 *
 * @Entity
 * @Table(name="mktg_Campaign_Billboard_Data")
 */
class MarketingCampaignBillboardLegacy extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="CampBillboard_id", type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @ManyToOne(targetEntity="MarketingCampaignLegacy", inversedBy="campaign_audiences")
     * @JoinColumn(name="Campaign_id", referencedColumnName="Campaign_id")
     */
    protected $campaign;
    
    /**
     * @ManyToOne(targetEntity="MarketingBillboardLegacy", inversedBy="campaign_billboards")
     * @JoinColumn(name="Billboard_id", referencedColumnName="Billboard_id")
     */
    protected $billboard;
    
    /**
     * @Column(name="Message", type="string")
     */
    protected $message;
    
    /**
     * @Column(name="Height", type="integer")
     */
    protected $height;
}
