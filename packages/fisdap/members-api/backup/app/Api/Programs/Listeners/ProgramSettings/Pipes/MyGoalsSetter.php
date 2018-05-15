<?php namespace Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes;

use Fisdap\Api\Programs\Listeners\ProgramSettings\EstablishesProgramSettings;

/**
 * Class MyGoalsSetter
 *
 * @package Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class MyGoalsSetter
{
    /**
     * @param EstablishesProgramSettings $listener
     */
    public function set(EstablishesProgramSettings $listener)
    {
        $listener->getProgram()->getProgramSettings()->setIncludeLabInMygoals(
            $listener->getEvent()->getSettings()->myGoals->includeLab
        );
        
        $listener->getProgram()->getProgramSettings()->setIncludeFieldInMygoals(
            $listener->getEvent()->getSettings()->myGoals->includeField
        );
        
        $listener->getProgram()->getProgramSettings()->setIncludeClinicalInMygoals(
            $listener->getEvent()->getSettings()->myGoals->includeClinical
        );
        
        $listener->getProgram()->getProgramSettings()->setSubjectTypesInMygoals(
            $listener->getEvent()->getSettings()->myGoals->subjectTypes
        );
    }
}
