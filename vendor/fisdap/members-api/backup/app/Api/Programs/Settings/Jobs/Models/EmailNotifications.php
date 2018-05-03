<?php namespace Fisdap\Api\Programs\Settings\Jobs\Models;

use Swagger\Annotations as SWG;

/**
 * Class EmailNotifications
 *
 * @package Fisdap\Api\Programs\Settings\Jobs\Models
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 *
 * @SWG\Definition(definition="ProgramSettingsEmailNotifications")
 */
final class EmailNotifications
{
    /**
     * @var bool
     * @see ProgramLegacy::$send_critical_thinking_emails
     * @SWG\Property(example=true)
     */
    public $sendCriticalThinking = true;
    
    /**
     * @var bool
     * @see ProgramLegacy::$send_late_shift_emails
     * @SWG\Property(example=true)
     */
    public $sendLateShift = true;
    
    /**
     * @var bool
     * @see ProgramSettings::$send_scheduler_student_notifications
     * @SWG\Property(example=true)
     */
    public $sendSchedulerStudentNotifications = true;
}
