<?php
/**
 * Conversion tool for converting skills tracker data to Fisdap 2.0
 */

class Util_Convert_SkillConverter extends Util_Convert_Converter
{
	
	/**
	 * This function takes a skill and a patient entity and ties all of the 
	 * necessary associations together for the skill.
	 * @param \Fisdap\Entity\Skill $skill Skill entity to create associations on
	 * @param mixed either \Fisdap\Entity\Patient or \Fisdap\Entity\ShiftLegacy entity to get associations from
	 * @param 
	 */
	public function setRelatedFields(&$skill, $model, $type = "patient") {
		if ($type == "shift") {
			$skill['shift_id'] = $model['Shift_id'];
			$skill['student_id'] = $model['Student_id'];
		} else {
			$skill['shift_id'] = $model['shift_id'];
			$skill['student_id'] = $model['student_id'];
			$skill['run_id'] = $model['run_id'];
			$skill['patient_id'] = $model['id'];
		}
	}
	
	/**
	 * This function takes in a subjectType string from the old Fisdap 
	 * SubjectTypeTable and returns a new fisdap2_subject_type entity.
	 * 
	 * @param String $subjectType Old fisdap subject type to parse.
	 */
	public function parseSubjectType($subjectType){
		$orig = $subjectType;
		list($subjectName, $subjectType) = explode(' - ', $subjectType);
		
		$subjectType = strtolower($subjectType);
		if ($subjectType == "cadaver") {
			$subjectType = "dead";
		} else if ($subjectType == "simulator") {
			$subjectType = "sim";
		}
		
		//$dql = "SELECT s FROM Fisdap\Entity\Subject s WHERE s.name = :subjectName AND s.type = :subjectType";
		//
		//$query = $this->em->createQuery($dql);
		//$query->setParameter('subjectName', $subjectName);
		//$query->setParameter('subjectType', $subjectType);
		
		//$result = $query->getSingleResult();
		
		$sql = "SELECT id FROM fisdap2_subject WHERE name = '$subjectName' AND type = '$subjectType'";
		$results = $this->db->query($sql);
		
		if($result = $results->fetch()){
			return $result['id'];
		}else{
			return null;
		}
	}
}
