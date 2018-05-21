<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for weight units (lbs or kgs).
 *
 * @Entity
 * @Table(name="fisdap2_weight_units")
 */
class WeightUnit extends Enumerated
{
}
