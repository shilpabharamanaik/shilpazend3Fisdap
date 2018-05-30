<?php
/*	*	*	*	*	*	*	*	*	*
 *
 *	Copyright (C) 1996-2011.  This is an unpublished work of
 *			Headwaters Software, Inc.
 *				ALL RIGHTS RESERVED
 *	This program is a trade secret of Headwaters Software, Inc.
 *	and it is not to be copied, distributed, reproduced, published,
 *	or adapted without prior authorization
 *	of Headwaters Software, Inc.
 *
 * *	*	*	*	*	*	*	*	*	*/

/**
 * Goals Customization Form
 *
 * To edit goal set, specify goalSet in options (arguments)
 *
 * For new goal set:
 * 	- don't specify goalSet, this will load default goal set
 * 	- specify provide goalSet as you would for edit,
 * 		then set option 'newGoalSet' => true
 *
 * @author Maciej
 */
class Reports_Form_CustomizeGoals extends Fisdap_Form_Base
{
    protected $programId;
    
    protected $goalSetId;
    protected $defaultGoalSetId;
    protected $sourceGoalSetId;
    
    /**
     *	Where goal set values come from
     */
    protected $goalSet;
    protected $referer;
    
    protected $isNewGoalSet = false;
    
    protected $categories;
    
    // goal elements which go to view
    public $goals = array();
    public $interviewExamLead = array();
    
    // are checkboxes enabled for a category?
    //	values for: interview, exam, team_lead in that order for each category
    protected static $categoryCheckboxes = array(
        'Skills' => array(
            false, false, false
        ),
        'Ages' => array(
            true, true, true
        ),
        'Impressions' => array(
            true, true, true
        ),
        'Complaints' => array(
            true, true, true
        ),
        'Team Lead' => array(
            true, true, false
        ),
        'Ages and Impressions' => array(
            true, true, true
        ),
        'Impressions and Complaints' => array(
            true, true, true
        )
        
    );
    
    const INTERVIEW = 0;
    const EXAM = 1;
    const TEAM_LEAD = 2;
    
    protected static $checkboxes = array(
        self::INTERVIEW => array(
            'label' => 'patient interview',
            'name' => 'interview',
        ),
        self::EXAM => array(
            'label' => 'patient exam',
            'name' => 'exam',
        ),
        self::TEAM_LEAD => array(
            'label' => 'team lead',
            'name' => 'team_lead',
        )
    );

    public $headings = array(
        'Title' => array(
            'Goals' => 'Give this goal set a descriptive title that your students will recognize.',
            ),
        'Skills' => array(
            'Goals' => 'Students must successfully perform:',
            ),
        'Ages' => array(
            'Goals' => 'patients',
            ),
        'Impressions' => array(
            'Goals' => 'patients',
            ),
        'Complaints' => array(
            'Goals' => 'patients',
            ),
        'Team' => array(
            ),
        'Evals' => array(
            'Goals' => 'Students must pass the following skill sheets and evaluations:',
        )
    );
    
    public function __construct($defaultGoalSetId = 1, $options = null)
    {
        $this->defaultGoalSetId = $defaultGoalSetId;
        parent::__construct($options);
    }
    
    /**
     *	@param mixed $goalSet: goalSet instance or goalSetId
     */
    public function setReferer($referer)
    {
        if ($referer) {
            $this->referer = $referer;
        } else {
            $this->referer = "/reports";
        }
    }
    
    public function setGoalSet($goalSet)
    {
        if (is_scalar($goalSet)) {
            $goalSet = \Fisdap\EntityUtils::getEntity('GoalSet', $goalSet);
        }
        
        $this->goalSet = $goalSet;
    }
    
    /**
     *	@param boolean $isNewGoalSet
     *
     *	If set ids in $this->goalSet need to be reset
     *
     *	Details: This flag changes what $this->goalSet is, if
     *		true: 	is data to be used for values, ids need not to be used
     *		false:	actual data, ids are to be kept
     *
     */
    public function setNewGoalSet($isIt)
    {
        if (is_null($isIt)) {
            $isIt = true;
        }
        
        $this->isNewGoalSet = (bool) $isIt;
    }
    
    protected function goalActivateMask($fieldName, $max)
    {
        $this->_view->jQuery()->addOnLoad("$('#{$fieldName}').mask(\"?$max\",{placeholder:\" \"})");
    }
    
