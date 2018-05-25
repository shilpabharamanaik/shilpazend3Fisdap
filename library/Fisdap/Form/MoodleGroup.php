<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * /


/**
 * Form for adding/editing a Moodle Group Mapping
 */

/**
 * @package    Account
 */
class Fisdap_Form_MoodleGroup extends Fisdap_Form_Base
{
	/**
	 * @var \Fisdap\Entity\MoodleGroup
	 */
	public $moodleGroup;
	
	/**
	 * @param int $studentId the id of the student to edit
	 * @param $options mixed additional Zend_Form options
	 */
	public function __construct($moodleGroupId = null, $options = null)
	{
		$this->moodleGroup = \Fisdap\EntityUtils::getEntity("MoodleGroup", $moodleGroupId);
		parent::__construct($options);
	}
	
	/**
	 * @var array the decorators for the form
	 */
	protected static $_formDecorators = array(
		'FormErrors',
		'PrepareElements',
        'FormElements',
		//array('ViewScript', array('viewScript' => "forms/orderIndividualProductsForm.phtml")),
		'Form',
	);
	
	/**
	 * init method that adds all the elements to the form
	 */
	public function init()
	{
		parent::init();
		
		//$this->addJsFile("/js/library/Account/Form/order-individual-products.js");
		$this->setDecorators(self::$_formDecorators);
		
        //Program
        $program = new Fisdap_Form_Element_Program("program");
        $program->setLabel("Program");
        $this->addElement($program);
        
		$moodleGroupId = new Zend_Form_Element_Hidden("moodleGroupId");
		$moodleGroupId->setValue($this->moodleGroup->id);
		$this->addElement($moodleGroupId);
		
        $continue = new \Fisdap_Form_Element_SaveButton("save");
        $continue->setLabel("Add");
        $this->addElement($continue);
        
		$this->setElementDecorators(self::$elementDecorators);
		$this->setElementDecorators(self::$buttonDecorators, array("save"));
        

		//Populate form values
		if ($this->moodleGroup->id) {
			//$defaults = array("orderConfig" => $orderConfigObject->configuration,
			//				  "orderCost" => $orderConfigObject->subtotal_cost);
			//
			//$this->setDefaults($defaults);
		}
	}
	
	/**
	 * Process the submitted POST values and do whatever you need to do
	 *
	 * @param array $post the POSTed values from the user
	 * @return mixed either the values or the form w/errors
	 */
	public function process($post)
	{
		if ($this->isValid($post)) {
            $values = $this->getValues();
            $program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $values['program']);
            $products = \Fisdap\EntityUtils::getRepository("Product")->findByCategory(4);
			$programName = preg_replace('/\s+/', '', $program->name);
			
			$moodleAPI = new \Util_MoodleAPI("transition_course");
			
			$groups = array();
			$groupings = array();
			foreach ($products as $product) {
				$groups[] = array("courseid" => $product->moodle_course_id, "name" => $programName, "description" => $programName);
				$groupings[] = array("courseid" => $product->moodle_course_id, "name" => $programName, "description" => $programName);
			}
			
            
            $groupResult = $moodleAPI->createGroups($groups);
            $groupingResult = $moodleAPI->createGroupings($groupings);
            
			//Create arrays to remember the group IDs and grouping IDs returned from Moodle
			$groupIds = array();
			$groupingIds = array();
			
			//Loop over the results from the create groups API call
			foreach($groupResult as $i => $group) {
				$groupIds[] = $group['id'];
			}
			
			//Loop over the results from the create groupings API call
			foreach($groupingResult as $i => $grouping) {
				$groupingIds[] = $grouping['id'];
			}
			
			//Create array to assign groupings to groups
			$assignments = array();
			
			//Loop over all of the products and group/groupings to create MoodleGroups in Fisdap
			foreach ($products as $i => $product) {
				$moodleGroup = \Fisdap\EntityUtils::getEntity("MoodleGroup");
				$moodleGroup->program = $program;
				$moodleGroup->product = $product;
				$moodleGroup->moodle_group_id = $groupIds[$i];
				$moodleGroup->moodle_grouping_id = $groupingIds[$i];
				$moodleGroup->save(false);
				
				$assignments[] = array("groupid" => $moodleGroup->moodle_group_id, "groupingid" => $moodleGroup->moodle_grouping_id);
			}
			
			//Finally, use the Moodle API to assign the groupings to their groups
			$result = $moodleAPI->assignGroupings($assignments);
			
			\Fisdap\EntityUtils::getEntityManager()->flush();
			return true;
		}
		else {
			return $this->getMessages();
		}
		return $this;
	}
}