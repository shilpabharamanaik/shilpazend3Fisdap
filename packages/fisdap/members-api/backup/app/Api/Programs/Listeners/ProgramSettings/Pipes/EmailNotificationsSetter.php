<?php namespace Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes;

use Fisdap\Api\Programs\Listeners\ProgramSettings\EstablishesProgramSettings;

/**
 * Class EmailNotificationsSetter
 *
 * @package Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class EmailNotificationsSetter
{
    /**
     * @param EstablishesProgramSettings $listener
     */
    public function set(EstablishesProgramSettings $listener)
    {
        $listener->getProgram()->setSendLateShiftEmails(
            $listener->getEvent()->getSettings()->emailNotifications->sendLateShift
        );
        
        $listener->getProgram()->setSendCriticalThinkingEmails(
            $listener->getEvent()->getSettings()->emailNotifications->sendCriticalThinking
        );

        $listener->getProgram()->getProgramSettings()->setSendSchedulerStudentNotifications(
            $listener->getEvent()->getSettings()->emailNotifications->sendSchedulerStudentNotifications
        );
    }
}
