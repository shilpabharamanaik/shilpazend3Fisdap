<?php
/**
 * source: http://www.php.net/manual/en/function.fgetcsv.php#57802
 * 
 * => can import a csv file and iterate over the items
 * => allows to specify the separator and the enclosure
 * 
 * Requires at least PHP 4.3
 * 
 * HowTo use example:
	$csvIterator = new CsvIterator('/path/to/file', true, '|', '"');
	while ($csvIterator->next()) {
		print_r($csvIterator->current());
	}
 * 
 * @author MO mortanon at gmail dot com, 14-Oct-2005 08:05
 * @author TK Tobias Kluge, http://enarion.net
 * 
 * @version 1.1 - 2006-04-22 by TK
 * 		- backport to PHP 4
 * 		- added usage of enclosure
 * 		- added support of headers 
 * @version 1.0 - 2005-10-14 by MO
 * 
 */

class Util_CsvIterator 
{
   /**
     * The cvs file array.
     */
   var $file = null;
   
   /**
  * The number of activation codes available
  */
   var $numberOfActivationCodes = null;


   /**
  * The max amount of rows we're willing to look through to find mroe data
  */
   var $maxAttempts = null;
   
   /**
    * The number of invalid rows found in this file
    */
   var $invalidCount = 0;
   
   /*
    * The total number of valid rows found in this file
    */
   var $totalValidRows = 0;
   
   /*
    * A collection of configurations associated with this file
    */
   var $configurations = null;
   
   /*
    * the number of serial numbers that are inactive and not distributed
    */
   var $snCount = 0;
   
      /**
     * This is the constructor.It try to open the csv file.
     */
   	public function __construct($csv, $configurations)
	{
      $this->file = $csv;
	  $this->configurations = $configurations;
	  //$this->numberOfActivationCodes = $this->getAvailableSn();
	  $this->maxAttempts = $this->numberOfActivationCodes * 2;
   }
   
   
   
   public function getDistributedCount($serialNumbers)
   {
	  $snCount = 0;
	  foreach($serialNumbers as $sn){
		 $count = false;
		 if($sn->distribution_email){
			$count = true;
		 }
		 if($sn->isActive()){
			// don't count
			$count = false;
		 }
		 
		 if($count){
			$snCount++;
		 }
	  }
	  return $snCount;
   }
   
   public function getAvailableSn($serialNumbers)
   {
	  $snCount = 0;
	  foreach($serialNumbers as $sn){
		 $count = true;
		 if($sn->distribution_email){
			// don't count
			$count = false;
		 }
		 if($sn->isActive()){
			// don't count
			$count = false;
		 }
		 
		 if($count){
			$snCount++;
		 }
	  }
	  return $snCount;
   }
   
   public function getGroup($configNum)
   {
	  $groupCount = 1;
	  for($i = 0; $i <= count($this->file); $i++){
		 // this row begins a new group/configuration
		 if(strlen(strpos($this->file[$i], "---------------")) > 0){
			if($groupCount-1 >= 1){
			   // end the previous group
			   $groupRowLocations[$groupCount-1]["end"] = $i;
			}
			// start our new group
			$groupRowLocations[$groupCount] = array(
				  "begin" => $i,
				  "end" => ""
			   );
			$groupCount++;
		 }
	  }

	  // set the last group's end to the number of rows in the file
	  $groupRowLocations[$groupCount-1]["end"] = count($this->file);
	  
	  // now slice the file array and return the group we're looking for
	  if($configNum < $groupCount){
		 $length = $groupRowLocations[$configNum]["end"]-$groupRowLocations[$configNum]["begin"];
		 return array_slice($this->file, $groupRowLocations[$configNum]["begin"]+1, $length-1);
	  }
	  else {
		 return null;
	  }
   }

   public function getSerialNumbers($config){
	  return $config->serial_numbers;
   }
   
   
   // gets all of those and puts them into an array of configurations/students
   public function getRows($configNum)
   {
	  $studentsArrayPos = 0;
	  $blankCount = 0;
	  
	  $configCount = 1;

	  foreach($this->configurations as $config){
		 if($configCount == $configNum){
			$groupConfig = $config;
		 }
		 $configCount++;
	  }
	  
	  $serialNumbers = $this->getSerialNumbers($groupConfig);
	  
	  //$studentCounter = $this->getAvailableSn($serialNumbers)+$this->getDistributedCount($serialNumbers);
	  
	  $group = $this->getGroup($configNum);
	  
	  for($i = 1; $i <= count($group); $i++){
		  $blank = 1;
		  
		  // step through each of this row's values and determine if there is anything
		  foreach(explode(",", $group[$i], 10) as $value){
			if(trim($value) != ""){
			  $blank = 0;
			}
		  }
		  if($blank != 1){
			  // we only care about the first 10 fields
			  $students[] = explode(",", $group[$i], 10);
			  // fix the year (it may have leading commas/spaces)
			  //$students[$studentsArrayPos][6] = substr($students[$studentsArrayPos][6], 0, 4);
			  $studentsArrayPos++;
		  }
		  else {
			  $blankCount++;
			  if($studentCounter < $this->maxAttempts){
				  // we have room for another student, increase the number of students
				  $studentCounter++;
			  }
		  }
	  }
	  
	  $this->invalidCount = $blankCount;
	  $this->totalValidRows =  count($this->file) - $offset;

	  return $students;
   }
   
   public function getNumberOfStudentsNotAdded()
   {
	  $numberOfStudentsFound = $this->totalValidRows - $this->invalidCount;
	  $availableSpots = $this->numberOfActivationCodes - $numberOfStudentsFound;
	  
	  if($availableSpots < 0){
		 return $numberOfStudentsFound - $this->numberOfActivationCodes;
	  }
	  else {
		 return 0;
	  }
   }

   
}
?>