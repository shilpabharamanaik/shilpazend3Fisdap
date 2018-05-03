<?php namespace Fisdap\Api\Jobs\Verifications;


use Auth;
use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\Shifts\Attachments\Entities\ShiftAttachment;
use Fisdap\Api\Users\UserContexts\Roles\Instructors\Http\Exceptions\InvalidPermission;
use Fisdap\Attachments\Entity\Attachment;
use Fisdap\Attachments\Queries\AttachmentsFinder;
use Fisdap\Data\User\UserRepository;
use Fisdap\Entity\Patient;
use Fisdap\Entity\ShiftLegacy;
use Fisdap\Entity\Signature;
use Fisdap\Entity\User;
use Fisdap\Entity\Verification;
use Fisdap\Entity\VerificationType;
use Fisdap\EntityUtils;
use Swagger\Annotations as SWG;

/**
 * Class SetVerification
 * @package Fisdap\Api\Shifts\PreceptorSignoffs\Jobs
 * @author  Isaac White <iwhite@fisdap.net>
 *
 * @SWG\Definition(
 *     definition="Verifications",
 *     required={ "type" }
 * )
 */
final class SetVerification extends Job implements RequestHydrated
{
    /**
     * @var integer
     * @see VerificationType
     * @SWG\Property(type="integer", description="Type of Verification (ex. Username/Password)", default=2)
     */
    public $type;

    /*** Username/Password ***/
    /**
     * @var string|null
     * @SWG\Property(type="string", description="This is the username")
     */
    public $username = null;

    /**
     * @var string|null
     * @SWG\Property(type="string", description="This is the password")
     */
    public $password = null;

    /**
     * @var integer|null
     * @see User
     * @SWG\Property(type="integer")
     */
    public $user = null;

    /*** Signature ***/

    /**
     * @var string|null
     * @SWG\Property(type="string", description="The name provided for the signature")
     */
    public $name = null;

    /**
     * @var string|null
     * @SWG\Property(type="string", description="The body of the type of verification")
     */
    public $signatureString = null;

    /**
     * @var string|null
     */
    public $attachmentId = null;

    /**
     * @var Verification|null
     */
    private $verification;

    /**
     * @var Patient|null
     */
    private $patient;

    /**
     * @var ShiftLegacy|null
     */
    private $shift;

    public function handle(
        UserRepository $repository,
        AttachmentsFinder $finder
    )
    {
        $verification = $this->getVerification() ? $this->getVerification() : new Verification;

        // Should not be in here if no verification has not been explicitly called.
        switch($this->type) {
            case null:
                $verification->setVerifiedBy();
                $verification->setVerified(false);
                $verification->set_signature(null);
                break;
            case 1:
                if (!User::authenticate_password($this->username, $this->password)) { // Check the submitted permissions
                    throw new InvalidPermission('Invalid permissions');
                }

                $verification->setVerifiedBy(User::getByUsername($this->username), true, $this->patient, $this->shift);
                $verification->setVerified(true);
                $verification->set_signature(null);
                break;
            case 2:
                $signature = new Signature;
                $signature->setSignatureString($this->signatureString);
                $signature->setName($this->name);
                $signature->setUser($repository->find(Auth::id()));
                $verification->setVerifiedBy(Auth::user(), false); // Whoever the user who sent it is the Auth user
                $verification->setVerified(true);
                $verification->set_signature($signature);
                break;
            case 3:
                break;
            case 4:
                $attachment = $finder->findAttachment(
                    "shift",
                    $this->attachmentId,
                    null,
                    null,
                    false
                );

                $verification->setVerified(true);

                $verification->setShiftAttachment($attachment);
                break;
            default:
                throw new ResourceNotFound("No resource found for type id: '$this->type'.");
                break;
        }

        $verification->set_type($this->type);

        return $verification;
    }

    /**
     * @param Verification|null $verification
     */
    public function setVerification(Verification $verification = null)
    {
        $this->verification = $verification;
    }

    /**
     * @return Verification|null
     */
    private function getVerification()
    {
        if ($this->verification) {
            $verification = EntityUtils::getRepository('Verification')->getById([$this->verification->getId()]);
            if ($verification) {
                return $this->verification;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * @param Patient $patient
     */
    public function setPatient(Patient $patient) {
        $this->patient = $patient;
    }

    /**
     * @param ShiftLegacy $shift
     */
    public function setShift(ShiftLegacy $shift) {
        $this->shift = $shift;
    }
}