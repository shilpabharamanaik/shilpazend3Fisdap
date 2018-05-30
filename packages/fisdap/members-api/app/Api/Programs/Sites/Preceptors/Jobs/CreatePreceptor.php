<?php namespace Fisdap\Api\Programs\Sites\Preceptors\Jobs;

use Faker\Provider\DateTime;
use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Api\Programs\Sites\Preceptors\Events\PreceptorWasCreated;
use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Data\Preceptor\PreceptorLegacyRepository;
use Fisdap\Data\ProgramPreceptor\ProgramPreceptorLegacyRepository;
use Fisdap\Data\Site\SiteLegacyRepository;
use Fisdap\Data\Student\StudentLegacyRepository;
use Fisdap\Entity\PreceptorLegacy;
use Fisdap\Entity\ProgramPreceptorLegacy;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Swagger\Annotations as SWG;

/**
 * A Job (Command) for creating and assigning a new preceptor to a site. (PreceptorLegacy Entity)
 *
 * Class CreatePreceptor
 * @package Fisdap\Api\Programs\Sites\Preceptors\Jobs
 * @author  Isaac White <iwhite@fisdap.net>
 *
 * @SWG\Definition(
 *     definition="Preceptor",
 *     required={"firstName", "lastName", "active"}
 * )
 */
final class CreatePreceptor extends Job implements RequestHydrated
{
    /**
     * @var string|null
     * @SWG\Property(type="string", example="ABC123")
     */
    public $uuid = null;

    /**
     * @var string
     * @SWG\Property(type="string", example="Michael")
     */
    public $firstName;

    /**
     * @var string
     * @SWG\Property(type="string", example="Smith")
     */
    public $lastName;

    /**
     * @var string|null
     * @SWG\Property(type="string", example="King")
     */
    public $title;

    /**
     * @var DateTime|null
     * @SWG\Property(type="DateTime")
     * @codeCoverageIgnore
     * @deprecated
     */
    public $dateSelected = null;

    /**
     * @var string|null
     * @SWG\Property(type="string", example="Home")
     */
    public $mainBase = null;

    /**
     * @var string|null
     * @SWG\Property(type="string", example="641-357-8111")
     */
    public $homePhone = null;

    /**
     * @var string|null
     * @SWG\Property(type="string", example="641-357-8111")
     */
    public $workPhone = null;

    /**
     * @var string|null
     * @SWG\Property(type="string", example="641-357-8111")
     */
    public $pager = null;

    /**
     * @var string|null
     * @SWG\Property(type="string", example="preceptor@site.com")
     */
    public $email = null;

    /**
     * @var string|null
     * @SWG\Property(type="string", example="I exist")
     */
    public $status = null;

    /**
     * @var integer|null
     * @SWG\Property(type="integer", example="148286")
     */
    public $studentId = null;

    /**
     * @var boolean
     * @SWG\Property(type="boolean", example=true)
     */
    public $active = true;

    /**
     * @var integer
     */
    protected $siteId;

    public function handle(
        PreceptorLegacyRepository $preceptorLegacyRepository,
        ProgramPreceptorLegacyRepository $programPreceptorLegacyRepository,
        SiteLegacyRepository $siteLegacyRepository,
        StudentLegacyRepository $studentLegacyRepository,
        EventDispatcher $eventDispatcher
    ) {
        $preceptor = new PreceptorLegacy;
        $preceptor->setUUID($this->uuid);
        
        $site = $siteLegacyRepository->find($this->siteId);

        if (empty($site)) {
            throw new ResourceNotFound("No sites found with id '$this->siteId'.");
        }

        $student = $studentLegacyRepository->find($this->studentId);

        if (empty($student)) {
            throw new ResourceNotFound("No students found with id '$this->studentId'.");
        }

        $preceptor->setSite($site);
        $preceptor->setStudent($student);
        $preceptor->setFirstName($this->firstName);
        $preceptor->setLastName($this->lastName);

        $this->setOptionalParameters($preceptor);

        $ppl = new ProgramPreceptorLegacy;
        $ppl->setPreceptor($preceptor);
        $ppl->setProgram($student->program);

        $preceptorLegacyRepository->store($preceptor);
        $programPreceptorLegacyRepository->store($ppl);

        $eventDispatcher->fire(new PreceptorWasCreated(
            $preceptor->getId(),
                                                       $preceptor->getFirstName() . ' ' . $preceptor->getLastName()
        ));

        return $preceptor;
    }

    /**
     * Simple helper to use URL $siteId
     *
     * @param $siteId
     */
    public function setSiteId($siteId)
    {
        $this->siteId = $siteId;
    }

    /**
     * This helper function allows us to leverage the Entity ot validate and set optional
     * parameters to relevant values.
     *
     * @param PreceptorLegacy $preceptor
     */
    private function setOptionalParameters(PreceptorLegacy $preceptor)
    {
        $preceptor->setTitle($this->title);
        $preceptor->setDateSelected($this->dateSelected);
        $preceptor->setMainBase($this->mainBase);
        $preceptor->setHomePhone($this->homePhone);
        $preceptor->setWorkPhone($this->workPhone);
        $preceptor->setPager($this->pager);
        $preceptor->setEmail($this->email);
        $preceptor->setStatus($this->status);
        $preceptor->setActive($this->active);
    }
}
