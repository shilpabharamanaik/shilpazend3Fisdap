<?php

/**
 * Class Util_PracticePopulator
 * @deprecated 
 */
class Util_PracticePopulator {
    
    /**
     * @var array the default categories
     */
    public $defaultCategories = array();
    
    /**
     * @var integer ID of profession for current cached categories
     */
    public $professionId;
    
    /**
     * @var Doctrine\ORM\EntityManager
     */
    public $em;
	public static $counter = 1;

    public function __construct()
    {
		$this->populateDefaultCategories();
        $this->em = \Fisdap\EntityUtils::getEntityManager();
    }

	/**
	 * @param int $professionId
	 * @deprecated 
	 */
	public function populateDefaultCategories($professionId = 1)
	{
        $this->professionId = $professionId;
        $this->defaultCategories = \Fisdap\EntityUtils::getRepository("PracticeCategoryDefault")->findByProfession($professionId);
	}

	/**
	 * @param $program
	 * @deprecated 
	 */
    public function populatePracticeDefinitions($program)
	{
        if ($this->professionId != $program->profession->id) {
            $this->populateDefaultCategories($program->profession->id);            
        }
        
        foreach($this->defaultCategories as $defaultCategory) {
            $cat = \Fisdap\EntityUtils::getEntity("PracticeCategory");
            $cat->program = $program;
            $cat->name = $defaultCategory->name;
            $cat->certification_level = $defaultCategory->certification_level;
            
            foreach($defaultCategory->practice_definitions as $defaultDefinition) {
                $def = \Fisdap\EntityUtils::getEntity("PracticeDefinition");
                $def->skillsheet = $defaultDefinition->skillsheet;
                $def->name = $defaultDefinition->name;
                $def->active = $defaultDefinition->active;
                $def->peer_goal = $defaultDefinition->peer_goal;
                $def->instructor_goal = $defaultDefinition->instructor_goal;
                $def->eureka_window = $defaultDefinition->eureka_window;
                $def->eureka_goal = $defaultDefinition->eureka_goal;
                $def->airway_management_credit = $defaultDefinition->airway_management_credit;
                $def->setPracticeSkillIds($defaultDefinition->getPracticeSkillIds());
                $cat->addPracticeDefinition($def);
            }
            
            $cat->save(false);
        }

		if ((self::$counter % 50) == 0) {
			$this->em->flush();
			$this->em->clear();
			echo "Doctrine cleared\n";
			$this->populateDefaultCategories($this->professionId);
		}
		self::$counter++;
    }

	/**
	 * @deprecated 
	 */
    public function populateAllProgramPracticeDefinitions()
    {
        
        $q = $this->em->createQuery('select p.id from Fisdap\Entity\ProgramLegacy p');
//        $iterableResult = $q->iterate();
//        foreach ($iterableResult AS $row) {
//            // do stuff with the data in the row, $row[0] is always the object
//            $this->populatePracticeDefinitions($row[0]);
//			echo get_class($row[0]) . "\n";
//			echo $row[0]->id . "\n";
//			echo (memory_get_usage() / 1024) . "\n";
//
//            // detach from Doctrine, so that it can be Garbage-Collected immediately
//            $this->em->detach($row[0]);
//        }
		$results = $q->getResult();
		foreach($results as $program) {
			$this->populatePracticeDefinitions($program['id']);
			echo (memory_get_usage() / 1024) . "\n";
		}
    }