    public function init()
    {
        if (!isset($this->_view)) {
            $this->_view = $this->getView();
        }
        $this->addJsFile("/js/jquery.maskedinput-1.3.js");
        $this->addJsFile("/js/library/Reports/Form/customize-goals.js");
        
        // Dojo-enable the form:
        Zend_Dojo::enableForm($this);

        $this->setDecorators(array(
            'FormErrors',
            //'PrepareElements',
            array('ViewScript', array('viewScript' => "goal/customize-goals-form.phtml")),
            'Form',
        ));
        
        // goal set
        if (is_null($this->goalSet)) {
            //$this->goalSet = \Fisdap\EntityUtils::getRepository('Goal')->getNewGoalSet($this->programId);	//getEntity('GoalSet');
            $this->goalSet = \Fisdap\EntityUtils::getRepository('Goal')->getGoalsForGoalSet($this->defaultGoalSetId);
            //$this->goalSet->name = "";
            //($this->goalSet->name) ? $this->goalSet->name . ' (modified)' : 'New Goal Set';
            $this->setNewGoalSet(true);
        }
        
        // certification level
        $defaultGoalSet = new Zend_Form_Element_Hidden('defaultGoalSetId');
        $defaultGoalSet->setDecorators(self::$hiddenElementDecorators);
        $defaultGoalSet->setValue($this->isNewGoalSet ? $this->defaultGoalSetId : $this->goalSet->goalset_template->id);
        $this->addElement($defaultGoalSet);
        
        // program id from goal set (first) or by user (second)
        if (!$this->isNewGoalSet) {
            $this->programId = $this->goalSet->program->id;
        }
        if (empty($this->programId)) {
            $user = \Fisdap\Entity\User::getLoggedInUser();
            $this->programId  = $user->getProgramId();
        }
        
        $this->goalSetId = ($this->isNewGoalSet) ? '' : $this->goalSet->id;
        $this->sourceGoalSetId = $this->goalSet->id;
        
        $goalSetId = new Zend_Form_Element_Hidden('id');
        $goalSetId->setOptions(array(
            'value' => $this->goalSetId,//			'decorators' => array('ViewHelper'),
        ));
        
        $referringUrl = new Zend_Form_Element_Hidden('referring_url');
        $referringUrl->setOptions(array('value' => $this->referer));
        
        $sourceGoalSetId = new Zend_Form_Element_Hidden('program_id');
        $sourceGoalSetId->setOptions(array(
            'value' => $this->programId,
        ));
        
        $goalSetName = new Zend_Form_Element_Text('name');
        $goalSetName->setOptions(array(
            //'value' => $this->goalSet->name,
            'label' => $this->headings['Title']['Goals'],
            'size' => 45,
            'required' => true,
        ));
        
        if (!$this->isNewGoalSet) {
            $goalSetName->setValue($this->goalSet->name);
        }
        
        $goalSetAccountType = new Zend_Form_Element_Select('account_type');
        $goalSetAccountType->setOptions(array(
            'multiOptions' => array(
                'paramedic' => 'Paramedic',
                'emt_b' => 'EMT',
                'emt_i' => 'AEMT',
            ),
            'label' => 'Account Type',
            'value' => $this->goalSet->account_type,
        ));
        
        $defaultGoalSet = new Zend_Form_Element_Checkbox('defaultGoalSet');
        $defaultGoalSet->setLabel("Default Goal Set for this certification")
                       ->setValue($this->goalSet->default_goalset)
                       ->setDecorators(self::$checkboxDecorators);
        
        $this->addElements(array($goalSetId, $referringUrl, $sourceGoalSetId, $goalSetName, $goalSetAccountType, $defaultGoalSet));
        
        $this->categories = $this->goalSet->getCategories();
        
        foreach ($this->categories as $categoryName => $categoryGoals) {
            $this->addCategoryDefsToForm($categoryName, $categoryGoals);
        }
        
        $this->addAgeGroupCustomizationElements();
        
        $this->_view->goalElements = $this->goals;

        $saveButton = new Zend_Form_Element_Button('Submit');
        $saveButton->setOptions(array(
            'decorators' => array(
                'ViewHelper',
                array('HtmlTag', array('tag'=>'div', 'class'=>'floating-button-container green-buttons')),
            ),
        ));
        
        $cancelButton = new Zend_Form_Element_Button('Cancel');		//Zend_Form_Element_Submit
        $cancelButton->setAttrib('id', 'cancel-button')
                     ->setAttrib('auto-redirect', $this->referer)
                     ->setAttrib('class', 'cancel-button')
                     ->setOptions(array(
                        'decorators' => array(
                            'ViewHelper',
                            array('HtmlTag', array('tag'=>'div', 'class'=>'floating-button-container')),
                        ),
        ));
        
        $this->addElements(array($cancelButton, $saveButton));
        //$this->addElements(array($saveButton));
    }
    
    
    protected function addCategoryDefsToForm($categoryName, $categoryGoals)
    {
        $isAgeCategory = $categoryName == 'Ages';
        
        if ($isAgeCategory) {
            $maxlength = 3;
            $max = 999;
        } else {
            $maxlength = 3;
            $max = 999;
        }
        uasort($categoryGoals, array("self", "sortGoalDefs"));
        
        foreach ($categoryGoals as $goalInGoalSetId => $goal) {
            $goalId = ($this->isNewGoalSet) ? '' : $goal->id;
            $name = 'goal_' . $goal->def->id . '_' . $goalId;
            $label = $goal->name;
                
            $element = new Zend_Form_Element_Text($name);
            $element->addValidator('Digits', true)
                                ->setRequired(true)
                                ->addErrorMessage('Please enter a valid number in the '. $label .' field below.')
                                ->setOptions(array(
                'label' => $label,
                'value' => $goal->number_required,
                'size' => 2,
                'maxlength' => $maxlength,
                'constraints' => array(
                    'min' => 0,
                    'max' => $max,
                ),
                'places' => 0,
                'decorators' => self::$checkboxDecorators,
            ));
            
            $d = $element->getDecorator('Label');
            $d->setOptions(array(
                'tag' => null,
                'separator' => ''));
            
            $this->addElement($element);
            
            // handle goal without category
            $i = ($goal->id) ? $goal->id : $name;
            
            $this->goals[$categoryName][$i] = $element;
            
            $this->goalActivateMask($name, $max);
        }
        
        $this->interviewExamLead[$categoryName] = array();
        
        if (is_array(self::$categoryCheckboxes[$categoryName])) {
            foreach (self::$categoryCheckboxes[$categoryName] as $whichCheckbox => $addIt) {
                if ($addIt) {
                    $name = self::$checkboxes[$whichCheckbox]['name'];
                    
                    $checkbox = new Zend_Form_Element_Checkbox($categoryName . '_' . $name);
                    $checkbox->setOptions(array(
                        'label' => self::$checkboxes[$whichCheckbox]['label'],
                        'value' => $goal->$name,
                        'decorators' => self::$checkboxDecorators
                    ));
                    
                    $this->interviewExamLead[$categoryName][$whichCheckbox] = $checkbox;
                    $this->addElement($checkbox);
                }
            }
        }
    }
    
