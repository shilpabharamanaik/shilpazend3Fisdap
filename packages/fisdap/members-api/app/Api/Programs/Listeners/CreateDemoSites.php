<?php namespace Fisdap\Api\Programs\Listeners;

use Fisdap\Api\Programs\Events\ProgramWasCreated;
use Fisdap\Data\Base\BaseLegacyRepository;
use Fisdap\Data\Program\ProgramLegacyRepository;
use Fisdap\Data\Site\SiteLegacyRepository;
use Fisdap\Entity\BaseLegacy;
use Fisdap\Entity\ProgramLegacy;
use Fisdap\Entity\SiteLegacy;
use Fisdap\Logging\Events\EventLogging;
use Illuminate\Contracts\Queue\ShouldQueue;


/**
 * Class CreateDemoSites
 *
 * @package Fisdap\Api\Programs\Listeners
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class CreateDemoSites implements ShouldQueue
{
    use EventLogging;
    
    
    /**
     * @var ProgramLegacyRepository
     */
    private $programLegacyRepository;
    
    /**
     * @var SiteLegacyRepository
     */
    private $siteLegacyRepository;
    
    /**
     * @var BaseLegacyRepository
     */
    private $baseLegacyRepository;


    /**
     * CreateDemoSites constructor.
     *
     * @param ProgramLegacyRepository $programLegacyRepository
     * @param SiteLegacyRepository    $siteLegacyRepository
     * @param BaseLegacyRepository    $baseLegacyRepository
     */
    public function __construct(
        ProgramLegacyRepository $programLegacyRepository,
        SiteLegacyRepository $siteLegacyRepository,
        BaseLegacyRepository $baseLegacyRepository
    ) {
        $this->programLegacyRepository = $programLegacyRepository;
        $this->siteLegacyRepository = $siteLegacyRepository;
        $this->baseLegacyRepository = $baseLegacyRepository;
    }


    /**
     * @param ProgramWasCreated $event
     */
    public function handle(ProgramWasCreated $event)
    {
        /** @var ProgramLegacy $program */
        $program = $this->programLegacyRepository->getOneById($event->getId());

        foreach ($program->getProgramTypes() as $type) {
            switch ($type->id) {
                case 1:
                    $type = 'lab';
                    break;
                case 2:
                case 3:
                    $type = 'clinical';
                    break;
                case 4:
                    $type = 'field';
                    break;
            }

            // todo - site and base creation should be handled by two Job classes respectively
            $site = new SiteLegacy;
            $site->name = $program->getName();
            $site->abbreviation = $program->getAbbreviation();
            $site->address = $program->getAddress();
            $site->city = $program->getCity();
            $site->state = $program->getState();
            $site->zipcode = $program->getZip();
            $site->country = $program->getCountry();
            $site->phone = $program->getPhone();
            $site->type = $type;
            $site->owner_program = $program;
            
            $this->siteLegacyRepository->store($site);

            $base = new BaseLegacy;
            $base->name = "Main";
            $base->site = $site;
            $base->abbreviation = "Main";
            $base->city = $program->getCity();
            $base->state = $program->getState();
            $base->address = $program->getAddress();
            $base->zip = $program->getZip();
            $base->type = $type;
            
            $this->baseLegacyRepository->store($base);

            $program->addSite($site, true);
            $program->addBase($base, true);

            $this->programLegacyRepository->update($program);
            
            $this->eventLogInfo('Created Demo Site/Base', [
                'programId' => $program->getId(), 'siteId' => $site->id, 'siteType' => $type, 'baseId' => $base->id
            ]);
        }
    }
}