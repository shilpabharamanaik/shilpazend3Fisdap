<?php namespace Fisdap\Api\Programs\Jobs;

use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Api\Programs\Events\ProgramWasCreated;
use Fisdap\Api\Programs\Jobs\Models\Accreditation;
use Fisdap\Api\Programs\Jobs\Models\Address;
use Fisdap\Api\Programs\Jobs\Models\Billing;
use Fisdap\Api\Programs\Jobs\Models\Phones;
use Fisdap\Api\Programs\Jobs\Models\Referral;
use Fisdap\Api\Programs\Settings\Jobs\Models\Settings;
use Fisdap\Api\Programs\Settings\Jobs\Models\Commerce;
use Fisdap\Data\Profession\ProfessionRepository;
use Fisdap\Data\Program\ProgramLegacyRepository;
use Fisdap\Data\Program\Type\ProgramTypeLegacyRepository;
use Fisdap\Entity\ProgramLegacy;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Swagger\Annotations as SWG;

/**
 * A Job (Command) for creating a new program (ProgramLegacy Entity)
 *
 * @package Fisdap\Api\Programs\Jobs
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *
 * @SWG\Definition(
 *     definition="Program",
 *     required={"name"}
 * )
 */
final class CreateProgram extends Job implements RequestHydrated
{
    /**
     * @var string|null
     * @SWG\Property(type="string", example="ABC123")
     */
    public $uuid = null;

    /**
     * @var string
     * @SWG\Property(example="Jones School")
     */
    public $name;

    /**
     * @var string|null
     * @SWG\Property(type="string", example="JS")
     */
    public $abbreviation = null;
    
    /**
     * @var int|null
     * @SWG\Property(type="integer", example=1)
     */
    public $professionId = null;

    /**
     * Array of program type IDs, defaults to EMS
     * 
     * @var int[]
     * @SWG\Property(type="array", items=@SWG\Items(type="integer"), example={1})
     */
    public $programTypeIds = [1];
    
    /**
     * @var int|null
     * @see ProgramLegacy::$class_size
     * @SWG\Property(type="integer", example=500)
     */
    public $studentsPerYear = null;

    /**
     * @var string|null
     * @SWG\Property(type="string", example="http://jonesschool.edu")
     */
    public $website = null;

    /**
     * @var bool
     * @SWG\Property(example=true)
     */
    public $active = true;

    /**
     * @var \Fisdap\Api\Programs\Jobs\Models\Address|null
     * @SWG\Property(ref="#/definitions/ProgramAddress")
     */
    public $address = null;

    /**
     * @var \Fisdap\Api\Programs\Jobs\Models\Phones|null
     * @SWG\Property(ref="#/definitions/ProgramPhones")
     */
    public $phones = null;
    
    /**
     * @var \Fisdap\Api\Programs\Jobs\Models\Billing|null
     * @SWG\Property(ref="#/definitions/ProgramBilling")
     */
    public $billing = null;

    /**
     * @var \Fisdap\Api\Programs\Jobs\Models\Accreditation|null
     * @SWG\Property(ref="#/definitions/ProgramAccreditation")
     */
    public $accreditation = null;

    /**
     * @var \Fisdap\Api\Programs\Jobs\Models\Referral|null
     * @SWG\Property(ref="#/definitions/ProgramReferral")
     */
    public $referral = null;
    
    /**
     * @var \Fisdap\Api\Programs\Settings\Jobs\Models\Settings|null
     * @see ProgramLegacy::$program_settings
     * @SWG\Property(ref="#/definitions/ProgramSettings")
     */
    public $settings = null;


    /**
     * @param ProgramLegacyRepository     $programLegacyRepository
     * @param ProgramTypeLegacyRepository $programTypeLegacyRepository
     * @param ProfessionRepository        $professionRepository
     *
     * @param EventDispatcher             $eventDispatcher
     *
     * @return ProgramLegacy
     */
    public function handle(
        ProgramLegacyRepository $programLegacyRepository,
        ProgramTypeLegacyRepository $programTypeLegacyRepository,
        ProfessionRepository $professionRepository,
        EventDispatcher $eventDispatcher
    ) {
        $program = new ProgramLegacy;
        $program->setName($this->name);
        $program->setUUID($this->uuid);

        if (is_null($this->abbreviation)) {
            $program->generateAbbreviation();
        } else {
            $program->setAbbreviation($this->abbreviation);
        }

        if (is_int($this->professionId)) {
            $program->setProfession($professionRepository->getOneById($this->professionId));
        }
        
        $programTypes = array_map(function($programTypeId) use ($programTypeLegacyRepository) {
            return $programTypeLegacyRepository->getOneById($programTypeId);
        }, $this->programTypeIds);

        $program->addProgramTypes($programTypes);
       

        if (is_int($this->studentsPerYear)) {
            $program->setClassSize($this->studentsPerYear);
        }

        if (is_string($this->website)) {
            $program->setWebsite($this->website);
        }

        $program->setActive($this->active);

        $this->setAddress($program);

        $this->setPhones($program);
        
        $this->setBilling($program);

        $this->setAccreditation($program);

        $this->setReferral($program);

        if (is_null($this->settings)) {
            $this->settings = new Settings;
            $this->settings->commerce = new Commerce;
        }

        $programLegacyRepository->store($program);

        $eventDispatcher->fire(new ProgramWasCreated($program->getId(), $program->getName(), $this->settings));
        
        return $program;
    }


    /**
     * @param ProgramLegacy $program
     */
    private function setAddress(ProgramLegacy $program)
    {
        if ($this->address instanceof Address) {
            $program->setAddress($this->address->address1);
            $program->setAddress2($this->address->address2);
            $program->setAddress3($this->address->address3);
            $program->setCity($this->address->city);
            $program->setState($this->address->state);
            $program->setZip($this->address->zip);
            $program->setCountry($this->address->country);
        }
    }


    /**
     * @param ProgramLegacy $program
     */
    private function setPhones(ProgramLegacy $program)
    {
        if ($this->phones instanceof Phones) {
            $program->setPhone($this->phones->phone);
            $program->setFax($this->phones->fax);
        }
    }


    /**
     * @param ProgramLegacy $program
     */
    private function setBilling(ProgramLegacy $program)
    {
        if ($this->billing instanceof Billing) {
            $program->setBillingEmail($this->billing->email);
            $program->setBillingContact($this->billing->contactName);
            $program->setBillingAddress($this->billing->address1);
            $program->setBillingAddress2($this->billing->address2);
            $program->setBillingAddress3($this->billing->address3);
            $program->setBillingCity($this->billing->city);
            $program->setBillingState($this->billing->state);
            $program->setBillingZip($this->billing->zip);
            $program->setBillingCountry($this->billing->country);
            $program->setBillingPhone($this->billing->phone);
            $program->setBillingFax($this->billing->fax);
        }
    }


    /**
     * @param ProgramLegacy $program
     */
    private function setAccreditation(ProgramLegacy $program)
    {
        if ($this->accreditation instanceof Accreditation) {
            $program->setAccredited($this->accreditation->accredited);
            $program->setCoaemspProgramId($this->accreditation->coaemspProgramId);
            $program->setYearAccredited($this->accreditation->yearAccredited);
        }
    }


    /**
     * @param ProgramLegacy $program
     */
    private function setReferral(ProgramLegacy $program)
    {
        if ($this->referral instanceof Referral) {
            $program->setReferral($this->referral->source);
            $program->setRefDescription($this->referral->description);
        }
    }
}