    protected function addAgeGroupCustomizationElements()
    {		// ages
        $ageValues = $this->goalSet->ages->getAllStartAges();
        
        foreach ($ageValues as $ageGroup => $ageStartAge) {
            $name = $ageGroup . '_start_age';
            $labelText = ucfirst(str_replace("_", " ", $ageGroup)) . " starts at ";
            if ($ageGroup == "infant") {
                $labelText .= "(months):";
            } else {
                $labelText .= "(years):";
            }
            $ageGroupElement = new Zend_Form_Element_Text($name);
            $ageGroupElement->setOptions(array(
                'label' => $labelText,
                'value' => $ageStartAge,
                'size' => 2,
                'maxlength' => 2,
                'constraints' => array(
                    'min' => 0,
                    'max' => 99,
                ),
                'places' => 0,
                'decorators' => self::$checkboxDecorators,
                //'addDecorator' => array('DijitElement'),
            ));
            
            $d = $ageGroupElement->getDecorator('Label');
            $d->setOptions(array(
                'tag' => null,
                //'tag' => 'div',
                //'class'=>'goal-label',
                'separator' => ''));
            
            $this->addElement($ageGroupElement);
            $this->goalActivateMask($name, 999);
        }
    }

    
    public function process($post)
    {
        if (!$this->isValid($post)) {
            return;
        }
            
        $values = $this->getValues();
        
        $ret=array(
            'newGoals' => array(),
            'goals' => array(),
            'categories' => array(),
        );
        
        // create categories array removing spaces from category names
        // (workaround for categories having spaces and these spaces being removed by zend when used as form names)
        foreach ($this->categories as $category => $obj) {
            $categories[str_replace(' ', '', $category)] = $category;
        }
        
        /** get:
         *		goals into newGoals and goals arrays
         *		interview/exam/team_lead by category
         */
        foreach ($values as $name => $value) {
            $v = explode('_', $name);
            
            if ($v[0]=='goal') {
                $isGoalNew = empty($v[2]);
                
                if (empty($v[2])) {
                    $ret['newGoals'][$v[1]]['numReq'] = $value;		//new goals
                } else {
                    $ret['goals'][$v[2]]['numReq'] = $value;		// existing goals
                    $ret['goals'][$v[2]]['def'] = $v[1];
                }
            } elseif (isset($categories[$v[0]])) { //in_array($v[0], $this->categories))
                // field is : interview, exam or team_lead (last case required following line)
                $field = (isset($v[2])) ? $v[1].'_'.$v[2] : $v[1];
                
                $ret['categories'][$categories[$v[0]]][$field] = (boolean) $value;
            } else {
                if (($name=='id' || $name=='source_goal_set_id') && empty($value)) {
                    $value = false;
                }
                $ret[$name] = $value;
            }
        }
        
        return $ret;
    }
    
