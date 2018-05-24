<?php namespace Fisdap\Api\Programs\Entities\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;
use Fisdap\Entity\BaseLegacy;
use Fisdap\Entity\ProgramBaseLegacy;


/**
 * Class Bases
 *
 * @package Fisdap\Api\Programs\Entities\Traits
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait Bases
{
    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="ProgramBaseLegacy", mappedBy="program", cascade={"persist","remove"})
     * @JoinColumn(name="Program_id", referencedColumnName="Program_id")
     */
    protected $program_base_associations;


    public function hasBase(BaseLegacy $base)
    {
        foreach ($this->program_base_associations as $association) {
            if ($association->base == $base) {
                return true;
            }
        }
        return false;
    }

    
    public function addBase(BaseLegacy $base, $active = 1)
    {
        if ($this->hasBase($base)) {
            return;
        }

        $association = new ProgramBaseLegacy();
        $association->base = $base;
        $association->program = $this;
        $association->active = $active;
        $association->save();

        $this->program_base_associations->add($association);
    }


    public function removeBase(BaseLegacy $base)
    {
        foreach ($this->program_base_associations as $association) {
            if ($association->base == $base) {
                $this->program_base_associations->removeElement($association);
                $base->program_base_associations->removeElement($association);
                $association->delete();
            }
        }
    }


    public function toggleBase(BaseLegacy $base, $active)
    {
        foreach ($this->program_base_associations as $association) {
            if ($association->base == $base) {
                $association->active = $active;
                $association->save();
            }
        }
    }
}
