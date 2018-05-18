<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;


/**
 * Vital Resp Quality
 * 
 * @Entity
 * @Table(name="fisdap2_vital_resp_quality")
 * @HasLifecycleCallbacks
 */
class VitalRespQuality extends Enumerated
{
    
}