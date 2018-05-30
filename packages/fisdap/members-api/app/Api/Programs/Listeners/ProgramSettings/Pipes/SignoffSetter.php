<?php namespace Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes;

use Fisdap\Api\Programs\Listeners\ProgramSettings\EstablishesProgramSettings;

/**
 * Class SignoffSetter
 *
 * @package Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class SignoffSetter
{
    /**
     * @param EstablishesProgramSettings $listener
     */
    public function set(EstablishesProgramSettings $listener)
    {
        $listener->getProgram()->getProgramSettings()->setAllowEducatorSignoffSignature(
            $listener->getEvent()->getSettings()->signoff->allowWithSignature
        );

        $listener->getProgram()->getProgramSettings()->setAllowEducatorSignoffLogin(
            $listener->getEvent()->getSettings()->signoff->allowWithLogin
        );

        $listener->getProgram()->getProgramSettings()->setAllowEducatorSignoffEmail(
            $listener->getEvent()->getSettings()->signoff->allowWithEmail
        );

        $listener->getProgram()->getProgramSettings()->setAllowEducatorSignoffAttachment(
            $listener->getEvent()->getSettings()->signoff->allowWithAttachment
        );

        $listener->getProgram()->getProgramSettings()->setAllowSignoffOnPatient(
            $listener->getEvent()->getSettings()->signoff->allowOnPatient
        );

        $listener->getProgram()->getProgramSettings()->setAllowSignoffOnShift(
            $listener->getEvent()->getSettings()->signoff->allowOnShift
        );
    }
}
