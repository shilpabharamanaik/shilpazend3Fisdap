<?php namespace User\Entity;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Fisdap\Api\Shifts\Attachments\Entities\ShiftAttachment;
use Fisdap\Api\Users\UserContexts\Roles\Instructors\Http\Exceptions\InvalidPermission;
use User\EntityUtils;
use phpDocumentor\Reflection\Types\Boolean;
use Zend_Registry;


/**
 * Entity class for Verifications for Runs, Patients and skills
 *
 * This should really be a value object embedded in PreceptorSignoff, since you can't have a signoff without validation.
 * Also, perhaps we should have an abstract Signoff Entity from which PreceptorSignoff would extend.
 * ~bgetsug
 *
 * @todo refactor as value object, migrating data to PreceptorSignoff
 *
 * @Entity(repositoryClass="Fisdap\Data\Verification\DoctrineVerificationRepository")
 * @Table(name="fisdap2_verifications")
 * @HasLifecycleCallbacks
 */
class Verification
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @Column(type="boolean")
     */
    protected $verified = false;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $verified_by;

    /**
     * @ManyToOne(targetEntity="VerificationType")
     */
    protected $type;

    /**
     * @OneToOne(targetEntity="SignoffEmail", inversedBy="verification", cascade={"persist","remove"})
     */
    protected $email;

    /**
     * @OneToOne(targetEntity="Signature", inversedBy="verification", cascade={"persist","remove"})
     */
    protected $signature;

    /**
     * @OneToOne(targetEntity="Run", mappedBy="verification")
     */
    protected $run;

    /**
     * @OneToOne(targetEntity="ShiftLegacy", inversedBy="verification")
     * @JoinColumn(name="shift_id", referencedColumnName="Shift_id")
     */
    protected $shift;

    /**
     * @var ShiftAttachment
     * @ManyToOne(targetEntity="Fisdap\Api\Shifts\Attachments\Entities\ShiftAttachment", inversedBy="verifications")
     */
    protected $shiftAttachment;


    public function set_signature(Signature $signature = null)
    {
        if (is_null($signature)) {
            if ($this->signature !== null) {
                $this->signature = null;
            }
        } else {
            $signature->verification = $this;
        }

        $this->signature = $signature;
    }

    /**
     * @return null|Signature
     */
    public function getSignature()
    {
        return $this->signature;
    }

    public function set_email(SignoffEmail $email = null)
    {
        if (is_null($email)) {
            $this->email->delete();
        }

        $email->verification = $this;
        $this->email = $email;
    }

    public function set_type($value)
    {
        $this->type = self::id_or_entity_helper($value, 'VerificationType');
    }

    /**
     * Returns the name of whomever verified this verification
     *
     * @return string the name of the preceptor
     */
    public function getSignoffName()
    {
        if (!$this->verified) {
            return false;
        }

        switch ($this->getTypeId()) {
            case 1:
                $user = EntityUtils::getEntity('User', $this->verified_by);
                return $user->first_name . " " . $user->last_name;
                break;
            case 2:
                return $this->signature->name;
                break;
            default:
                return null;
        }
    }

    public function getUsername()
    {
        if (!$this->verified) {
            return false;
        }

        switch ($this->getTypeId()) {
            case 1:
                $user = EntityUtils::getEntity('User', $this->verified_by);
                return $user->username;
                break;
            default:
                return false;
        }
    }

    /**
     * Returns a plain text message describing the status of the verification.
     *
     * @param ProgramSettings $programSettings
     * @return string
     */
    public function getSignoffMessage(ProgramSettings $programSettings) {
        $name = $this->getSignoffName();

        $signoffDTFormat = 'M j, Y \a\t Hi';
        date_default_timezone_set('America/Chicago');

        /** @var DateTime $signoffDT */
        $signoffDT = clone $this->updated;

        if ($programSettings && $programSettings->timezone && $programSettings->timezone->mysql_offset) {
            $signoffDT->setTimezone(DateTime::createFromFormat('O', $programSettings->timezone->mysql_offset)->getTimezone());
            $signoffDTFormat = $signoffDT->format($signoffDTFormat);
        } else {
            $signoffDT->setTimezone(new DateTimeZone('America/Chicago'));
            $signoffDTFormat = $signoffDT->format($signoffDTFormat);
        }

        if($name) {
            return 'This evaluation was verified by ' . $name . ' at ' . $signoffDTFormat . '.';
        } else {
            return "This evaluation was verified by attachment at " . $signoffDTFormat . ".";
        }
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return ShiftAttachment
     */
    public function getShiftAttachment()
    {
        return $this->shiftAttachment;
    }


    /**
     * @param ShiftAttachment|null $shiftAttachment
     */
    public function setShiftAttachment(ShiftAttachment $shiftAttachment = null)
    {
        $this->shiftAttachment = $shiftAttachment;
    }

    public function setVerified($isVerified = false)
    {
        $this->verified = $isVerified;
    }

    /**
     * @param User|null $user
     * @param bool $requireInstructor
     * @param Patient $patient
     */
    public function setVerifiedBy(User $user = null, $requireInstructor = false, $patient = null)
    {
        if ($user && $requireInstructor) {
            if (!$user->isInstructor()) {
                throw new InvalidPermission('This username does not belong to an educator.');
            } else if ($patient && $patient->getStudent()->program->getId() != $user->getCurrentUserContext()->getProgram()->getId()) {
                throw new InvalidPermission('This educator does not belong to the same program as student.');
            }
        }
        $this->verified_by = $user ? $user->id : null;
    }

    public function getTypeId()
    {
        return $this->type ? $this->type->id : null;
    }

    /**
     * Get an array representation of a Verification
     *
     * @return array
     */
    public function toArray()
    {
        $rtv = [
            'id' => $this->getId(),
            'type' => $this->getTypeId(),
            'verified' => $this->verified,
            'verifiedBy' => $this->verified_by,
            'updated' => $this->getUpdated()
        ];

        if ($this->getUsername()) $rtv['username'] = $this->getUsername();
        if ($this->getSignoffName()) $rtv['name'] = $this->getSignoffName();
        if ($this->getSignature() && $this->getSignature()->getSignatureString()) $rtv['signatureString'] = $this->getSignature()->getSignatureString();
        if ($this->getShiftAttachment()) $rtv['attachment'] = $this->getShiftAttachment()->toArray();

        return $rtv;
    }
}