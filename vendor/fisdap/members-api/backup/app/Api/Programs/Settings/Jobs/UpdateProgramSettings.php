<?php namespace Fisdap\Api\Programs\Settings\Jobs;

use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Api\Programs\Settings\Events\ProgramSettingsWasUpdated;
use Fisdap\Api\Programs\Settings\Jobs\Models\EmailNotifications;
use Fisdap\Api\Programs\Settings\Jobs\Models\MyGoals;
use Fisdap\Api\Programs\Settings\Jobs\Models\PracticeSkills;
use Fisdap\Api\Programs\Settings\Jobs\Models\ShiftDeadlines;
use Fisdap\Api\Programs\Settings\Jobs\Models\Signoff;
use Fisdap\Api\Programs\Settings\Jobs\Models\StudentPermissions;
use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Data\Order\Permission\OrderPermissionRepository;
use Fisdap\Data\Program\ProgramLegacyRepository;
use Fisdap\Data\Program\Settings\ProgramSettingsRepository;
use Fisdap\Data\Repository\Repository;
use Fisdap\Data\Timezone\TimezoneRepository;
use Fisdap\Entity\EntityBaseClass;
use Fisdap\Entity\OrderPermission;
use Fisdap\Entity\ProgramLegacy;
use Fisdap\Entity\ProgramSettings;
use Fisdap\Entity\Timezone;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Swagger\Annotations as SWG;

/**
 * A Job (Command) for updating program settings (ProgramSettings Entity)
 *
 * Object properties will be hydrated automatically using JSON from an HTTP request body
 *
 * @package Fisdap\Api\Programs\Settings\Jobs
 * @author  Nick Karnick <nkarnick@fisdap.net>
 *
 * @SWG\Definition(
 *     definition="Update ProgramSettings"
 * )
 */
final class UpdateProgramSettings extends Job implements RequestHydrated
{
    /**
     * @var int
     */
    public $programId;

    /**
     * @var bool
     * @see ProgramSettings::$allow_educator_shift_audit
     * @SWG\Property(example=false)
     */
    public $allowShiftAudit = false;

    /**
     * @var bool
     * @see ProgramSettings::$allow_educator_evaluations
     * @SWG\Property(example=false)
     */
    public $allowEvaluations = false;

    /**
     * @var bool
     * @see ProgramSettings::$allow_tardy
     * @SWG\Property(example=true)
     */
    public $allowTardy = true;

    /**
     * @var bool
     * @see ProgramSettings::$allow_absent
     * @SWG\Property(example=true)
     */
    public $allowAbsent = true;

    /**
     * @var int
     * @see ProgramSettings::$timezone
     * @SWG\Property(example=2)
     */
    public $timezoneId = 2;

    /**
     * @var bool
     * @see ProgramSettings::$quick_add_clinical
     * @SWG\Property(example=true)
     */
    public $quickAddClinical = true;

    /**
     * @var \Fisdap\Api\Programs\Settings\Jobs\Models\Signoff|null
     * @SWG\Property(ref="#/definitions/ProgramSettingsSignoff")
     */
    public $signoff = null;

    /**
     * @var \Fisdap\Api\Programs\Settings\Jobs\Models\MyGoals|null
     * @SWG\Property(ref="#/definitions/ProgramSettingsMyGoals")
     */
    public $myGoals = null;

    /**
     * @var \Fisdap\Api\Programs\Settings\Jobs\Models\PracticeSkills|null
     * @SWG\Property(ref="#/definitions/ProgramSettingsPracticeSkills")
     */
    public $practiceSkills = null;

    /**
     * @var \Fisdap\Api\Programs\Settings\Jobs\Models\EmailNotifications|null
     * @SWG\Property(ref="#/definitions/ProgramSettingsEmailNotifications")
     */
    public $emailNotifications = null;

    /**
     * @var \Fisdap\Api\Programs\Settings\Jobs\Models\ShiftDeadlines|null
     * @SWG\Property(ref="#/definitions/ProgramSettingsShiftDeadlines")
     */
    public $shiftDeadlines = null;

    /**
     * @var \Fisdap\Api\Programs\Settings\Jobs\Models\StudentPermissions|null
     * @SWG\Property(ref="#/definitions/ProgramSettingsStudentPermissions")
     */
    public $studentPermissions = null;

    /**
     * @var \Fisdap\Api\Programs\Settings\Jobs\Models\Commerce|null
     * @SWG\Property(ref="#/definitions/ProgramSettingsCommerce")
     */
    public $commerce = null;


