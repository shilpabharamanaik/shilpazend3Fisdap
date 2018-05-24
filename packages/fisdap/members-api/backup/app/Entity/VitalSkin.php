<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;


/**
 * Vital Skin
 * 
 * @Entity
 * @Table(name="fisdap2_vital_skin")
 * @HasLifecycleCallbacks
 */
class VitalSkin extends Enumerated
{
    
}