<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use User\EntityUtils;

/**
 * Base class for associating programs to procedures.
 * @MappedSuperclass
 */
class ProgramProcedure extends EntityBaseClass
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="ProgramLegacy")
     * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     */
    protected $program;

    /**
     * @Column(type="boolean")
     */
    protected $included = true;

    public static function getProgramProcedureInclusion($procedureEnt, $procedureTypeName, $programId, $procedureId)
    {
        $result = self::getProgramProcedureEntity($procedureEnt, $procedureTypeName, $programId, $procedureId);

        return $result->included;
    }

    public static function getProgramProcedureEntity($procedureEnt, $procedureTypeName, $programId, $procedureId)
    {
        $repository = EntityUtils::getEntityManager()->getRepository($procedureEnt);

        $result = $repository->findOneBy(array('program' => $programId, $procedureTypeName => $procedureId));

        if ($result == null) {
            $newProc = new $procedureEnt();
            $newProc->program = EntityUtils::getEntity('ProgramLegacy', $programId);

            switch ($procedureTypeName) {
                case "airway":
                    $realProcedureEntName = "AirwayProcedure";
                    break;
                case "cardiac":
                    $realProcedureEntName = "CardiacProcedure";
                    break;
                case "iv":
                    $realProcedureEntName = "IvProcedure";
                    break;
                case "med_type":
                    $realProcedureEntName = "MedType";
                    break;
                case "other":
                    $realProcedureEntName = "OtherProcedure";
                    break;
                case "lab":
                    $realProcedureEntName = "LabAssessment";
                    break;
                default:
                    $realProcedureEntName = "FAILED" . $procedureEnt;
            }

            $newProc->$procedureTypeName = EntityUtils::getEntity($realProcedureEntName, $procedureId);

            return $newProc;
        } else {
            return $result;
        }
    }
}
