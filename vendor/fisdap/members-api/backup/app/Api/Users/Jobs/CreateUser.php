<?php namespace Fisdap\Api\Users\Jobs;

use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Api\Users\Events\UserWasCreated;
use Fisdap\Api\Users\Jobs\Models\Address;
use Fisdap\Api\Users\Jobs\Models\Certifications;
use Fisdap\Api\Users\Jobs\Models\Contact;
use Fisdap\Api\Users\Jobs\Models\Licenses;
use Fisdap\Api\Users\Jobs\Models\Phones;
use Fisdap\Data\Ethnicity\EthnicityRepository;
use Fisdap\Data\Gender\GenderRepository;
use Fisdap\Data\User\UserRepository;
use Fisdap\Entity\User;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Swagger\Annotations as SWG;


/**
 * A Job (Command) for creating a new user (User Entity)
 *
 * Object properties will be hydrated automatically using JSON from an HTTP request body
 *
 * @package Fisdap\Api\Users\Jobs
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *          
 * @SWG\Definition(
 *     definition="User",
 *     required={"firstName", "lastName", "username", "email"}
 * )
 */
final class CreateUser extends Job implements RequestHydrated
{
    /**
     * @var string|null
     * @SWG\Property(type="string", example="ABC123")
     */
    public $uuid = null;

    /**
     * @var string
     * @SWG\Property
     */
    public $firstName;

    /**
     * @var string
     * @SWG\Property
     */
    public $lastName;

    /**
     * @var string
     * @SWG\Property
     */
    public $username;

    /**
     * @var string|null
     * @SWG\Property(type="string")
     */
    public $ltiUserId = null;

    /**
     * @var string|null
     * @SWG\Property(type="string")
     */
    public $psgUserId = null;

    /**
     * @var string|null
     * @SWG\Property(type="string")
     */
    public $password = null;

    /**
     * @var string
     * @SWG\Property
     */
    public $email;

    /**
     * @var bool
     * @SWG\Property
     */
    public $demo = false;

    /**
     * @var bool
     * @SWG\Property
     */
    public $acceptedAgreement = false;

    /**
     * @var \Fisdap\Api\Users\Jobs\Models\Address|null
     * @SWG\Property(ref="#/definitions/UserAddress")
     */
    public $address = null;

    /**
     * @var \Fisdap\Api\Users\Jobs\Models\Phones|null
     * @SWG\Property(ref="#/definitions/UserPhones")
     */
    public $phones = null;

    /**
     * @var \Fisdap\Api\Users\Jobs\Models\Contact|null
     * @SWG\Property(ref="#/definitions/UserContact")
     */
    public $contact = null;

    /**
     * @var \Fisdap\Api\Users\Jobs\Models\Licenses|null
     * @SWG\Property(ref="#/definitions/UserLicenses")
     */
    public $licenses = null;

    /**
     * @var \Fisdap\Api\Users\Jobs\Models\Certifications|null
     * @SWG\Property(ref="#/definitions/UserCertifications")
     */
    public $certifications = null;

    /**
     * @var \DateTime|null
     * @SWG\Property(type="string")
     */
    public $birthDate = null;

    /**
     * @var int|null
     * @SWG\Property(type="integer")
     */
    public $genderId = null;

    /**
     * @var int|null
     * @SWG\Property(type="integer")
     */
    public $ethnicityId = null;

    /**
     * @var \Fisdap\Api\Users\UserContexts\Jobs\CreateUserContext[]
     * @SWG\Property(type="array", items=@SWG\Items(ref="#/definitions/UserContext"))
     */
    public $userContexts = [];


    /**
     * CreateUser constructor.
     *
     * @param string                                                  $firstName
     * @param string                                                  $lastName
     * @param string                                                  $username
     * @param null                                                    $ltiUserId
     * @param null                                                    $psgUserId
     * @param null|string                                             $password
     * @param string                                                  $email
     * @param bool                                                    $demo
     * @param bool                                                    $acceptedAgreement
     * @param Address|null                                            $address
     * @param Phones|null                                             $phones
     * @param Contact|null                                            $contact
     * @param Licenses|null                                           $licenses
     * @param Certifications|null                                     $certifications
     * @param \DateTime|null                                          $birthDate
     * @param int|null                                                $genderId
     * @param int|null                                                $ethnicityId
     * @param \Fisdap\Api\Users\UserContexts\Jobs\CreateUserContext[] $userContexts
     */
    public function __construct(
        $firstName = '',
        $lastName = '',
        $username = '',
        $ltiUserId = null,
        $psgUserId = null,
        $password = null,
        $email = '',
        $demo = false,
        $acceptedAgreement = false,
        Address $address = null,
        Phones $phones = null,
        Contact $contact = null,
        Licenses $licenses = null,
        Certifications $certifications = null,
        \DateTime $birthDate = null,
        $genderId = null,
        $ethnicityId = null,
        array $userContexts = []
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->username = $username;
        $this->ltiUserId = $ltiUserId;
        $this->psgUserId = $psgUserId;
        $this->password = $password;
        $this->email = $email;
        $this->demo = $demo;
        $this->acceptedAgreement = $acceptedAgreement;
        $this->address = $address;
        $this->phones = $phones;
        $this->contact = $contact;
        $this->licenses = $licenses;
        $this->certifications = $certifications;
        $this->birthDate = $birthDate;
        $this->genderId = $genderId;
        $this->ethnicityId = $ethnicityId;
        $this->userContexts = $userContexts;
    }


