<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;


/**
 * Legacy Entity class for Discounts.
 *
 * @Entity(repositoryClass="Fisdap\Data\Discount\DoctrineDiscountLegacyRepository")
 * @Table(name="PriceDiscount")
 */
class DiscountLegacy extends EntityBaseClass
{
	/**
	 * @Id
	 * @Column(name="pd_id", type="integer")
	 * @GeneratedValue
	 */
	protected $id;

    /**
     * @var \Fisdap\Entity\ProgramLegacy
     * @ManyToOne(targetEntity="ProgramLegacy")
     * @JoinColumn(name="Program_id", referencedColumnName="Program_id")
     */
    protected $program;

    /**
     * @var string
     * @Column(name="Type", type="string")
     */
    protected $type;

    /**
     * @var \DateTime
     * @Column(name="StartDate", type="date")
     */
    protected $start_date;

    /**
     * @var \DateTime
     * @Column(name="EndDate", type="date")
     */
    protected $end_date;

    /**
     * @var integer
     * @Column(name="Configuration", type="integer")
     */
    protected $configuration = 0;

    /**
     * @var boolean
     * @Column(name="Rewards", type="boolean")
     */
    protected $rewards = false;

    /**
     * @var string
     * @Column(name="Notes", type="text", nullable=true);
     */
    protected $notes;

    /**
     * @var double
     * @Column(name="PercentOff", type="decimal", scale=5, precision=8)
     */
    protected $percent_off;

    public function init()
    {
        $this->start_date = new \DateTime("0000-00-00");
        $this->end_date = new \DateTime("0000-00-00");
    }

	public function getDiscountedPrice($originalPrice)
	{
        return round(($originalPrice - ($originalPrice * $this->percent_off * .01)), 2, PHP_ROUND_HALF_DOWN);
	}

	/**
	 * Get a summary for this discount
	 * @param integer $config the configuration that we care about
	 * @return string
	 */
	public function getSummary($config = 0)
	{
		if ($config > 0) {
			$config = $config & $this->configuration;
		} else {
			$config = $this->configuration;
		}

		return round($this->percent_off) . "% " . Product::getProductSummary($config, $this->program->profession->id);
	}
}
