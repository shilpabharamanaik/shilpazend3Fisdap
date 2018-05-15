<?php namespace Fisdap\Api\Shifts\PracticeItems;

use Doctrine\ORM\EntityManager;
use Fisdap\Data\Practice\PracticeCategoryRepository;
use Fisdap\Data\Practice\PracticeDefinitionRepository;
use Fisdap\Entity\PracticeCategory;
use Fisdap\Entity\PracticeCategoryDefault;
use Fisdap\Entity\PracticeDefinition;
use Fisdap\Entity\ProgramLegacy;

/**
 * Class PracticePopulator
 *
 * Adapted from Util_PracticePopulator in Members codebase
 *
 * @package Fisdap\Api\Shifts\PracticeItems
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class PracticePopulator implements PopulatesPracticeDefinitions
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var PracticeCategoryRepository
     */
    private $practiceCategoryRepository;

    /**
     * @var PracticeDefinitionRepository
     */
    private $practiceDefinitionRepository;

    /**
     * @var array the default categories
     */
    private $defaultCategories = [];

    /**
     * @var integer ID of profession for current cached categories
     */
    private $professionId;


    /**
     * PracticePopulator constructor.
     *
     * @param EntityManager                $entityManager
     * @param PracticeCategoryRepository   $practiceCategoryRepository
     * @param PracticeDefinitionRepository $practiceDefinitionRepository
     */
    public function __construct(
        EntityManager $entityManager,
        PracticeCategoryRepository $practiceCategoryRepository,
        PracticeDefinitionRepository $practiceDefinitionRepository
    ) {
        $this->em = $entityManager;
        $this->practiceCategoryRepository = $practiceCategoryRepository;
        $this->practiceDefinitionRepository = $practiceDefinitionRepository;
        
        $this->setDefaultCategories();
    }


    /**
     * @inheritdoc
     */
    public function populatePracticeDefinitions(ProgramLegacy $program)
    {
        $counter = 1;
        $batchSize = 50;

        if ($this->professionId != $program->getProfession()->getId()) {
            $this->setDefaultCategories($program->getProfession()->getId());
        }

        foreach ($this->defaultCategories as $defaultCategory) {
            $practiceCategory = new PracticeCategory;
            $practiceCategory->program = $program;
            $practiceCategory->name = $defaultCategory->name;
            $practiceCategory->certification_level = $defaultCategory->certification_level;

            foreach ($defaultCategory->practice_definitions as $defaultDefinition) {
                $practiceDefinition = new PracticeDefinition;
                $practiceDefinition->skillsheet = $defaultDefinition->skillsheet;
                $practiceDefinition->name = $defaultDefinition->name;
                $practiceDefinition->active = $defaultDefinition->active;
                $practiceDefinition->peer_goal = $defaultDefinition->peer_goal;
                $practiceDefinition->instructor_goal = $defaultDefinition->instructor_goal;
                $practiceDefinition->eureka_window = $defaultDefinition->eureka_window;
                $practiceDefinition->eureka_goal = $defaultDefinition->eureka_goal;
                $practiceDefinition->airway_management_credit = $defaultDefinition->airway_management_credit;
                $practiceDefinition->setPracticeSkillIds($defaultDefinition->getPracticeSkillIds());
                $practiceCategory->addPracticeDefinition($practiceDefinition);
            }

            $this->em->persist($practiceCategory);

            if (($counter % $batchSize) === 0) {
                $this->em->flush();
                $this->em->clear();
            }

            ++$counter;
        }

        $this->em->flush();
        $this->em->clear();
    }


    /**
     * @inheritdoc
     */
    public function updatePPCPSkillsheets(ProgramLegacy $program)
    {
        //double check that we really should convert

        $ppcpSkillsheets = [
            607,
            653,
            631,
            656,
            657,
            658,
            659,
            644,
            645,
            646,
            647,
            648,
            649,
            650,
            651,
            608,
            609,
            616,
            624,
            625,
            626,
            628,
            629,
            630,
            632,
            675,
            677,
            634,
            635,
            636,
            637,
            652,
            655,
            654,
            627
        ];
        $continue = false;
        $definitions = $this->practiceDefinitionRepository->getProgramDefinitions($program);
        foreach ($definitions as $definition) {
            if (in_array($definition->skillsheet->id, $ppcpSkillsheets)) {
                $continue = true;
                break;
            }
        }
        //If we didn't find any matching PPCP skillsheets, bail out.
        if (! $continue) {
            return;
        }

        $skillsheets = [
            653 => 1632,
            631 => 1606,
            656 => 1608,
            657 => 1629,
            658 => 1603,
            659 => 1604,
            644 => 1626,
            645 => 1617,
            646 => 1625,
            647 => 1623,
            648 => 1624,
            649 => 1622,
            650 => 1620,
            651 => 1621,
            607 => 1619,
            608 => 1614,
            609 => 1615,
            616 => 1618,
            624 => 1605,
            625 => 1607,
            626 => 1631,
            628 => 1611,
            629 => 1613,
            630 => 1612,
            632 => 1628,
            675 => 1634,
            677 => 1633,
            634 => 1616,
            635 => 1630,
            636 => 1609,
            637 => 1610,
            652 => 1627,
            655 => 1602,
            654 => 1601,
            627 => 1599
        ];

        //update the skillset_id attached to each practice definition
        foreach ($skillsheets as $oldSkillsheetId => $newSkillsheetId) {
            $qb = $this->em->createQueryBuilder();
            $qb->update(PracticeDefinition::class, "pd")
                ->set('pd.skillsheet', '?1')
                ->andWhere('pd.skillsheet = ?2')
                ->andWhere('pd.program = ?3')
                ->setParameters([1 => $newSkillsheetId, 2 => $oldSkillsheetId, 3 => $program->getId()]);

            //echo $qb->getQuery()->getSQL();
            $qb->getQuery()->execute();
        }

        //rename alt airway device to supraglottic
        $qb = $this->em->createQueryBuilder();
        $qb->select('pd')
            ->from(PracticeDefinition::class, 'pd')
            ->where('pd.name = ?1')
            ->andWhere('pd.program = ?2')
            ->setParameters([1 => "Alternative Airway Device Adult", 2 => $program->getId()]);
        $airwayDeviceResult = $qb->getQuery()->getResult();
        $airwayDevice = $airwayDeviceResult[0];

        if ($airwayDevice instanceof PracticeDefinition) {
            $airwayDevice->name = "Supraglottic Airway Device Adult";
            $airwayDevice->save();
        }

        //rename Team Leader to Team Leader - Adult
        $qb = $this->em->createQueryBuilder();
        $qb->select('pd')
            ->from(PracticeDefinition::class, 'pd')
            ->where('pd.name = ?1')
            ->andWhere('pd.program = ?2')
            ->setParameters([1 => "Team Leader", 2 => $program->getId()]);
        $teamLeaderResult = $qb->getQuery()->getResult();
        $teamLeader = $teamLeaderResult[0];

        if ($teamLeader instanceof PracticeDefinition) {
            $teamLeader->name = "Team Leader - Adult";
            $teamLeader->peer_goal = 8;
            $teamLeader->instructor_goal = 4;
            $teamLeader->eureka_window = 0;
            $teamLeader->eureka_goal = 0;
            $teamLeader->save();

            //create two new practice items in the Scenarios category
            //find the category for scenarios for this program
            $qb = $this->em->createQueryBuilder();
            $qb->select('c')
                ->from(PracticeCategory::class, 'c')
                ->where('c.name = ?1')
                ->andWhere('c.program = ?2')
                ->setParameters([1 => "Scenarios", 2 => $program->getId()]);
            $categoryResult = $qb->getQuery()->getResult();
            $category = $categoryResult[0];


            if ($category instanceof PracticeCategory) {
                $practiceDefinition = new PracticeDefinition;
                $practiceDefinition->skillsheet = 1634;
                $practiceDefinition->name = "Team Leader - Pediatric";
                $practiceDefinition->active = 1;
                $practiceDefinition->peer_goal = 6;
                $practiceDefinition->instructor_goal = 3;
                $practiceDefinition->eureka_window = 0;
                $practiceDefinition->eureka_goal = 0;
                $category->addPracticeDefinition($practiceDefinition);

                $practiceDefinition = new PracticeDefinition;
                $practiceDefinition->skillsheet = 1634;
                $practiceDefinition->name = "Team Leader - Geriatric";
                $practiceDefinition->active = 1;
                $practiceDefinition->peer_goal = 6;
                $practiceDefinition->instructor_goal = 3;
                $practiceDefinition->eureka_window = 0;
                $practiceDefinition->eureka_goal = 0;
                $category->addPracticeDefinition($practiceDefinition);

                $category->save();
            }
        }
    }


    /**
     * @inheritdoc
     */
    public function updateSkillsheets(ProgramLegacy $program)
    {
        //Don't update the skillsheets if they already have the new practice category
        if (count($this->practiceCategoryRepository->findBy([
            "program" => $program,
            "name"    => "History Taking and Physical Examination"
        ]))) {
            return;
        }

        $skillsheets = [
            339 => 653,
            336 => 631,
            338 => 656,
            340 => 657,
            349 => 658,
            350 => 659,
            317 => 644,
            341 => 645,
            318 => 646,
            319 => 647,
            320 => 648,
            333 => 649,
            321 => 650,
            322 => 651,
            323 => 607,
            324 => 608,
            325 => 609,
            335 => 616,
            326 => 624,
            327 => 625,
            342 => 626,
            329 => 628,
            343 => 629,
            344 => 630,
            331 => 632,
            358 => 675,
            359 => 677,
            345 => 634,
            346 => 635,
            312 => 636,
            332 => 637,
            334 => 652,
        ];

        foreach ($skillsheets as $oldSkillsheetId => $newSkillsheetId) {
            $qb = $this->em->createQueryBuilder();
            $qb->update(PracticeDefinition::class, 'pd')
                ->set('pd.skillsheet', '?1')
                ->andWhere('pd.skillsheet = ?2')
                ->andWhere('pd.program = ?3')
                ->setParameters([1 => $newSkillsheetId, 2 => $oldSkillsheetId, 3 => $program->getId()]);

            //echo $qb->getQuery()->getSQL();
            $qb->getQuery()->execute();
        }

        //Create new category for History Taking and Physical Examination
        $practiceCategory = new PracticeCategory;
        $practiceCategory->program = $program;
        $practiceCategory->name = "History Taking and Physical Examination";
        $practiceCategory->certification_level = 3;

        //Add three definitions to this new category
        $definitions = [
            627 => "Obtain a Patient History",
            654 => "Comprehensive Normal Adult Physical Assessment Techniques",
            655 => "Comprehensive Normal Pediatric Physical Assessment Techniques",
        ];

        foreach ($definitions as $skillsheet => $name) {
            $practiceDefinition = new PracticeDefinition;
            $practiceDefinition->skillsheet = $skillsheet;
            $practiceDefinition->name = $name;
            $practiceDefinition->active = 1;
            $practiceDefinition->peer_goal = 5;
            $practiceDefinition->instructor_goal = 1;
            $practiceDefinition->eureka_window = 0;
            $practiceDefinition->eureka_goal = 0;
            $practiceCategory->addPracticeDefinition($practiceDefinition);
        }

        $this->removeDeprecatedPracticeDefinitions($program);
        $this->updateDeprecatedPracticeDefinitions($program);
        
        $this->practiceCategoryRepository->store($practiceCategory);
    }


    /**
     * @param int $professionId
     */
    private function setDefaultCategories($professionId = 1)
    {
        $this->professionId = $professionId;
        $this->defaultCategories = $this->em->getRepository(PracticeCategoryDefault::class)->findByProfession($professionId);
    }
    

    /**
     * Delete old practice definitions for this program if they
     * have no items filled out for that definition.
     *
     * @param ProgramLegacy $program
     */
    private function removeDeprecatedPracticeDefinitions(ProgramLegacy $program)
    {
        /** @var PracticeDefinition[] $definitions */
        $definitions = $this->practiceDefinitionRepository->findBy([
            "name"    => "Team Leader - Pediatric Patient",
            "program" => $program
        ]);
        foreach ($definitions as $definition) {
            if ($definition->practice_items->count() == 0) {
                $definition->delete(false);
            }
        }
    }


    /**
     * Rename old practice definitions for this program if they
     * have no items filled out for that definition.
     *
     * @param ProgramLegacy $program
     */
    private function updateDeprecatedPracticeDefinitions(ProgramLegacy $program)
    {
        /** @var PracticeDefinition[] $definitions */
        $definitions = $this->practiceDefinitionRepository->findBy([
            "name"    => "Team Leader - Adult Patient",
            "program" => $program
        ]);
        foreach ($definitions as $definition) {
            if ($definition->practice_items->count() == 0) {
                $definition->name = "Team Leader";
                $definition->eureka_window = 20;
                $definition->eureka_goal = 18;
                $definition->peer_goal = 10;
                $definition->instructor_goal = 5;
                $definition->save(false);
            }
        }
    }
}
