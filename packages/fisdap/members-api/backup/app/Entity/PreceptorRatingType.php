<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Preceptor Rating Types
 *
 * @Entity
 * @Table(name="fisdap2_preceptor_rating_type")
 */
class PreceptorRatingType extends Enumerated
{
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
