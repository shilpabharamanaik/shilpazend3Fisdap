<?php namespace Fisdap\Api\Shifts\Patients\Narratives\Jobs;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Data\Narrative\NarrativeSectionDefinitionRepository;
use Fisdap\Entity\Narrative;
use Fisdap\Entity\NarrativeSection;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Fisdap\Entity\NarrativeSectionDefinition;

/**
 * Class SetNarrative
 * @package Fisdap\Api\Shifts\Patients\Narratives\Jobs
 * @author  Isaac White <isaac.white@ascendlearning.com>
 *
 * @SWG\Definition(
 *     definition="Narrative",
 *     required={ "sections" }
 * )
 */
final class SetNarrative extends Job implements RequestHydrated
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Narrative|null
     * @see Narrative
     * @SWG\Property(type="Narrative")
     */
    protected $narrative;

    /**
     * @var NarrativeSectionDefinition[]
     */
    protected $narrativeSectionDefinitions = [];
    
    /**
     * @var \Fisdap\Api\Shifts\Patients\Narratives\Jobs\SetSection[]
     * @See NarrativeSection
     * @SWG\Property(type="array", items=@SWG\Items(ref="#/definitions/Section"))
     */
    public $sections = [];

    /**
     * @param EntityManagerInterface $em
     * @param BusDispatcher $busDispatcher
     * @return Narrative|null
     */
    public function handle(
        EntityManagerInterface $em,
        BusDispatcher $busDispatcher
    ) {
        $this->em = $em;
        $narrative = $this->getNarrative() ? $this->getNarrative() : new Narrative;

        foreach ((array) $this->sections as $section) {
            $setSection = false;
            foreach ((array) $this->getNarrativeSectionDefinitions() as $definition) {
                if ($section->getDefId() === $definition->getId()) {
                    $section->setNarrativeId($this->getNarrative()->id);
                    $sec = $busDispatcher->dispatch($section);
                    $narrative->addSection($sec);
                    $setSection = true;
                }
            }
            if ($setSection === false) {
                throw new ResourceNotFound('No section definition ID '.$section->getDefId().' found for this narrative.');
            }
        }
        return $narrative;
    }

    public function setNarrative(Narrative $narrative = null)
    {
        $this->narrative = $narrative;
    }

    public function getNarrative()
    {
        return $this->narrative;
    }

    /**
     * @param $definitions
     */
    public function setNarrativeSectionDefinitions($definitions)
    {
        $this->narrativeSectionDefinitions = is_array($definitions) ? $definitions : [$definitions];
    }

    /**
     * @return \Fisdap\Entity\NarrativeSectionDefinition[]
     */
    public function getNarrativeSectionDefinitions()
    {
        return $this->narrativeSectionDefinitions;
    }
}
