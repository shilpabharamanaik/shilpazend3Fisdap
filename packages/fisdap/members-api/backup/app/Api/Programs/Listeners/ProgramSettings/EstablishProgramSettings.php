<?php namespace Fisdap\Api\Programs\Listeners\ProgramSettings;

use Fisdap\Api\Programs\Events\ProgramWasCreated;
use Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes\CommerceAttributesSetter;
use Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes\EmailNotificationsSetter;
use Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes\MiscSetter;
use Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes\MyGoalsSetter;
use Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes\ShiftDeadlinesSetter;
use Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes\SignoffSetter;
use Fisdap\Api\Programs\Listeners\ProgramSettings\Pipes\StudentPermissionsSetter;
use Fisdap\Data\Program\ProgramLegacyRepository;
use Fisdap\Entity\ProgramLegacy;
use Fisdap\Logging\Events\EventLogging;
use Illuminate\Pipeline\Pipeline;


/**
 * Event listener for establishing program settings when a program (ProgramLegacy Entity) was created
 *
 * @package Fisdap\Api\Programs\Listeners\ProgramSettings
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class EstablishProgramSettings implements EstablishesProgramSettings
{
    use EventLogging;

    
    /**
     * @var Pipeline
     */
    private $pipeline;

    /**
     * @var ProgramLegacyRepository
     */
    private $programLegacyRepository;
    
    /**
     * @var ProgramLegacy
     */
    private $program;

    /**
     * @var ProgramWasCreated
     */
    private $event;


    /**
     * EstablishEstablishesProgramSettings constructor.
     *
     * @param Pipeline                $pipeline
     * @param ProgramLegacyRepository $programLegacyRepository
     */
    public function __construct(Pipeline $pipeline, ProgramLegacyRepository $programLegacyRepository)
    {
        $this->pipeline = $pipeline;
        $this->programLegacyRepository = $programLegacyRepository;
    }


    /**
     * @inheritdoc
     */
    public function handle(ProgramWasCreated $event)
    {
        $this->event = $event;

        if (is_null($event->getSettings())) {
            return;
        }

        /** @var ProgramLegacy $program */
        $this->program = $program = $this->programLegacyRepository->getOneById($event->getId());

        $this->pipeline->send($this)->through([
            StudentPermissionsSetter::class,
            EmailNotificationsSetter::class,
            ShiftDeadlinesSetter::class,
            CommerceAttributesSetter::class,
            SignoffSetter::class,
            MyGoalsSetter::class,
            MiscSetter::class
        ])->via('set');

        $this->programLegacyRepository->update($program);
        
        $this->eventLogInfo('Established program settings', ['programId' => $event->getId()]);
    }


    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function getProgram()
    {
        return $this->program;
    }


    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function getEvent()
    {
        return $this->event;
    }
}
