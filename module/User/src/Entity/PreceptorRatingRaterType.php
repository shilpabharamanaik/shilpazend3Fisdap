<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;


/**
 * Entity class for Preceptor Rating Rater Types
 * 
 * @Entity
 * @Table(name="fisdap2_preceptor_rating_rater_type")
 */
class PreceptorRatingRaterType extends Enumerated
{
	/***  These lines should be removed - all Enumerated classes have id and name ***/
    /**
	 * @var integer
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;
    
	/**
     * @Column(type="text")
     */
    protected $name;
}