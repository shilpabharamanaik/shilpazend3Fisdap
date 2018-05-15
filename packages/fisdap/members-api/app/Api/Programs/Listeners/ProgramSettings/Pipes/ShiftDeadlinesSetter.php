<?php namespace Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes;

use Fisdap\Api\Programs\Listeners\ProgramSettings\EstablishesProgramSettings;

/**
 * Class ShiftDeadlinesSetter
 *
 * @package Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ShiftDeadlinesSetter
{
    /**
     * @param EstablishesProgramSettings $listener
     */
    public function set(EstablishesProgramSettings $listener)
    {
        $listener->getProgram()->setLateClinicalDeadline(
            $listener->getEvent()->getSettings()->shiftDeadlines->lateClinicalDeadlineHours
        );
        
        $listener->getProgram()->setLateFieldDeadline(
            $listener->getEvent()->getSettings()->shiftDeadlines->lateFieldDeadlineHours
        );
        
        $listener->getProgram()->setLateLabDeadline(
            $listener->getEvent()->getSettings()->shiftDeadlines->lateLabDeadlineHours
        );

        
        $listener->getProgram()->getProgramSettings()->setAutolockLateShifts(
            $listener->getEvent()->getSettings()->shiftDeadlines->autolockLateShifts
        );
    }
}
