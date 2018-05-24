<?php namespace Fisdap\Api\Shifts\Patients\Narratives\Jobs;


use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Entity\NarrativeSection;
use Fisdap\Entity\NarrativeSectionDefinition;

/**
 * Class SetSection
 * @package Fisdap\Api\Shifts\Patients\Narratives\Jobs
 * @author  Isaac White <isaac.white@ascendlearning.com>
 *
 * @SWG\Definition(
 *     definition="Section",
 *     description="This is a model representation of a default Section",
 *     required={ "definitionId" }
 * )
 */
final class SetSection extends Job implements RequestHydrated
{
    /**
     * Not really used
     * @var int
     */
    protected $id;

    /**
     * @var integer
     * @SWG\Property(type="integer", example=1)
     */
    public $definitionId;

    /**
     * @var string|null
     * @SWG\Property(type="string", example="Some default text")
     */
    public $sectionText;

    /**
     * @var integer
     */
    public $narrativeId;

    public function handle(
        EntityManagerInterface $em
    )
    {
        $this->em = $em;

        $narrativeSection = $this->em->getRepository(NarrativeSection::class)->findBy(['definition' => $this->getDefId(), 'narrative' => $this->getNarrativeId()]);
        $narrativeSection = $narrativeSection ? current($narrativeSection) : new NarrativeSection;

        $narrativeSection->set_section_text($this->getSecText());
        $narrativeSectionDefinition = $this->em->getRepository(NarrativeSectionDefinition::class)->find($this->getDefId());
        $narrativeSection->setSectionDefinition($narrativeSectionDefinition);

        return $narrativeSection;
    }

    /**
     * @param $definitionId
     */
    public function setDefId($definitionId)
    {
        $this->definitionId = $definitionId;
    }

    /**
     * @return int
     */
    public function getDefId()
    {
        return $this->definitionId;
    }

    /**
     * @param $sectionText|null
     */
    public function setSecText($sectionText = null)
    {
        $this->sectionText = $sectionText;
    }

    /**
     * @return string|null
     */
    public function getSecText()
    {
        return $this->sectionText;
    }

    /**
     * @param $narrativeId
     */
    public function setNarrativeId($narrativeId)
    {
        $this->narrativeId = $narrativeId;    
    }

    /**
     * @return integer
     */
    public function getNarrativeId()
    {
        return $this->narrativeId;
    }
}




