<?php namespace Fisdap\Data\ScheduledTest;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;


/**
 * Class DoctrineScheduledTestsLegacyRepository
 *
 * @package Fisdap\Data\ScheduledTest
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineScheduledTestsLegacyRepository extends DoctrineRepository implements ScheduledTestsLegacyRepository
{
	public function getScheduledTestsByStudentAndDate($test_id, $student, $date)
	{
		$qb = $this->_em->createQueryBuilder();
		
		$qb->setParameter(1, $test_id);
		$qb->setParameter(2, $student->program->id);
                $qb->setParameter(3, $student->id);
                $qb->setParameter(4, $date);
		$qb->select('t')
		   ->from('\Fisdap\Entity\ScheduledTestsLegacy', 't')
		   ->innerJoin('t.students', 'stu')
		   ->where($qb->expr()->andx(
                        $qb->expr()->orx(
                            $qb->expr()->andx(
                                $qb->expr()->eq('t.start_date', '?4'),
                                $qb->expr()->eq('t.end_date', '0000-00-00')
                            ),
                            $qb->expr()->andx(
                                $qb->expr()->lte('t.start_date', '?4'),
                                $qb->expr()->gte('t.end_date', '?4')
                            )
                        ),
                        $qb->expr()->eq('t.active', '1'),
                        $qb->expr()->eq('t.test', '?1'),
                        $qb->expr()->eq('t.program_id', '?2'),
                        $qb->expr()->eq('stu.id', '?3')
                    ));
		   
		return $qb->getQuery()->getResult();
	}
	
	/**
	 * This function returns a listing of filtered tests.
	 *
	 * @param array $options Contains various options for filterering.  All are optional.  Valid indices:
	 * 		start_date   - String in MM/DD/YYYY format
	 * 		end_date     - String in MM/DD/YYYY format
	 * 		test_id      - ID of the test type to filter by
	 * 		contact_name - String containing the name of the contact to filter by 
	 */
	public function getFilteredTests($options){
		$qb = $this->_em->createQueryBuilder();
		
		// Needed to add "t" (MoodleTestData) as an explicit select here because this function, used on the schedule page,
		// in conjunction with getUniqueTests() was causing data to be pulled up incorrectly. Don't know why that was happening.
		$qb->select('st, t')
			->from('\Fisdap\Entity\ScheduledTestsLegacy', 'st')
			->innerJoin('st.test', 't')
			->where('st.program_id = ?1')
			->andWhere('st.active = 1');
		
		$qb->setParameter(1, \Fisdap\Entity\User::getLoggedInUser()->getProgramId());
		
		$usedParams = 1;
		
		// add start date clause, if applicable
		$startDatePresent = (array_key_exists('start_date', $options) && $options['start_date'] != '');
		if($startDatePresent){
			$cleanStartDate = date('Y-m-d', strtotime($options['start_date']));
			$startParamId1 = ++$usedParams;
				
			$qb->andWhere("st.start_date >= ?{$startParamId1}");
			$qb->setParameter($startParamId1, $cleanStartDate);
		}
			
		// add end date clause, if applicable
		$endDatePresent = (array_key_exists('end_date', $options) && $options['end_date'] != '');
		if($endDatePresent){
			$cleanEndDate = date('Y-m-d', strtotime($options['end_date']));
			$endParamId1 = ++$usedParams;
			
			$qb->andWhere("st.end_date <= ?{$endParamId1}");
			$qb->setParameter($endParamId1, $cleanEndDate);
		}
		
		if(array_key_exists('test_id', $options) && $options['test_id'] != ''){
			$testParamId = ++$usedParams;
			$qb->andWhere("st.test = ?{$testParamId}");
			$qb->setParameter($testParamId, $options['test_id']);
		}
		
		if(array_key_exists('contact_name', $options) && $options['contact_name'] != ''){
			$contactParamId = ++$usedParams;
			$qb->andWhere("st.contact_name = ?{$contactParamId}");
			$qb->setParameter($contactParamId, $options['contact_name']);
		}
		
		$qb->orderBy('st.start_date', 'DESC');
		
		$results = $qb->getQuery()->getResult();
		
		return $results;
	}
	
public function getUniqueContactNames(){
		$qb = $this->_em->createQueryBuilder();
		
		// Joining instructors so that Doctrine doesn't lazy load them for each
		// model (one-to-ones get lazy loaded).
		$qb->select('st.contact_name')
		->from('\Fisdap\Entity\ScheduledTestsLegacy', 'st')
		->where('st.program_id = ?1')
		->andWhere('st.active = 1')
		->groupBy('st.contact_name')
		->orderBy('st.contact_name');
		
		$qb->setParameter(1, \Fisdap\Entity\User::getLoggedInUser()->getProgramId());
		
		$results = $qb->getQuery()->getResult();
		
		$returnData = array('' => '');
		
		foreach($results as $r){
			$returnData[$r['contact_name']] = $r['contact_name'];
		}
		
		return $returnData;
	}
	
	/*
	 * Returns a list of the moodle tests that have been scheduled
	 * for students at least once in this program.
	 *
	 * @param string $returnType Either 'array' to return a flat array or 'entities' to return the MoodleTestDataLegacy entities
	 */
	public function getUniqueTests($returnType = 'array'){
		$qb = $this->_em->createQueryBuilder();
		
		$qb->select('st, t')
		->from('\Fisdap\Entity\ScheduledTestsLegacy', 'st')
		->innerJoin('st.test', 't')
		->where('st.program_id = ?1')
		->andWhere('st.active = 1')
		->groupBy('t.id')
		->orderBy('t.test_name');
	
		$qb->setParameter(1, \Fisdap\Entity\User::getLoggedInUser()->getProgramId());
	
		$results = $qb->getQuery()->getResult();
		
		if ($returnType == 'array') {
			$returnData = array('' => '');
			foreach($results as $r){
				$returnData[$r->test->moodle_quiz_id] = $r->test->test_name;
			}
		} else {
			$returnData = array();
			foreach($results as $r) {
				$returnData[$r->test->moodle_quiz_id] = $r->test;
			}
		}
		
		return $returnData;
	}
}
