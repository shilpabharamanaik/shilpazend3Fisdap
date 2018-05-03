<?php namespace Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes;

use Fisdap\Api\Programs\Listeners\ProgramSettings\EstablishesProgramSettings;


/**
 * Class PracticeSkillsSetter
 *
 * @package Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class PracticeSkillsSetter
{
    /**
     * @param EstablishesProgramSettings $listener
     */
    public function set(EstablishesProgramSettings $listener)
    {
        $listener->getProgram()->getProgramSettings()->setPracticeSkillsField(
            $listener->getEvent()->getSettings()->practiceSkills->field
        );

        $listener->getProgram()->getProgramSettings()->setPracticeSkillsClinical(
            $listener->getEvent()->getSettings()->practiceSkills->clinical
        );
    }
}