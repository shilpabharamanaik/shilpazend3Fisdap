<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

/**
 * Mental Orientation
 *
 * @Entity(repositoryClass="Fisdap\Data\MentalOrientation\DoctrineMentalOrientationRepository")
 * @Table(name="fisdap2_mental_orientation")
 */
class MentalOrientation extends Enumerated
{
}
