<?php

/**
 *	User stuff that can be reused but doesn't belong in controller or entity. 
 */
class Fisdap_User_UserLegacyMisc
{
	const FULL_NAME = 1;
	const GRADUATION_YEAR = 2;
	const GRADUATION_MONTH  = 3;
	const CLASS_ID = 4;
	const CERTIFICATION_LEVEL = 5;
	
	
	const CERT_EMT_VAL = 'emt-b';
	const CERT_EMT = 1;
	
	const CERT_ADV_EMT_VAL = 'emt-i';
	const CERT_ADV_EMT = 2;
	
	const CERT_PARAMEDIC_VAL = 'paramedic';
	const CERT_PARAMEDIC = 3;

	const CERT_INSTRUCTOR_VAL = 'instructor';
	const CERT_INSTRUCTOR = 4;
	
	const CERT_SURG_TECH_VAL = 'surg-tech';
	const CERT_SURG_TECH = 5;
	
	protected static $certs;
	
	/**
	 *	Student picker values needed by frontend
	 *	Not sure where else to put it, but hate putting it in controller
	 *	As of 11-10-18 there are some null users for students.. these won't have
	 *		certification type assignes = not searchable
	 */
	public static function getStudentPickerValuesForProgram($program)
	{
		
		$conn = \Fisdap\EntityUtils::getEntityManager()->getConnection();
		self::staticInit();
		
		// Follow the object means of population...
		$ret->classSections = array();
		$ret->gradYears = array();
		$ret->classSectionYears = array();
		$ret->students = array();
		
		// Populate the students...
		$studentSql = "
			SELECT
				sd.Student_id AS id,
				sd.FirstName AS first_name,
				sd.LastName AS last_name,
				sd.Class_Year AS graduation_year,
				sd.ClassMonth AS graduation_month,
				(SELECT GROUP_CONCAT(Section_id SEPARATOR ',') FROM SectStudents WHERE Student_id = sd.Student_id) AS class_sections,
				sn.AccountType AS account_type
			FROM
				StudentData sd 
				LEFT JOIN SerialNumbers sn ON sn.Student_id = sd.Student_id
			WHERE
				sd.Program_id = {$program->id}
				AND sd.username != 'NotActiveYet'
			ORDER BY 
				sd.LastName, sd.FirstName
		";
		
		$studentResultSet = $conn->query($studentSql);
		
		foreach($studentResultSet as $row){
			if(strlen($row['class_sections']) > 0){
				$classSectionsArray = explode(',', $row['class_sections']);
			}else{
				$classSectionsArray = array();
			}
			
			//Add an underscore to the id so that Chrome won't autosort by ID
			//This symbol will be removed by the GoalController
			$rowId = "_" . $row['id'];
			
			$ret->students[$rowId] = array(
				self::FULL_NAME => $row['last_name'] . ', ' . $row['first_name'],
				self::GRADUATION_YEAR => $row['graduation_year'],
				self::GRADUATION_MONTH => $row['graduation_month'],
				self::CLASS_ID => $classSectionsArray,
				self::CERTIFICATION_LEVEL => 0
			);
			
			if($row['account_type'] != null){
				$ret->students[$rowId][self::CERTIFICATION_LEVEL] = self::$certs[$row['account_type']];
				$ret->students[$rowId][6] = $row['account_type'];
			}
			
			if($row['graduation_year'] != '' && $row['graduation_month'] != ''){
				// I don't know why there's the extra index here.  Just go with it.
				$ret->gradYears[$row['graduation_year']][1] = true;
			}
		}
		
		$ret->gradYearsSorted = array();
		
		foreach ($ret->gradYears as $year => $months) {
			foreach ($months as $month => $val) {
				$ret->gradYearsSorted[$year .'-'.str_pad($month, 2, '0')] = array($year, $month);
			}
		}
		
		// SORTING
		ksort($ret->gradYearsSorted, SORT_DESC);
		
		// Populate the class sections...
		$classSectionSql = "
			SELECT
				cs.Name,
				cs.Type,
				cs.Year,
				cs.Sect_id
			FROM
				StudentData sd 
				INNER JOIN SectStudents ss ON ss.Student_id = sd.Student_id
				INNER JOIN ClassSections cs ON cs.Sect_id = ss.Section_id
			WHERE
				sd.Program_id = {$program->id}
			GROUP BY 
				cs.Sect_id
			ORDER BY 
				cs.Name
		";
		
		$classSectionResultSet = $conn->query($classSectionSql);
		
		foreach($classSectionResultSet as $classSection){
			$ret->classSections[$classSection['Sect_id']] = array(
				'name' => $classSection['Name'],
				'year' => $classSection['Year'],
				'type' => $classSection['Type'],
			);
			
			$ret->classSectionYears[] = $classSection['Year'];
		}
		
		$ret->classSectionYears = array_unique($ret->classSectionYears);
		
		sort($ret->classSectionYears);
		
		return $ret;

/*
		self::staticInit();
		
		$programId = $program->id;
		$students = \Fisdap\EntityUtils::getRepository('UserLegacy')->getAllStudentsByProgram($program);	//, $getProgramOptions);
		
		//$ret = new stdClass();
		$ret->classSections = array();
		$ret->gradYears = array();
		$ret->classSectionYears = array();
		$ret->students = array();
		
		foreach ($students as $student) {
			// student class sections
			$class = array();

			if (!is_null($student->classSectionStudent)) {

				foreach ($student->classSectionStudent as $classSection) {

					// rare case of not matching program ids
					if ($classSection->section->program->id == $programId) {

						$sectionId = $classSection->section->id;
						$class[] = $classSection->section->id;

						if (!isset($ret->classSections[$classSection->section->id])) {

							$ret->classSections[$sectionId] = array(
								'name' => $classSection->section->name,
								'year' => $classSection->section->year,
								'type' => $classSection->section->type,
							);

							if(!in_array($classSection->section->year, $ret->classSectionYears)) {
								$ret->classSectionYears[] = $classSection->section->year;
							}

						}
					}
				}
			}
			
			$gradYear = $student->graduation_year;
			$gradMonth = $student->graduation_month;
			
			$ret->students[$student->id] = array(
				self::FULL_NAME => $student->getLastFirstName(),
				self::GRADUATION_YEAR => $gradYear,
				self::GRADUATION_MONTH => $gradMonth,
				self::CLASS_ID => $class,
				self::CERTIFICATION_LEVEL => 0,
			);
			
			// get certification level
			if (!is_null($student->user->id)) {	// skipping null users
				if (isset($student->user->serial_numbers[0])) {
					$sn = $student->user->serial_numbers[0];
					
					$ret->students[$student->id][self::CERTIFICATION_LEVEL] = self::$certs[$sn->account_type];
					$ret->students[$student->id][6] = $sn->account_type;
				}
				
			}
			
			// make list of month/year graduations
			if (!isset($ret->gradYears[$gradYear]) && !isset($ret->gradYears[$gradYear][$gradMonth])) {
				$ret->gradYears[$gradYear][$gradMonth] = true;
			}
		}
		
		$ret->gradYearsSorted = array();
		
		foreach ($ret->gradYears as $year => $months) {
			foreach ($months as $month => $val) {
				$ret->gradYearsSorted[$year .'-'.str_pad($month, 2, '0')] = array($year, $month);
			}
		}
		
		// SORTING
		ksort($ret->gradYearsSorted, SORT_DESC);
		sort($ret->classSectionYears, SORT_DESC);
		
		// reorder array by column: 'name'
		Util_Array::sortByColumn($ret->classSections, 'name');
		
		return $ret;
*/
	}
	
	public static function staticInit()
	{
		if (is_null(self::$certs)) {
			self::$certs=array(
				self::CERT_EMT_VAL => self::CERT_EMT,
				self::CERT_ADV_EMT_VAL => self::CERT_ADV_EMT,
				self::CERT_PARAMEDIC_VAL => self::CERT_PARAMEDIC,
				self::CERT_INSTRUCTOR_VAL => self::CERT_INSTRUCTOR,
				self::CERT_SURG_TECH_VAL => self::CERT_SURG_TECH,
			);
		}
	}
}

