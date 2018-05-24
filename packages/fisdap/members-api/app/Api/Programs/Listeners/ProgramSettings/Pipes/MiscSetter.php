<?php namespace Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes;

use Fisdap\Api\Programs\Listeners\ProgramSettings\EstablishesProgramSettings;
use Fisdap\Data\Timezone\TimezoneRepository;


/**
 * Class MiscSetter
 *
 * @package Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class MiscSetter
{
    /**
     * @var TimezoneRepository
     */
    private $timezoneRepository;


    /**
     * MiscSetter constructor.
     *
     * @param TimezoneRepository $timezoneRepository
     */
    public function __construct(TimezoneRepository $timezoneRepository)
    {
        $this->timezoneRepository = $timezoneRepository;
    }


    /**
     * @param EstablishesProgramSettings $listener
     */
    public function set(EstablishesProgramSettings $listener)
    {
        $listener->getProgram()->getProgramSettings()->setAllowEducatorShiftAudit(
            $listener->getEvent()->getSettings()->allowShiftAudit
        );

        $listener->getProgram()->getProgramSettings()->setAllowEducatorEvaluations(
            $listener->getEvent()->getSettings()->allowEvaluations
        );

        $listener->getProgram()->getProgramSettings()->setQuickAddClinical(
            $listener->getEvent()->getSettings()->quickAddClinical
        );


        /** @noinspection PhpParamsInspection */
        $listener->getProgram()->getProgramSettings()->setTimezone(
            $this->timezoneRepository->getOneById($listener->getEvent()->getSettings()->timezoneId)
        );
    }
}