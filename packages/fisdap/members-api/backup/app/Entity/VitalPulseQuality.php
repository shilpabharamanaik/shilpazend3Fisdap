<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;


/**
 * Vital Pulse Quality
 * 
 * @Entity
 * @Table(name="fisdap2_vital_pulse_quality")
 * @HasLifecycleCallbacks
 */
class VitalPulseQuality extends Enumerated
{
    
}