	/**
	 * @param $program
	 * @deprecated 
	 */
    public function updatePPCPSkillsheets($program)
    {
        //double check that we really should convert

        $ppcpSkillsheets = array(607,653,631,656,657,658,659,644,645,646,647,648,649,650,651,608,609,616,624,625,626,628,629,630,632,675,677,634,635,636,637,652,655,654,627);
        $continue = false;
        $definitions = \Fisdap\EntityUtils::getRepository('PracticeDefinition')->getProgramDefinitions($program);
        foreach($definitions as $definition){
            if(in_array($definition->skillsheet->id, $ppcpSkillsheets)){
                $continue = true;
                break;
            }
        }
        //If we didn't find any matching PPCP skillsheets, bail out.
        if(!$continue){
            return;
        }

        $skillsheets = array(
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
        );

        //update the skillset_id attached to each practice definition
        foreach($skillsheets as $oldSkillsheetId => $newSkillsheetId) {
            $qb = $this->em->createQueryBuilder();
            $qb->update("\Fisdap\Entity\PracticeDefinition", "pd")
                ->set("pd.skillsheet", "?1")
                ->andWhere("pd.skillsheet = ?2")
                ->andWhere("pd.program = ?3")
                ->setParameters(array(1 => $newSkillsheetId, 2 => $oldSkillsheetId, 3 => $program->id));

            //echo $qb->getQuery()->getSQL();
            $qb->getQuery()->execute();
        }

        //rename alt airway device to supraglottic
        $qb = $this->em->createQueryBuilder();
        $qb->select('pd')
            ->from('\Fisdap\Entity\PracticeDefinition', 'pd')
            ->where('pd.name = ?1')
            ->andWhere('pd.program = ?2')
            ->setParameters(array(1 => "Alternative Airway Device Adult", 2 => $program->id));
        $airwayDeviceResult = $qb->getQuery()->getResult();
        $airwayDevice = $airwayDeviceResult[0];

        if($airwayDevice instanceof \Fisdap\Entity\PracticeDefinition){
            $airwayDevice->name = "Supraglottic Airway Device Adult";
            $airwayDevice->save();
        }

        //rename Team Leader to Team Leader - Adult
        $qb = $this->em->createQueryBuilder();
        $qb->select('pd')
            ->from('\Fisdap\Entity\PracticeDefinition', 'pd')
            ->where('pd.name = ?1')
            ->andWhere('pd.program = ?2')
            ->setParameters(array(1 => "Team Leader", 2 => $program->id));
        $teamLeaderResult = $qb->getQuery()->getResult();
        $teamLeader = $teamLeaderResult[0];

        if($teamLeader instanceof \Fisdap\Entity\PracticeDefinition){
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
                ->from('\Fisdap\Entity\PracticeCategory', 'c')
                ->where('c.name = ?1')
                ->andWhere('c.program = ?2')
                ->setParameters(array(1 => "Scenarios", 2 => $program->id));
            $categoryResult = $qb->getQuery()->getResult();
            $category = $categoryResult[0];


            if($category instanceof \Fisdap\Entity\PracticeCategory) {

                $def = \Fisdap\EntityUtils::getEntity("PracticeDefinition");
                $def->skillsheet = 1634;
                $def->name = "Team Leader - Pediatric";
                $def->active = 1;
                $def->peer_goal = 6;
                $def->instructor_goal = 3;
                $def->eureka_window = 0;
                $def->eureka_goal = 0;
                $category->addPracticeDefinition($def);

                $def = \Fisdap\EntityUtils::getEntity("PracticeDefinition");
                $def->skillsheet = 1634;
                $def->name = "Team Leader - Geriatric";
                $def->active = 1;
                $def->peer_goal = 6;
                $def->instructor_goal = 3;
                $def->eureka_window = 0;
                $def->eureka_goal = 0;
                $category->addPracticeDefinition($def);

                $category->save();
            }
        }
    }

	/**
	 * @param $program
	 * @deprecated 
	 */
	public function updateSkillsheets($program)
	{
		//Don't update the skillsheets if they already have the new practice category
		if (count(\Fisdap\EntityUtils::getRepository("PracticeCategory")->findBy(array("program" => $program, "name" => "History Taking and Physical Examination")))) {
			return;
		}
		
		$skillsheets = array(
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
		);
		
		foreach($skillsheets as $oldSkillsheetId => $newSkillsheetId) {
			$qb = $this->em->createQueryBuilder();
			$qb->update("\Fisdap\Entity\PracticeDefinition", "pd")
			   ->set("pd.skillsheet", "?1")
			   ->andWhere("pd.skillsheet = ?2")
			   ->andWhere("pd.program = ?3")
			   ->setParameters(array(1 => $newSkillsheetId, 2 => $oldSkillsheetId, 3 => $program->id));
			
			//echo $qb->getQuery()->getSQL();
			$qb->getQuery()->execute();
		}
		
		//Create new category for History Taking and Physical Examination
		$cat = \Fisdap\EntityUtils::getEntity("PracticeCategory");
		$cat->program = $program;
		$cat->name = "History Taking and Physical Examination";
		$cat->certification_level = 3;
		
		//Add three definitions to this new category
		$definitions = array(
			627 => "Obtain a Patient History",
			654 => "Comprehensive Normal Adult Physical Assessment Techniques",
			655 => "Comprehensive Normal Pediatric Physical Assessment Techniques",
		);
		
		foreach($definitions as $skillsheet => $name) {
			$def = \Fisdap\EntityUtils::getEntity("PracticeDefinition");
			$def->skillsheet = $skillsheet;
			$def->name = $name;
			$def->active = 1;
			$def->peer_goal = 5;
			$def->instructor_goal = 1;
			$def->eureka_window = 0;
			$def->eureka_goal = 0;
			$cat->addPracticeDefinition($def);
		}
		
		$this->removeDeprecatedPracticeDefinitions($program);
		$this->updateDeprecatedPracticeDefinitions($program);
		$cat->save();
	}
	
	/**
	 * Delete old practice definitions for this program if they
	 * have no items filled out for that definition.
	 *
	 * @param \Fisdap\Entity\ProgramLegacy
	 */
	private function removeDeprecatedPracticeDefinitions($program)
	{
		$definitions = \Fisdap\EntityUtils::getRepository("PracticeDefinition")->findBy(array("name" => "Team Leader - Pediatric Patient", "program" => $program));
		foreach($definitions as $definition) {
			if ($definition->practice_items->count() == 0) {
				$definition->delete(false);
			}
		}
	}
	
	/**
	 * Rename old practice definitions for this program if they
	 * have no items filled out for that definition.
	 *
	 * @param \Fisdap\Entity\ProgramLegacy
	 */
	private function updateDeprecatedPracticeDefinitions($program)
	{
		$definitions = \Fisdap\EntityUtils::getRepository("PracticeDefinition")->findBy(array("name" => "Team Leader - Adult Patient", "program" => $program));
		foreach($definitions as $definition) {
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
