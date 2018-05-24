<?php namespace Fisdap\Data\ClassSection;

use Fisdap\Data\Repository\DoctrineRepository;


/**
 * Class DoctrineClassSectionLegacyRepository
 *
 * @package Fisdap\Data\ClassSection
 */
class DoctrineClassSectionLegacyRepository extends DoctrineRepository implements ClassSectionLegacyRepository
{
	/**
	 * @inheritdoc
	 */
	public function getAssociationCountByInstructor($instructor_id, $section_id)
	{
		$qb = $this->_em->createQueryBuilder();
		
		$qb->select('count(c.id)')
		   ->from('\Fisdap\Entity\ClassSectionInstructorLegacy', 'c')
		   ->where('c.instructor = ?1')
		   ->andWhere('c.section = ?2')
		   ->setParameter(1, $instructor_id)
		   ->setParameter(2, $section_id);
		
		$result = $qb->getQuery()->getSingleResult();
		return array_pop($result);
	}


	/**
	 * @inheritdoc
	 */
    public function getAssociationCountByStudent($student_id, $section_id)
	{
		$qb = $this->_em->createQueryBuilder();
		
		$qb->select('count(c.id)')
		   ->from('\Fisdap\Entity\ClassSectionStudentLegacy', 'c')
		   ->where('c.student = ?1')
		   ->andWhere('c.section = ?2')
		   ->setParameter(1, $student_id)
		   ->setParameter(2, $section_id);
		
		$result = $qb->getQuery()->getSingleResult();
		return array_pop($result);
	}


	/**
	 * @inheritdoc
	 */
    public function getAssociationCountByTa($student_id, $section_id)
	{
		$qb = $this->_em->createQueryBuilder();
		
		$qb->select('count(c.id)')
		   ->from('\Fisdap\Entity\ClassSectionTaLegacy', 'c')
		   ->where('c.ta_student = ?1')
		   ->andWhere('c.section = ?2')
		   ->setParameter(1, $student_id)
		   ->setParameter(2, $section_id);
		
		$result = $qb->getQuery()->getSingleResult();
		return array_pop($result);
	}


	/**
	 * @inheritdoc
	 */
	public function getUniqueYears($programId)
	{
		$qb = $this->_em->createQuery("SELECT DISTINCT c.year FROM \Fisdap\Entity\ClassSectionLegacy c WHERE c.program = ?1 ORDER BY c.year DESC");
		$qb->setParameter(1, $programId);
		
		$result = $qb->getResult();
		
		$returnArray = array("all" => "All Years");
		
		foreach($result as $r){
			$returnArray[$r['year']] = $r['year'];
		}
		
		return $returnArray;
	}


	/**
	 * @inheritdoc
	 */
	public function getNamesByProgram($programId, $year = null)
	{
		$qb = $this->_em->createQueryBuilder();
		
		$qb->select('c.id, c.name, c.year')
		   ->from('\Fisdap\Entity\ClassSectionLegacy', 'c')
		   ->where('c.program = ?1')
		   ->setParameter(1, $programId);
		
		$returnArray = array("Any Section");
		
		if($year > 0){
			$returnArray = array("Any " . $year . " Section");
			$qb->andWhere('c.year = ?2');
			$qb->setParameter(2, $year);
		}
		
		$qb->orderBy('c.year, c.name', 'ASC');
		
		$result = $qb->getQuery()->getResult();
		
		foreach($result as $r){
			$returnArray[$r['id']] = $r['year'] . " - " . $r['name'];
		}
		
		return $returnArray;
	}


	/**
	 * @inheritdoc
	 */
	public function getProgramGroups($programId, $active=null, $studentId=null, $optimized=false, $just_ids=false)
	{
		
		$qb = $this->_em->createQueryBuilder();
		
		if($optimized){
			$selectPartials  = 'partial c.{id,name}, partial sc.{id}';
			$qb->select($selectPartials);
		}
		else {
			$qb->select('c, sc');
		}
		
		$qb->from('\Fisdap\Entity\ClassSectionLegacy', 'c')
			->leftJoin('c.section_student_associations', 'sc')
			->where('c.program = ?1')
			->setParameter(1, $programId);
		
		if($studentId != null){
			$qb->andWhere('sc.student = ?2')
			->setParameter(2, $studentId);
		}
		
		if($active === true){
			$qb->andWhere('c.start_date < CURRENT_TIMESTAMP() AND c.end_date > CURRENT_TIMESTAMP()');
		}elseif($active === false){
			$qb->andWhere('c.start_date > CURRENT_TIMESTAMP() OR c.end_date < CURRENT_TIMESTAMP()');
		}
		
		$qb->orderBy("c.name");
		
		$results =  ($optimized) ? $qb->getQuery()->getArrayResult() : $qb->getQuery()->getResult();
		
		if($just_ids){
			$groups_ids = array();
			foreach($results as $group){
				$groups_ids[] = $group['id'];
			}
			$results = $groups_ids;
		}

		
		return $results;

	}


	/**
	 * @inheritdoc
	 */
	public function getFormOptions($programId, $active=null)
	{
		
		$form_options = array();
		
		if(is_null($active)){
			
			// they don't care about active, so return them all but organize them
			$active_groups = $this->getProgramGroups($programId, true, null, true);
			$inactive_groups = $this->getProgramGroups($programId, false, null, true);
			
			if(count($active_groups) > 0){
				$form_options['Active'] = array();
				foreach($active_groups as $group) {$form_options['Active'][$group['id']] = $group['name'];}
			}
			
			if(count($inactive_groups) > 0){
				$form_options['Inactive'] = array();
				foreach($inactive_groups as $group) {$form_options['Inactive'][$group['id']] = $group['name'];}
			}
			
		}
		else {
			
			$groups = $this->getProgramGroups($programId, $active, null, true);
			foreach($groups as $group) {$form_options[$group['id']] = $group['name'];}
			
		}
		
		
		return $form_options;
	}
}