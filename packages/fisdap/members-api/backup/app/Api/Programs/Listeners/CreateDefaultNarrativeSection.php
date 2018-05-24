<?php namespace Fisdap\Api\Programs\Listeners;

use Fisdap\Api\Programs\Events\ProgramWasCreated;
use Fisdap\Data\Narrative\NarrativeSectionDefinitionRepository;
use Fisdap\Entity\NarrativeSectionDefinition;
use Fisdap\Logging\Events\EventLogging;
use Illuminate\Contracts\Queue\ShouldQueue;


/**
 * Class CreateDefaultNarrativeSection
 *
 * @package Fisdap\Api\Programs\Listeners
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class CreateDefaultNarrativeSection implements ShouldQueue
{
    use EventLogging;
    
    
    /**
     * @var NarrativeSectionDefinitionRepository
     */
    private $narrativeSectionDefinitionRepository;


    /**
     * CreateDefaultNarrativeSection constructor.
     *
     * @param NarrativeSectionDefinitionRepository $narrativeSectionDefinitionRepository
     */
    public function __construct(NarrativeSectionDefinitionRepository $narrativeSectionDefinitionRepository)
    {
        $this->narrativeSectionDefinitionRepository = $narrativeSectionDefinitionRepository;
    }


    /**
     * @param ProgramWasCreated $event
     */
    public function handle(ProgramWasCreated $event)
    {
        $narrativeSectionDefinition = new NarrativeSectionDefinition;
        $narrativeSectionDefinition->setProgramId($event->getId());
        
        $this->narrativeSectionDefinitionRepository->store($narrativeSectionDefinition);
        
        $this->eventLogInfo('Created default narrative section definition', ['programId' => $event->getId()]);
    }
}