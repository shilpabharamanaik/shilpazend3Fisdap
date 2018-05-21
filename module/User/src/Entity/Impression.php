<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Impression.
 *
 * @Entity
 * @Table(name="fisdap2_impression")
 */
class Impression extends Enumerated
{
    /**
     * @Column(type="string", nullable=true)
     */
    protected $nsc_type;

    /**
     * @Column(type="integer", length=2, nullable=true)
     */
    protected $goal_def_id;

    public function isArrest()
    {
        return $this->nsc_type == "c-arrest";
    }

    public function isTrauma()
    {
        return $this->nsc_type == "trauma";
    }

    public static function getImpressionTypes()
    {
        return self::getFormOptions();
    }
    
    public static function getIdsByType($type, $format = 'array')
    {
        $impressions = self::getAll(true, 'nsc_type');
        $results = array();
        foreach ($impressions as $id => $this_type) {
            if ($this_type == $type) {
                $results[] = $id;
            }
        }
        
        if ($format == 'string') {
            return implode(", ", $results);
        }

        return $results;
    }

    /**
     * @return array
     */
    public function getGoalDefId()
    {
        return $this->goal_def_id;
    }

    /**
     * @return array
     */
    public function getNscType()
    {
        return $this->nsc_type;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge(parent::toArray(), ['nsc_type' => $this->getNscType(), 'goal_def_id' => $this->getGoalDefId()]);
    }
}
