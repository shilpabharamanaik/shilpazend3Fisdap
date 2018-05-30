<?php namespace Fisdap\Api\Users\UserContexts\Roles;

use Fisdap\Api\Users\UserContexts\Roles\Jobs\CreateRoleData;
use Fisdap\Api\Users\UserContexts\Roles\Jobs\Models\CommunicationPreferences;
use Fisdap\Entity\Ethnicity;
use Fisdap\Entity\InstructorLegacy;
use Fisdap\Entity\RoleData;
use Fisdap\Entity\StudentLegacy;
use Fisdap\Entity\UserContext;

/**
 * A factory for creating RoleData Entities
 *
 * @package Fisdap\Api\Users\UserContexts\Roles
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class RoleDataFactory
{
    /**
     * @param CreateRoleData $createRoleDataJob
     *
     * @return RoleData
     * @throws \Exception
     */
    public function create(CreateRoleData $createRoleDataJob)
    {
        switch ($createRoleDataJob->name) {
            case 'instructor':
                /** @noinspection PhpParamsInspection */
                $roleData = $this->createInstructor($createRoleDataJob->userContext, $createRoleDataJob->communicationPreferences);
                break;
            case 'student':
                /** @noinspection PhpParamsInspection */
                $roleData = $this->createStudent($createRoleDataJob->userContext);
                break;
            default:
                throw new \Exception("'{$createRoleDataJob->name}' is not a known role");
                break;
        }

        $this->setCommonProperties($roleData, $createRoleDataJob->userContext);

        return $roleData;
    }


    /**
     * @param UserContext              $userContext
     * @param CommunicationPreferences $communicationPreferences
     *
     * @return InstructorLegacy
     */
    private function createInstructor(UserContext $userContext, CommunicationPreferences $communicationPreferences = null)
    {
        $instructor = new InstructorLegacy;

        if ($communicationPreferences !== null) {
            $instructor->setReceiveClinicalLateDataEmails($communicationPreferences->receiveClinicalLateDataEmails);
            $instructor->setReceiveFieldLateDataEmails($communicationPreferences->receiveFieldLateDataEmails);
            $instructor->setReceiveLabLateDataEmails($communicationPreferences->receiveLabLateDataEmails);
            $instructor->setEmailEventFlag($communicationPreferences->emailEvent);
        }

        // todo - eliminate the need for this...client code should use the User directly
        $instructor->cell_phone = $userContext->getUser()->getCellPhone();
        $instructor->office_phone = $userContext->getUser()->getWorkPhone();

        return $instructor;
    }


    /**
     * @param UserContext      $userContext
     *
     * @return StudentLegacy
     */
    private function createStudent(UserContext $userContext)
    {
        $user = $userContext->getUser();

        $student = new StudentLegacy;

        // todo - eliminate the duplication of data...client code should use the User directly
        $student->setGraduationDate($userContext->getEndDate());

        $student->home_phone       = $user->getHomePhone();
        $student->cell_phone       = $user->getCellPhone() ?: '';
        $student->work_phone       = $user->getWorkPhone();
        $student->address          = $user->getAddress();
        $student->city             = $user->getCity();
        $student->state            = $user->getState();
        $student->country          = $user->getCountry();
        $student->zip              = $user->getZip();
        $student->contact_name     = $user->getContactName() ?: '';
        $student->contact_phone    = $user->getContactPhone() ?: '';
        $student->contact_relation = $user->getContactRelation() ?: '';

        return $student;
    }


    /**
     * @param RoleData    $roleData
     * @param UserContext $userContext
     *
     * @todo eliminate the need for this...client code should use the User/UserContext directly
     */
    private function setCommonProperties(RoleData $roleData, UserContext $userContext)
    {
        $roleData->setUsername($userContext->getUser()->getUsername());
        $roleData->setProgram($userContext->getProgram());
        $roleData->setFirstName($userContext->getUser()->getFirstName());
        $roleData->setLastName($userContext->getUser()->getLastName());

        $userEmail = $userContext->getUser()->getEmail();
        $userContextEmail = $userContext->getEmail();

        $roleData->setEmail(is_null($userContextEmail) ? $userEmail : $userContextEmail);
    }
}