    /**
     * @param UserRepository      $userRepository
     * @param GenderRepository    $genderRepository
     * @param EthnicityRepository $ethnicityRepository
     * @param EventDispatcher     $eventDispatcher
     * @param BusDispatcher       $busDispatcher
     *
     * @return User
     */
    public function handle(
        UserRepository $userRepository,
        GenderRepository $genderRepository,
        EthnicityRepository $ethnicityRepository,
        EventDispatcher $eventDispatcher,
        BusDispatcher $busDispatcher
    ) {
        $user = new User;
        $user->setUUID($this->uuid);
        $user->setFirstName($this->firstName);
        $user->setLastName($this->lastName);
        $user->setUsername($this->username);
        $user->setLtiUserId($this->ltiUserId);
        $user->setPsgUserId($this->psgUserId);
        $user->setEmail($this->email);
        $user->setPassword($this->password);

        $user->setDemo($this->demo);
        $user->setAcceptedAgreement($this->acceptedAgreement);

        $this->setAddress($user);

        $this->setPhones($user);

        $this->setContact($user);

        $this->setCertifications($user);

        $this->setLicenses($user);

        $user->setBirthDate($this->birthDate);

        if ($this->genderId !== null) {
            $user->setGender($genderRepository->getOneById($this->genderId));
        }

        if ($this->ethnicityId !== null) {
            $user->setEthnicity($ethnicityRepository->getOneById($this->ethnicityId));
        }

        $userRepository->store($user);

        $eventDispatcher->fire(new UserWasCreated($user));

        // $this->userContexts is an array of Hydrated functions/Jobs (CreateUserContexts[])
        foreach ($this->userContexts as $userContext) {
            $userContext->userId = $user->getId();
            $busDispatcher->dispatch($userContext);
        }

        return $user;
    }


    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            'firstName'         => 'required|string',
            'lastName'          => 'required|string',
            'username'          => 'required|string',
            'email'             => 'required|email',
            'demo'              => 'boolean',
            'acceptedAgreement' => 'boolean',
            'address.address'   => 'string',
            'address.city'      => 'string',
            'address.state'     => 'string',
            'address.zip'       => 'numeric',
            'genderId'          => 'integer',
            'ethnicityId'       => 'integer'
        ];

        return $rules;
    }


    /**
     * @param User $user
     */
    private function setAddress(User $user)
    {
        if ($this->address instanceof Address) {
            $user->setAddress($this->address->address);
            $user->setCity($this->address->city);
            $user->setState($this->address->state);
            $user->setZip($this->address->zip);
        }
    }


    /**
     * @param User $user
     */
    private function setPhones(User $user)
    {
        if ($this->phones instanceof Phones) {
            $user->setHomePhone($this->phones->homePhone);
            $user->setWorkPhone($this->phones->workPhone);
            $user->setCellPhone($this->phones->cellPhone);
        }
    }


    /**
     * @param User $user
     */
    private function setContact(User $user)
    {
        if ($this->contact instanceof Contact) {
            $user->setContactName($this->contact->name);
            $user->setContactPhone($this->contact->phone);
            $user->setContactRelation($this->contact->relation);
        }
    }


    /**
     * @param User $user
     */
    private function setCertifications(User $user)
    {
        if ($this->certifications instanceof Certifications) {
            $user->setEmtGrad($this->certifications->emtGrad);
            $user->setEmtGradDate($this->certifications->emtGradDate);
            $user->setEmtCert($this->certifications->emtCert);
            $user->setEmtCertDate($this->certifications->emtCertDate);
        }
    }


    /**
     * @param User $user
     */
    private function setLicenses(User $user)
    {
        if ($this->licenses instanceof Licenses) {
            $user->setLicenseNumber($this->licenses->licenseNumber);
            $user->setLicenseExpirationDate($this->licenses->licenseExpirationDate);
            $user->setLicenseState($this->licenses->licenseState);
            $user->setStateLicenseNumber($this->licenses->stateLicenseNumber);
            $user->setStateLicenseExpirationDate($this->licenses->stateLicenseExpirationDate);
        }
    }
}