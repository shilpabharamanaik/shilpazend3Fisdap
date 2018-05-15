<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity for scenario reviews.
 *
 * @Entity
 * @Table(name="fisdap2_scenario_reviews")
 */
class ScenarioReview extends Timestampable
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @ManyToOne(targetEntity="Scenario")
     */
    protected $scenario;

    /**
     * @Column(type="text")
     */
    protected $review;
    
    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $reviewer;
}
