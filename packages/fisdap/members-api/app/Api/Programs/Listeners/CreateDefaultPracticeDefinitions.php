<?php namespace Fisdap\Api\Programs\Listeners;

use Fisdap\Api\Programs\Events\ProgramWasCreated;
use Fisdap\Api\Shifts\PracticeItems\PopulatesPracticeDefinitions;
use Fisdap\Data\Program\ProgramLegacyRepository;
use Fisdap\Logging\Events\EventLogging;
use Illuminate\Contracts\Queue\ShouldQueue;


/**
 * Class CreateDefaultPracticeDefinitions
 *
 * @package Fisdap\Api\Programs\Listeners
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class CreateDefaultPracticeDefinitions implements ShouldQueue
{
    use EventLogging;
    
    
    /**
     * @var ProgramLegacyRepository
     */
    private $programLegacyRepository;
    
    /**
     * @var PopulatesPracticeDefinitions
     */
    private $practicePopulator;


    /**
     * CreateDefaultPracticeDefinitions constructor.
     *
     * @param ProgramLegacyRepository      $programLegacyRepository
     * @param PopulatesPracticeDefinitions $practicePopulator
     */
    public function __construct(ProgramLegacyRepository $programLegacyRepository, PopulatesPracticeDefinitions $practicePopulator)
    {
        $this->programLegacyRepository = $programLegacyRepository;
        $this->practicePopulator = $practicePopulator;
    }


    /**
     * @param ProgramWasCreated $event
     */
    public function handle(ProgramWasCreated $event)
    {
        /** @noinspection PhpParamsInspection */
        $this->practicePopulator->populatePracticeDefinitions($this->programLegacyRepository->getOneById($event->getId()));
        
        $this->eventLogInfo('Populated default practice definitions', ['programId' => $event->getId()]);
    }
}