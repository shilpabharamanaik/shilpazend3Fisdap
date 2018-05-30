<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for categories for the Medrill videos
 *
 * @Entity
 * @Table(name="fisdap2_medrill_category")
 */
class MedrillCategory extends Enumerated
{
}
