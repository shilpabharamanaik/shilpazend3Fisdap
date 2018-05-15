<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;

/**
 * Vital Lung Sound
 *
 * @Entity
 * @Table(name="fisdap2_vital_lung_sound")
 * @HasLifecycleCallbacks
 */
class VitalLungSound extends Enumerated
{
}