    /**
     *	Expecting values in format returned by $this->process()
     *	@param array $values
     */
    public function save($values)
    {
        $goalSet = \Fisdap\EntityUtils::getEntity('GoalSet', $values['id']);
        $goalSet->name = $values['name'];
        $goalSet->account_type = $values['account_type'];
        $goalSet->program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $this->programId);
        $goalSet->infant_start_age = $values['infant_start_age'];
        $goalSet->toddler_start_age = $values['toddler_start_age'];
        $goalSet->preschooler_start_age = $values['preschooler_start_age'];
        $goalSet->school_age_start_age = $values['school_age_start_age'];
        $goalSet->adolescent_start_age = $values['adolescent_start_age'];
        $goalSet->adult_start_age = $values['adult_start_age'];
        $goalSet->geriatric_start_age = $values['geriatric_start_age'];
        
        $goalSet->default_goalset = $values['defaultGoalSet'];
        $goalSet->goalset_template = $values['defaultGoalSetId'];
        $goalSet->save();
        
        foreach ($values['newGoals'] as $defId => $v) {
            $goal = \Fisdap\EntityUtils::getEntity('Goal');
            
            $def = \Fisdap\EntityUtils::getEntity('GoalDef', $defId);
            if (is_null($def)) {
                throw new Exception('def null for new goal def:'.$defId.' numreq:'.$v['numReq']);
            }
            
            // save interview, exam, team_lead settings
            $category = $def->category;
            if (is_array($values['categories'][$category])) {
                foreach ($values['categories'][$category] as $field => $value) {
                    $goal->$field = $value;
                }
            }

            
            $goal->def = $def;
            $goal->goalSet = $goalSet;
            $goal->number_required = $v['numReq'];
            $goal->save(false);
            //$g[$newGoalId]=$goal;
            //$goalSet->goal->add($goal);
            //var_dump($goal); exit;
        }
        
        foreach ($values['goals'] as $goalId => $v) {
            $goal = \Fisdap\EntityUtils::getEntity('Goal', $goalId);
            $goal->number_required = $v['numReq'];
            
            // save interview, exam, team_lead settings
            $category = $goal->def->category;
            if (is_array($values['categories'][$category])) {
                foreach ($values['categories'][$category] as $field => $value) {
                    $goal->$field = $value;
                }
            }

            $goal->save(false);
        }
        
        $goalSet->name = $values['name'];
        $goalSet->save();
    }
    
    public static function sortGoalDefs($a, $b)
    {
        if ($a->def->display_order == $b->def->display_order) {
            return 0;
        }
        return ($a->def->display_order < $b->def->display_order) ? -1 : 1;
    }
}
