<?php namespace Fisdap\Data\Practice;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;
use Fisdap\Entity\InstructorLegacy;
use Fisdap\EntityUtils;


/**
 * Class DoctrinePracticeItemRepository
 *
 * @package Fisdap\Data\Practice
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrinePracticeItemRepository extends DoctrineRepository implements PracticeItemRepository
{
	public function getAllByDefinition($defId, $studentId = null)
	{
		$qb = $this->_em->createQueryBuilder();

		$def = EntityUtils::getEntity("PracticeDefinition", $defId);
		
		$qb->select('item')
		   ->from('\Fisdap\Entity\PracticeItem', 'item')
		   ->leftJoin('item.shift', 'shift')
		   ->where('item.practice_definition = ?1')
		   ->andWhere($this->getStudentQuery($studentId))
		   ->andWhere('(item.evaluator_type = 1 and item.confirmed = 1) or (item.evaluator_type = 2)')
		   ->orderBy('shift.start_datetime, item.time', 'ASC')
		   ->setParameter(1, $defId);
		
		if($studentId){
			$qb->setParameter(2, $studentId);
		}
		

		return $qb->getQuery()->getResult();
	}
	
	public function getAllSuccessfulByStudent($studentId){
		$qb = $this->_em->createQueryBuilder();

		$qb->select('item')
		   ->from('\Fisdap\Entity\PracticeItem', 'item')
		   ->leftJoin('item.shift', 'shift')
		   ->leftJoin('item.practice_definition', 'def')
		   ->where('def.active = 1')
		   ->andWhere('item.student = ?1')
		   ->andWhere('item.passed = 1')
		   ->orderBy('shift.start_datetime, item.time', 'ASC')
		   ->setParameter(1, $studentId);

		return $qb->getQuery()->getResult();
	}
	
	public function getAllWithEvalByDefinition($defId)
	{
		$qb = $this->_em->createQueryBuilder();
	 
		$def = EntityUtils::getEntity("PracticeDefinition", $defId);
		$student = EntityUtils::getEntity("StudentLegacy", $studentId);
		
		$qb->select('item')
		   ->from('\Fisdap\Entity\PracticeItem', 'item')
		   ->where('item.practice_definition = ?1')
		   ->andWhere('item.eval_session is not null')
		   ->setParameter(1, $def);
		
		return $qb->getQuery()->getResult();
	}
	
	private function getStudentQuery($student = null){
		$query = "";
		
		if($student){
			$query = "item.student = ?2";
		}else{
                    $query = null;
                }
		
		return $query;
	}
	
	private function getEvaluatorType($evalType = null){
		$query = "";
		
		if($evalType){
			$query = "item.evaluator_type = ?3";
		}else{
                    $query = null;
                }
		
		return $query;
	}

	
	public function getItemsForReport($defId, $studentIds = null, $dateRange = null)
	{
		$qb = $this->_em->createQueryBuilder();

		$qb->select('item')
		   ->from('\Fisdap\Entity\PracticeItem', 'item')
		   ->leftJoin('item.shift', 'shift')
		   ->where('item.practice_definition = ?1');
		   
		if($dateRange){
			
			
			if($dateRange['start_date']){
				$qb->andWhere("shift.start_datetime >= '" . $dateRange['start_date'] . "'");
			}
			
			if($dateRange['end_date']){
				$qb->andWhere("shift.start_datetime <= '" . $dateRange['end_date'] . "'");
			}
		}
		   
		$qb->orderBy('shift.start_datetime, item.time', 'ASC')
		   ->setParameter(1, $defId);
		   
		if($studentIds){
			$qb->andWhere($qb->expr()->in('item.student', $studentIds));
		}
		
		return $qb->getQuery()->getResult();
	}
	
	

	/**
	 * This function returns a set of actual items for the users who belong to the
	 * specified program and certLevel.
	 * 
	 * @param \Fisdap\Entity\ProgramLegacy $program
	 * @param \Fisdap\Entity\CertificationLevel $certLevel
	 * @param array $studentIds Array of users Defaults to null - valid inputs are
	 * either null or an array of User IDs.
	 */
	public function getItems($program, $certLevel=null, $studentIds=null)
	{
		$qb = $this->_em->createQueryBuilder();
		
		$qb->select('
			item.id AS item_id, 
			item.passed, 
			item.time, 
			item.confirmed,
			def.id AS definition_id, 
			def.name AS definition_name, 
			def.peer_goal, 
			def.instructor_goal, 
			def.eureka_window, 
			def.eureka_goal,
			student.id AS student_id,
			student.first_name,
			student.last_name,
			cat.name AS category_name,
			cat.id AS category_id,
			eval_type.name as evaluator_type_name,
			eval_type.id as evaluator_type_id
			')
		->from('\Fisdap\Entity\PracticeItem', 'item')
		->join('item.practice_definition', 'def')
		->join('item.student', 'student')
		->join('def.category', 'cat')
		->join('item.evaluator_type', 'eval_type');
		
		$qb->where('def.program = ?1')
		->setParameter(1, $program);
		
		if($studentIds){
			$qb->andWhere($qb->expr()->in('student.id', $studentIds));
		}
		
		if($certLevel){
			$qb->andWhere('def.certification_level = ?2');
			$qb->setParameter(2, $certLevel);
		}
		
		$qb->andWhere('def.active = 1');
		
		$results = $qb->getQuery()->getResult();
		
		$itemsArray = array();
		
		foreach($results as $res){
			$itemsArray[$res['category_id']][$res['definition_id']][$res['student_id']][$res['item_id']] = $res;
		}
		
		$categoryArray = array();
		$definitionArray = array();
		
		// Get back a listing of definitions for this program
		$definitions = EntityUtils::getRepository('PracticeDefinition')->getProgramDefinitions($program, $certLevel);
		
		foreach($definitions as $def){
			if($def->active){
				$categoryArray[$def->category->id]['category_name'] = $def->category->name;
				$categoryArray[$def->category->id]['definitions'][] = $def->id;
				
				if(!isset($definitionArray[$def->id])){
					$definitionArray[$def->id] = array(
							'definition_name' => $def->name,
							'peer_goal' => $def->peer_goal,
							'instructor_goal' => $def->instructor_goal,
							'eureka_window' => $def->eureka_window,
							'eureka_goal' => $def->eureka_goal,
							'category_id' => $def->category->id
					);
				}
			}
		}
		
		$returnArray = array(
			'category_data' => $categoryArray,
			'definition_data' => $definitionArray,
			'item_data' => $itemsArray
		);
		
		return $returnArray;
	}
	
	

	/**
	 * Calculate whether a eurkea point was met or not
	 * based on an array of lab practice items. The incoming
	 * array's items must be in sequential order (keyed by time,
	 * but watch out for key collisions)
	 *
	 * @param array $attempts An array of 1 or 0 integer values representing a sequence of successes/failures
	 * @param integer $goal The number of successes that must be found within the window to merit eureka success
	 * @param integer $window The number of attempts considred for the goal
	 *
	 * @return array A keyed array with values for 'success' (boolean for met goal or not) and 'max' (highest number of successful attempts in any one window)
	 */
	public function calculateEurekaFromArray($attempts, $goal, $window) {
		
		if (count($attempts) >= $window) {
			// use array_slice to check each window within the dataset
			$max = $success = 0;
			for ($i = 0; $i < (count($attempts) - $window); $i++) {
				$slice = array_slice($attempts, $i, $window);
				$thisSliceSuccess = array_sum($slice);
				if ($thisSliceSuccess > $max) {
					$max = $thisSliceSuccess;
				}
				if ($thisSliceSuccess > $goal) {
					$success = TRUE;	
				}
			}
			return array('success' => $success, 'max' => $max);
		}
		else {
			return array('success' => false, 'max' => $success);
		}
	}
	
	
	// $attempts needs keys! the keys are the item ids
	// returns the item id that causes the eureka point or false if it never reaches eureka
	public function getEurekaPoint($attempts, $goal, $window) {
		$movingWindow = array();
		
		foreach($attempts as $itemId => $success){
			if(count($movingWindow) == $window){
				array_shift($movingWindow);
			}
			
			array_push($movingWindow, $success[0]);
			
			if(array_sum($movingWindow) >= $goal){
				return $itemId;
			}
		}
		
		return false;
	}
	
	// $attempts is just an array of 1s and 0s
	// returns true if eureka, false if not
	public function hasEureka($attempts, $goal, $window) {
		$movingWindow = array();
		
		foreach($attempts as $attempt){
			if(count($movingWindow) == $window){
				array_shift($movingWindow);
			}
			
			array_push($movingWindow, $attempt);
			
			if(array_sum($movingWindow) >= $goal){
				return true;
			}
		}
		
		return false;
	}
	
	public function getSuccessRateAfterEureka($attempts, $goal, $window)
	{
		$movingWindow = array();
		$eureka = false;
		
		foreach($attempts as $i => $attempt){
			if(count($movingWindow) == $window){
				array_shift($movingWindow);
			}
			
			array_push($movingWindow, $attempt);
			
			//Remove this attempt
			unset($attempts[$i]);
			
			if(array_sum($movingWindow) >= $goal){
				$eureka = true;
				break;
			}
		}
		
		if ($eureka === false) {
			return false;
		}
		
		//Return the percentage of successful attempts after hitting the eureka point
		return (array_sum($attempts)/count($attempts)) * 100;
	}

	
	/**
	 * Get success counts for items
	 */
	public function getItemPassCounts($studentId)
	{
		$qb = $this->_em->createQueryBuilder();

		$qb->select('count(item.id) as pass_count, def.name')
		   ->from('\Fisdap\Entity\PracticeItem', 'item')
		   ->leftJoin('item.practice_definition', 'def')
		   ->andWhere('item.student = ?1')
		   ->andWhere('item.passed = 1')
		   ->andWhere('(item.evaluator_type = 1 AND item.confirmed = 1) OR item.evaluator_type = 2')
		   ->groupBy('def.name')
		   ->setParameter(1, $studentId);

		return $qb->getQuery()->getResult();
	}

	/**
	 * Get a list of practice items (evaluated by an instructor) for a given student at given shift types (i.e. field, clinical, lab).
	 * Can also be filtered further by a specific Instructor evaluator.
     *
     * Note: If you pass in an empty array for $shiftTypes, you will not receive any items back
	 *
	 * @param int      $studentId
	 * @param array    $shiftTypes
	 * @param int|null $instructorId
	 *
	 * @return array
	 */
    public function getItemsByStudentEvaluatorShiftTypes($studentId, array $shiftTypes, $instructorId = null){
        $qb = $this->_em->createQueryBuilder();

        $qb->select('item.id, def.name, item.time, item.passed, item.confirmed, item.evaluator_id, subject.name as subject_name, subject.type as subject_type, shift.start_datetime, shift.hours, site.name as site_name, base.name as base_name, shift.id as shift_id, shift.type as shift_type')
            ->from('\Fisdap\Entity\PracticeItem', 'item')
            ->leftJoin('item.shift', 'shift')
            ->leftJoin('item.practice_definition', 'def')
            ->leftJoin('item.patient_type', 'subject')
            ->leftJoin('shift.site', 'site')
            ->leftJoin('shift.base', 'base')
            ->where('def.active = 1')
            ->andWhere('item.student = ?1')
            ->andWhere($qb->expr()->in('shift.type', $shiftTypes))
            ->andWhere('item.evaluator_type = 1')
            ->addOrderBy('shift.start_datetime', 'DESC')
            ->addOrderBy('item.time', 'DESC')
            ->setParameter(1, $studentId);

        if ($instructorId) {
            $qb->andWhere('item.evaluator_id = ?2')
                ->setParameter(2, $instructorId);
        }

        $results = $qb->getQuery()->getResult();

        $items = [];
        foreach($results as $result) {
            $inst = EntityUtils::getEntity('InstructorLegacy', $result['evaluator_id']);
            if ($inst instanceof InstructorLegacy) {
                $result['evaluator'] = $inst->user->getName();
            } else {
                $result['evaluator'] = 'N/A';
            }

//            $shiftString = $result['start_datetime']->format('M j, Y | Hi') . " (" . $result['hours'] . "hrs) " . $result['site_name'] . ": " . $result['base_name'];
            $items[$result['shift_id']][] = $result;
        }

        return $items;
    }
}
