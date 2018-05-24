<?php namespace Fisdap\Api\Programs\Entities\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\OneToMany;
use Fisdap\Entity\PreceptorLegacy;
use Fisdap\Entity\ProgramPreceptorLegacy;


/**
 * Class Preceptors
 *
 * @package Fisdap\Api\Programs\Entities\Traits
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait Preceptors
{
    /**
     * @var ArrayCollection|ProgramPreceptorLegacy[]
     * @OneToMany(targetEntity="ProgramPreceptorLegacy", mappedBy="program", cascade={"persist","remove"})
     */
    protected $program_preceptor_associations;


    public function addPreceptor(PreceptorLegacy $preceptor, $active = 1)
    {
        if ($this->getEntityRepository()->getAssociationCountByPreceptor($preceptor->id, $this->id)) {
            return;
        }

        $association = new ProgramPreceptorLegacy;
        $association->program = $this;
        $association->preceptor = $preceptor;
        $association->active = $active;
        $association->save();
        $this->program_preceptor_associations->add($association);
    }


    public function togglePreceptor(PreceptorLegacy $preceptor, $active)
    {
        foreach ($this->program_preceptor_associations as $association) {
            if ($association->preceptor == $preceptor) {
                $association->active = $active;
                $association->save();
            }
        }
    }
}
