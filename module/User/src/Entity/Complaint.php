<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Complaint.
 *
 * @Entity
 * @Table(name="fisdap2_complaint")
 */
class Complaint extends Enumerated
{
    /**
     * @Column(type="integer", length=2, nullable=true)
     */
    protected $goal_def_id;

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
    public function toArray()
    {
        return array_merge(parent::toArray(), ['goal_def_id' => $this->getGoalDefId()]);
    }
}
