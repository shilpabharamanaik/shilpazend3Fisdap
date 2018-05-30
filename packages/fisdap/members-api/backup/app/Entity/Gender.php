<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Gender.
 *
 * @Entity(repositoryClass="Fisdap\Data\Gender\DoctrineGenderRepository")
 * @Table(name="fisdap2_gender")
 */
class Gender extends Enumerated
{
}
