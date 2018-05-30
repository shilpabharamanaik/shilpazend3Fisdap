<?php namespace Fisdap\Api\Programs\Entities\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Fisdap\Entity\ProgramTypeLegacy;
use Fisdap\EntityUtils;

/**
 * Class Types
 *
 * @package Fisdap\Api\Programs\Entities\Traits
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait Types
{
    /**
     * @var ArrayCollection|ProgramTypeLegacy[]
     * @ManyToMany(targetEntity="ProgramTypeLegacy")
     * @JoinTable(name="ProgramTypeData",
     *  joinColumns={@JoinColumn(name="Program_id", referencedColumnName="Program_id")},
     *  inverseJoinColumns={@JoinColumn(name="ProgramType_id",referencedColumnName="ProgramType_id")})
     */
    protected $program_types;


    /**
     * @param ProgramTypeLegacy $programTypeLegacy
     */
    public function addProgramType(ProgramTypeLegacy $programTypeLegacy)
    {
        $this->program_types->add($programTypeLegacy);
    }


    /**
     * @param ProgramTypeLegacy[] $programTypes
     */
    public function addProgramTypes(array $programTypes)
    {
        foreach ($programTypes as $programType) {
            if (! $programType instanceof ProgramTypeLegacy) {
                continue;
            }
            
            $this->program_types->add($programType);
        }
    }


    /**
     * @return ProgramTypeLegacy[]
     */
    public function getProgramTypes()
    {
        return $this->program_types;
    }


    /**
     * Take an array of ProgramType IDs and attach them to this
     * Program clearing previously set ones
     * @param array $ids the ProgramType IDs to add
     * @return \Fisdap\Entity\ProgramLegacy
     *
     * @codeCoverageIgnore
     * @deprecated
     */
    public function setProgramTypeIds($ids)
    {
        $this->program_types->clear();

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        foreach ($ids as $id) {
            $this->program_types->add(EntityUtils::getEntity("ProgramTypeLegacy", $id));
        }

        return $this;
    }


    public function getProgramTypeIds()
    {
        $ids = [];
        foreach ($this->program_types as $type) {
            $ids[] = $type->id;
        }

        return $ids;
    }
    
    
    /**
     * Determine if this program trains students
     *
     * @return boolean
     */
    public function isTrainingProgram()
    {
        $programTypes = $this->getProgramTypeIds();

        //If no program types are set, assume we're dealing with a school
        if (empty($programTypes)) {
            return true;
        } else {
            return in_array(1, $programTypes);
        }
    }
    

    public function isHospital()
    {
        $program_repo = EntityUtils::getRepository('ProgramLegacy');

        // technically a program can be more than one type.
        // If we discover "hospital" (type id 2), consider the program a hospital
        $program_types = $program_repo->getProgramTypes($this->id);

        $hospital = false;

        if ($program_types) {
            foreach ($program_types as $type_id => $type_description) {
                if ($type_id == 2) {
                    $hospital = true;
                }
            }
        }

        return $hospital;
    }
}
