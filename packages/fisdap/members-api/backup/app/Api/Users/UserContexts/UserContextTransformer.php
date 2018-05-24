<?php namespace Fisdap\Api\Users\UserContexts;

use Fisdap\Api\Professions\CertificationLevels\CertificationLevelTransformer;
use Fisdap\Api\Programs\Transformation\ProgramTransformer;
use Fisdap\Api\Users\UserContexts\Roles\Instructors\InstructorTransformer;
use Fisdap\Api\Users\UserContexts\Roles\Students\StudentTransformer;
use Fisdap\Entity\InstructorLegacy;
use Fisdap\Entity\Role;
use Fisdap\Entity\StudentLegacy;
use Fisdap\Entity\UserContext;
use Fisdap\Fractal\Transformer;


/**
 * Prepares user context data for JSON output
 *
 * @package Fisdap\Api\Users
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class UserContextTransformer extends Transformer
{
    /**
     * @var string[]
     */
    protected $defaultIncludes = [
        'certification_level',
        'program',
        'studentRoleData',
        'instructorRoleData'
    ];


    /**
     * @param array|UserContext $userContext
     *
     * @return array
     */
    public function transform($userContext)
    {
        if ($userContext instanceof UserContext) {
            $userContext = $userContext->toArray();
        }

        if (isset($userContext['start_date'])) {
            $userContext['start_date'] = $this->formatDateTimeAsString($userContext['start_date']);
        }

        if (isset($userContext['end_date'])) {
            $userContext['end_date'] = $this->formatDateTimeAsString($userContext['end_date']);
        }

        if (isset($userContext['role'])) {
            $userContext['role_id']   = $userContext['role'] instanceof Role ? $userContext['role']->getId()   : $userContext['role']['id'];
            $userContext['role_name'] = $userContext['role'] instanceof Role ? $userContext['role']->getName() : $userContext['role']['name'];
            unset($userContext['role']);
        }

        return $userContext;
    }


    /**
     * @param $userContext
     *
     * @return \League\Fractal\Resource\Item|void
     * @codeCoverageIgnore
     */
    public function includeCertificationLevel($userContext)
    {
        $certificationLevel = $userContext instanceof UserContext ? $userContext->getCertificationLevel() : $userContext['certification_level'];

        if (empty($certificationLevel)) return;

        return $this->item($certificationLevel, new CertificationLevelTransformer);
    }


    /**
     * @param array|UserContext $userContext
     *
     * @return \League\Fractal\Resource\Item
     * @codeCoverageIgnore
     */
    public function includeProgram($userContext)
    {
        $program = $userContext instanceof UserContext ? $userContext->getProgram() : $userContext['program'];

        return $this->item($program, new ProgramTransformer);
    }


    /**
     * @param array|UserContext $userContext
     *
     * @return \League\Fractal\Resource\Item
     * @codeCoverageIgnore
     */
    public function includeStudentRoleData($userContext)
    {
        if ($userContext instanceof UserContext) {
            $roleData = $userContext->getRoleData();
            $studentRoleData = $roleData instanceof StudentLegacy ? $roleData : null;
        } else {
            $studentRoleData = isset($userContext['studentRoleData']) ? $userContext['studentRoleData'] : null;
        }

        if (isset($studentRoleData)) {
            return $this->item($studentRoleData, new StudentTransformer);
        }

        return null;
    }


    /**
     * @param array|UserContext $userContext
     *
     * @return \League\Fractal\Resource\Item
     * @codeCoverageIgnore
     */
    public function includeInstructorRoleData($userContext)
    {
        if ($userContext instanceof UserContext) {
            $roleData = $userContext->getRoleData();
            $instructorRoleData = $roleData instanceof InstructorLegacy ? $roleData : null;
        } else {
            $instructorRoleData = isset($userContext['instructorRoleData']) ? $userContext['instructorRoleData'] : null;
        }

        if (isset($instructorRoleData)) {
            return $this->item($instructorRoleData, new InstructorTransformer);
        }

        return null;
    }
} 