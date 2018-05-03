<?php namespace Fisdap\Api\Programs\Settings\Jobs\Models;

use Swagger\Annotations as SWG;

/**
 * Class Settings
 *
 * @package Fisdap\Api\Programs\Settings\Jobs\Models
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 *
 * @SWG\Definition(definition="ProgramSettings")
 */
final class Settings
{
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


    // SCHEDULER SETTINGS -todo
//    /**
//     * @var \Fisdap\Entity\Window
//     * @ManyToOne(targetEntity="Window")
//     */
//    public $default_field_window;
//
//    /**
//     * @var \Fisdap\Entity\Window
//     * @ManyToOne(targetEntity="Window")
//     */
//    public $default_lab_window;
//
//    /**
//     * @var \Fisdap\Entity\Window
//     * @ManyToOne(targetEntity="Window")
//     */
//    public $default_clinical_window;

    
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
}
