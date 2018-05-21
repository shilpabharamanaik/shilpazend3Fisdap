<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Preceptor Ratings
 *
 * @Entity(repositoryClass="Fisdap\Data\PreceptorRating\DoctrinePreceptorRatingRepository")
 * @Table(name="fisdap2_preceptor_ratings")
 */
class PreceptorRating extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @ManyToOne(targetEntity="PreceptorRatingType")
     */
    protected $type;
    
    /**
     * @ManyToOne(targetEntity="PreceptorRatingRaterType")
     */
    protected $rater_type;
    
    /**
     * @ManyToOne(targetEntity="PreceptorSignoff", inversedBy="ratings", cascade={"persist","remove"})
     */
    protected $signoff;
    
    /**
     * @Column(type="integer")
     * TODO: Should this be enumerated?
     */
    protected $value;
    
    public function init()
    {
    }
    
    /**
     * @param PreceptorRatingType $ratingType
     */
    public function setRatingType(PreceptorRatingType $ratingType)
    {
        $this->type = $ratingType;
    }

    /**
     * @return PreceptorRatingType
     */
    public function getRatingType()
    {
        return $this->type->id;
    }

    /**
     * @param PreceptorRatingRaterType $raterType
     */
    public function setRatingRaterType(PreceptorRatingRaterType $raterType)
    {
        $this->rater_type = $raterType;
    }

    /**
     * @return PreceptorRatingRaterType
     */
    public function getRatingRaterType()
    {
        return $this->rater_type->id;
    }

    /**
     * @param PreceptorSignoff $signoff
     */
    public function setSignoff(PreceptorSignoff $signoff)
    {
        $this->signoff = $signoff;
    }
    
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @param $value
     * @codeCoverageIgnore
     * @deprecated
     *
     * @throws \Exception
     */
    public function set_rater_type($value)
    {
        $this->rater_type = self::id_or_entity_helper($value, "PreceptorRatingRaterType");
    }

    /**
     * @param $value
     * @codeCoverageIgnore
     * @deprecated
     *
     * @throws \Exception
     */
    public function set_type($value)
    {
        $this->type = self::id_or_entity_helper($value, "PreceptorRatingType");
    }

    /**
     * @param $value
     * @codeCoverageIgnore
     * @deprecated
     *
     * @throws \Exception
     */
    public function set_signoff($value)
    {
        $this->signoff = self::id_or_entity_helper($value, "PreceptorSignoff");
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();
        $array['raterType'] = $this->getRatingRaterType();
        $array['type'] = $this->getRatingType();

        return $array;
    }
}
