<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * This produces a modal form for setting the group
 * and grad date for a set of serial numbers
 */

/**
 * @package    Account
 * @subpackage Forms
 */
class Account_Form_ProductGradGroupModal extends SkillsTracker_Form_Modal
{
    /**
     * @var DateTime the graduation date for these students
     */
    public $graduationDate;
    
    /**
     * @var array the groups to tie to
     */
    public $groupIds;
    
    /**
     * @param string $graduationDate a string representing the graduation date of these students
     * @param string $groupIds containing a comma separated list of group IDs
     * @param mixed $options additional Zend_Form options
     */
    public function __construct($graduationDate = null, $groupIds = null, $options = null)
    {
        //$this->graduationDate = new DateTime($graduationDate);
        //$this->groupIds = explode(",", $groupIds);
        parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();
        
        //$this->addJsFile("/js/library/SkillsTracker/Form/med-modal.js");

        $graduationDate = new Fisdap_Form_Element_GraduationDate('grad');
        $graduationDate->setLabel("When will these students graduate?")
                       ->setDescription('(optional)');
        $this->addElement($graduationDate);
        
        $groups = new Fisdap_Form_Element_Groups('group');
        $groups->setLabel('Are these students associated with a group?')
               ->setDescription('(optional)');
        $this->addElement($groups);
        
        $counter = new Zend_Form_Element_Hidden("counter");
        $this->addElement($counter);
        
        //$this->addElements(array($graduationDate, $groups));
        
        $this->setElementDecorators(self::$elementDecorators, array(), false);
        //$this->setElementDecorators(self::$checkboxDecorators, array('medPerformed'), true);
        //$this->setElementDecorators(self::$hiddenElementDecorators, array('medId', 'patientId', 'shiftId'), true);
        
        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "forms/gradGroupModal.phtml")),
            'Form',
            array('DialogContainer', array(
                'id'          => 'gradGroupDialog',
                'class'          => 'gradGroupDialog',
                'jQueryParams' => array(
                    'tabPosition' => 'top',
                    'modal' => true,
                    'autoOpen' => false,
                    'resizable' => false,
                    'width' => 800,
                    'title' => 'Graduation Date & Groups',
                    'open' => new Zend_Json_Expr("function(event, ui) { $('button').css('color', '#000000'); }"),
                    'buttons' => array(array("text" => "Cancel", "className" => "gray-button", "click" => new Zend_Json_Expr("function() { $(this).dialog('close'); }")),array("text" => "Save", "id" => "save-btn", "class" => "gray-button small", "click" => new Zend_Json_Expr(
                        "function() {
                            if ($('#group-id').val() > 0) {
                                $('#groupId_' + lastCounter).val($('#group-id').val());
                                $('#group-' + lastCounter).html($('#group-id option:selected').text());
                            }
							
                            year = $('#grad-year').val();
                            month = $('#grad-month').val();
                            
                            if (year > 0 && month > 0) {
                                gradDate = year + '-' + month + '-01';
                                $('#gradDate_' + lastCounter).val(gradDate);
                                $('#graduation-date-' + lastCounter).html('Graduating: ' + month + '/' + year);
                            }
							
                            
                            $(this).dialog('close');
						}"
                    ))),
                ),
            )),
        ));
        
        //if ($this->med->id) {
        //	$this->setDefaults(array(
        //		'medPerformed' => $this->med->performed_by,
        //		'medication' => $this->med->medication->id,
        //		'dose' => $this->med->dose,
        //		'route' => $this->med->route->id,
        //		'patientId' => $this->med->patient->id,
        //		'shiftId' => $this->med->shift->id,
        //		'medId' => $this->med->id,
        //	));
        //} else {
        //
        //}
    }
    
    /**
     * Validate the form, if valid, save the Med, if not, return the error msgs
     *
     * @param array $data the POSTed data
     * @return mixed either boolean true, or an array of error messages
     */
    public function process($data)
    {
        if ($this->isValid($data)) {
            $values = $this->getValues($data);
            
            if ($values['medId']) {
                $med = \Fisdap\EntityUtils::getEntity('Med', $values['medId']);
            } else {
                $med = \Fisdap\EntityUtils::getEntity('Med');
            }
            
            $med->performed_by = $values['medPerformed'];
            $med->medication = $values['medication'];
            $med->dose = $values['dose'];
            $med->route = $values['route'];

            if ($values['patientId']) {
                $patient = \Fisdap\EntityUtils::getEntity('Patient', $values['patientId']);
                $patient->addMed($med);
                $patient->save();
            } elseif ($values['shiftId']) {
                $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $values['shiftId']);
                $shift->addMed($med);
                $shift->save();
            }

            return "Med_" . $med->id;
        }
        
        return $this->getMessages();
    }
}
