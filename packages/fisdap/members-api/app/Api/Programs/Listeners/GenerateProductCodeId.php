<?php namespace Fisdap\Api\Programs\Listeners;

use Fisdap\Api\Programs\Events\ProgramWasCreated;
use Fisdap\Data\Program\ProgramLegacyRepository;
use Fisdap\Entity\ProgramLegacy;
use Fisdap\Logging\Events\EventLogging;

/**
 * Event listener that generates a Product Code ID for a Program
 *
 * @package Fisdap\Api\Users\UserContexts\Listeners
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class GenerateProductCodeId
{
    use EventLogging;
    
    
    /**
     * @var ProgramLegacyRepository
     */
    private $programLegacyRepository;


    /**
     * GenerateProductCodeId constructor.
     *
     * @param ProgramLegacyRepository $programLegacyRepository
     */
    public function __construct(ProgramLegacyRepository $programLegacyRepository)
    {
        $this->programLegacyRepository = $programLegacyRepository;
    }


    /**
     * @param ProgramWasCreated $event
     *
     * @throws \Exception
     */
    public function handle(ProgramWasCreated $event)
    {
        /** @var ProgramLegacy $program */
        $program = $this->programLegacyRepository->getOneById($event->getId());
        
        $program->generateProductCodeId();
        
        $this->programLegacyRepository->update($program);
        
        $this->eventLogInfo('Generated product code ID', [
            'programId' => $program->getId(), 'productCodeId' => $program->getProductCodeId()
        ]);
    }
}
