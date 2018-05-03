<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;


/**
 * Entity class for Ethnicity.
 * 
 * @Entity(repositoryClass="Fisdap\Data\Ethnicity\DoctrineEthnicityRepository")
 * @Table(name="fisdap2_ethnicity")
 */
class Ethnicity extends Enumerated
{
}
