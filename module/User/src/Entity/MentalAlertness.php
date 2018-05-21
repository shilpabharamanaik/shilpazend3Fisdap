<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

/**
 * Mental Alertness
 *
 * @Entity(repositoryClass="Fisdap\Data\MentalAlertness\DoctrineMentalAlertnessRepository")
 * @Table(name="fisdap2_mental_alertness")
 */
class MentalAlertness extends Enumerated
{
}