    public function setProgramId($programId)
    {
        $this->programId = $programId;
    }
    
    
    /**
     * @param ProgramLegacyRepository       $programRepo,
     * @param ProgramSettingsRepository     $progSettingsRepo
     * @param TimezoneRepository            $timezoneRepository
     * @param OrderPermissionRepository     $orderPermissionRepo
     * @param EventDispatcher               $eventDispatcher
     *
     * @throws ResourceNotFound
     *
     * @return ProgramSettings
     */
    public function handle(
        ProgramLegacyRepository $programRepo,
        ProgramSettingsRepository $progSettingsRepo,
        TimezoneRepository $timezoneRepository,
        OrderPermissionRepository $orderPermissionRepo,
        EventDispatcher $eventDispatcher
    ) {
        // BEGIN settings stored in ProgramLegacy
        /** @var ProgramLegacy $program */
        $program = $this->validResource($programRepo, 'programId');

        if (!is_null($this->emailNotifications)) {
            if (!is_null($this->emailNotifications->sendCriticalThinking)) {
                $program->setSendCriticalThinkingEmails($this->emailNotifications->sendCriticalThinking);
            }

            if (!is_null($this->emailNotifications->sendLateShift)) {
                $program->setSendLateShiftEmails($this->emailNotifications->sendLateShift);
            }
        }

        if (!is_null($this->shiftDeadlines)) {
            if (!is_null($this->shiftDeadlines->lateFieldDeadlineHours)) {
                $program->setLateFieldDeadline($this->shiftDeadlines->lateFieldDeadlineHours);
            }

            if (!is_null($this->shiftDeadlines->lateClinicalDeadlineHours)) {
                $program->setLateClinicalDeadline($this->shiftDeadlines->lateClinicalDeadlineHours);
            }

            if (!is_null($this->shiftDeadlines->lateLabDeadlineHours)) {
                $program->setLateLabDeadline($this->shiftDeadlines->lateLabDeadlineHours);
            }
        }

        if (!is_null($this->studentPermissions)) {
            if (!is_null($this->studentPermissions->createLab)) {
                $program->set_can_students_create_lab($this->studentPermissions->createLab);
            }

            if (!is_null($this->studentPermissions->createField)) {
                $program->set_can_students_create_field($this->studentPermissions->createField);
            }

            if (!is_null($this->studentPermissions->createClinical)) {
                $program->set_can_students_create_clinical($this->studentPermissions->createClinical);
            }

            if (!is_null($this->studentPermissions->allowAbsentWithPermission)) {
                $program->setAllowAbsentWithPermission($this->studentPermissions->allowAbsentWithPermission);
            }

            if (!is_null($this->studentPermissions->includeNarrative)) {
                $program->setIncludeNarrative($this->studentPermissions->includeNarrative);
            }
        }

        /** @var OrderPermission $orderPermission */
        $orderPermission = $orderPermissionRepo->findOneBy(['id' => $this->commerce->orderPermissionId]);
        if (empty($orderPermission)) {
            throw new ResourceNotFound(
                "OrderPermission not found for id ".$this->commerce->orderPermissionId."."
            );
        }

        if (!is_null($orderPermission)) {
            $program->setOrderPermission($orderPermission);
        }

        if (!is_null($this->commerce)) {
            if (!is_null($this->commerce->requiresPurchaseOrder)) {
                $program->setRequiresPo($this->commerce->requiresPurchaseOrder);
            }
        }

        $programRepo->update($program);
        // END settings stored in ProgramLegacy

        // BEGIN settings stored in ProgramSettings
        /** @var ProgramSettings $settings */
        $settings = $progSettingsRepo->findOneBy(['program' => $this->programId]);
        if (empty($orderPermission)) {
            throw new ResourceNotFound("ProgramSettings not found for id $this->programId.");
        }

        if (!is_null($this->allowShiftAudit)) {
            $settings->setAllowEducatorShiftAudit($this->allowShiftAudit);
        }

        if (!is_null($this->allowEvaluations)) {
            $settings->setAllowEducatorEvaluations($this->allowEvaluations);
        }

        if (!is_null($this->allowTardy)) {
            $settings->setAllowTardy($this->allowTardy);
        }

        if (!is_null($this->allowAbsent)) {
            $settings->setAllowAbsent($this->allowAbsent);
        }

        /** @var Timezone $timezone */
        $timezone = $this->validResource($timezoneRepository, 'timezoneId');

        if (!is_null($timezone)) {
            $settings->setTimezone($timezone);
        }

        if (!is_null($this->quickAddClinical)) {
            $settings->setQuickAddClinical($this->quickAddClinical);
        }

        $this->setSignoff($settings);
        $this->setMyGoals($settings);
        $this->setPracticeSkills($settings);
        $this->setEmailNotifications($settings);
        $this->setShiftDeadlines($settings);
        $this->setStudentPermissions($settings);

        $progSettingsRepo->update($settings);
        // END settings stored in ProgramSettings

        $eventDispatcher->fire(new ProgramSettingsWasUpdated($settings->getId()));

        return $settings;
    }

    /**
     * @param ProgramSettings $settings
     */
    private function setSignoff(ProgramSettings $settings)
    {
        if ($this->signoff instanceof Signoff) {
            if (!is_null($this->signoff->allowWithSignature)) {
                $settings->setAllowEducatorSignoffSignature($this->signoff->allowWithSignature);
            }

            if (!is_null($this->signoff->allowWithLogin)) {
                $settings->setAllowEducatorSignoffLogin($this->signoff->allowWithLogin);
            }

            if (!is_null($this->signoff->allowWithEmail)) {
                $settings->setAllowEducatorSignoffEmail($this->signoff->allowWithEmail);
            }

            if (!is_null($this->signoff->allowWithAttachment)) {
                $settings->setAllowEducatorSignoffAttachment($this->signoff->allowWithAttachment);
            }

            if (!is_null($this->signoff->allowOnPatient)) {
                $settings->setAllowSignoffOnPatient($this->signoff->allowOnPatient);
            }

            if (!is_null($this->signoff->allowOnShift)) {
                $settings->setAllowSignoffOnShift($this->signoff->allowOnShift);
            }
        }
    }

    /**
     * @param ProgramSettings $settings
     */
    private function setMyGoals(ProgramSettings $settings)
    {
        if ($this->myGoals instanceof MyGoals) {
            if (!is_null($this->myGoals->includeClinical)) {
                $settings->setIncludeClinicalInMygoals($this->myGoals->includeClinical);
            }

            if (!is_null($this->myGoals->includeField)) {
                $settings->setIncludeFieldInMygoals($this->myGoals->includeField);
            }

            if (!is_null($this->myGoals->includeLab)) {
                $settings->setIncludeLabInMygoals($this->myGoals->includeLab);
            }

            if (!is_null($this->myGoals->subjectTypes)) {
                $settings->setSubjectTypesInMygoals($this->myGoals->subjectTypes);
            }
        }
    }

    /**
     * @param ProgramSettings $settings
     */
    private function setPracticeSkills(ProgramSettings $settings)
    {
        if ($this->practiceSkills instanceof PracticeSkills) {
            if (!is_null($this->practiceSkills->clinical)) {
                $settings->setPracticeSkillsClinical($this->practiceSkills->clinical);
            }

            if (!is_null($this->practiceSkills->field)) {
                $settings->setPracticeSkillsField($this->practiceSkills->field);
            }
        }
    }

    /**
     * @param ProgramSettings $settings
     */
    private function setEmailNotifications(ProgramSettings $settings)
    {
        if ($this->emailNotifications instanceof EmailNotifications) {
            if (!is_null($this->emailNotifications->sendSchedulerStudentNotifications)) {
                $settings->setSendSchedulerStudentNotifications(
                    $this->emailNotifications->sendSchedulerStudentNotifications
                );
            }
        }
    }

    /**
     * @param ProgramSettings $settings
     */
    private function setShiftDeadlines(ProgramSettings $settings)
    {
        if ($this->shiftDeadlines instanceof ShiftDeadlines) {
            if (!is_null($this->shiftDeadlines->autolockLateShifts)) {
                $settings->setAutolockLateShifts($this->shiftDeadlines->autolockLateShifts);
            }
        }
    }

    /**
     * @param ProgramSettings $settings
     */
    private function setStudentPermissions(ProgramSettings $settings)
    {
        if ($this->studentPermissions instanceof StudentPermissions) {
            if (!is_null($this->studentPermissions->viewFullCalendar)) {
                $settings->setStudentViewFullCalendar($this->studentPermissions->viewFullCalendar);
            }


            if (!is_null($this->studentPermissions->pickClinical)) {
                $settings->setStudentPickClinical($this->studentPermissions->pickClinical);
            }

            if (!is_null($this->studentPermissions->pickField)) {
                $settings->setStudentPickField($this->studentPermissions->pickField);
            }

            if (!is_null($this->studentPermissions->pickLab)) {
                $settings->setStudentPickLab($this->studentPermissions->pickLab);
            }


            if (!is_null($this->studentPermissions->switchClinical)) {
                $settings->setStudentSwitchClinical($this->studentPermissions->switchClinical);
            }

            if (!is_null($this->studentPermissions->switchField)) {
                $settings->setStudentSwitchField($this->studentPermissions->switchField);
            }

            if (!is_null($this->studentPermissions->switchLab)) {
                $settings->setStudentSwitchLab($this->studentPermissions->switchLab);
            }


            if (!is_null($this->studentPermissions->switchClinicalNeedsPermission)) {
                $settings->setSwitchClinicalNeedsPermission($this->studentPermissions->switchClinicalNeedsPermission);
            }

            if (!is_null($this->studentPermissions->switchFieldNeedsPermission)) {
                $settings->setSwitchFieldNeedsPermission($this->studentPermissions->switchFieldNeedsPermission);
            }

            if (!is_null($this->studentPermissions->switchLabNeedsPermission)) {
                $settings->setSwitchLabNeedsPermission($this->studentPermissions->switchLabNeedsPermission);
            }
        }
    }
}
