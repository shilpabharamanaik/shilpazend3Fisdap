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
 * This produces a modal form for customizing a program's narrative
 */

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class SkillsTracker_Form_CustomNarrativeModal extends Fisdap_Form_BaseJQuery
{
    /**
     * @var
     */
    public $sections;

    /**
     *
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();
        $this->addJsFile("/js/library/SkillsTracker/Form/custom-narrative-modal.js");
        
        // get any existing narrative sections
        $program_id = \Fisdap\Entity\User::getLoggedInUser()->getProgramId();
        $sections = \Fisdap\EntityUtils::getRepository('NarrativeSectionDefinition')->getNarrativeSectionsByProgram($program_id, true);
        if (!$sections) {
            $sections = array();
            $new_section = new \Fisdap\Entity\NarrativeSectionDefinition();
            $new_section->program_id = $program_id;
            $new_section->section_order = 1;
            $new_section->name = "Narrative";
            $new_section->size = 32;
            $new_section->seeded = false;
            $new_section->section_order = 1;
            $new_section->active = true;
            $sections['new_1'] = $new_section;
        }
        
        $this->sections = $sections;
        $order_count = 1;
        foreach ($sections as $id => $section) {
            $section_name = new Zend_Form_Element_Text($id.'_name');
            $section_name->removeDecorator('Label');
            $section_name->setAttrib('maxlength', '50');
            
            $size = new Zend_Form_Element_Radio($id.'_size');
            $size->setMultiOptions(array(
                '2' => 'Small',
                '8' => 'Med',
                '32' => 'Large'))
                ->setSeparator('</div><div>');
            $size->removeDecorator('Label');
            $size->setDecorators(array(
                'ViewHelper',
                'Errors',
                array('HtmlTag', array('tag' => 'div')),
            ));
                
            $seed = new Zend_Form_Element_Checkbox($id.'_seed');
            $seed->removeDecorator('Label');
            $active = new Zend_Form_Element_Hidden($id.'_active');
            $active->removeDecorator('Label');
            $order = new Zend_Form_Element_Hidden($id.'_order');
            $order->removeDecorator('Label');
                
            $this->addElements(array($section_name, $size, $seed, $active, $order));
            $this->setDefaults(array(
                                $id.'_name' => $section->name,
                                $id.'_size' => $section->size,
                                $id.'_seed' => $section->seeded,
                $id.'_active' => $section->active,
                $id.'_order' => $order_count
                        ));
            $order_count++;
        }
        
        $section_ids = new Zend_Form_Element_Hidden('section_ids');
        $section_ids->removeDecorator('Label');
        $section_count = new Zend_Form_Element_Hidden('section_count');
        $section_count->removeDecorator('Label');
        
        $this->addElements(array($section_ids, $section_count));
        $this->setDefaults(array('section_ids' => serialize(array_keys($sections)), 'section_count' => $order_count));
            
        $this->setAttrib('id', 'customNarrativeForm');
        
        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "customNarrativeModal.phtml")),
            'Form',
            array('DialogContainer', array(
                'id'          => 'customNarrativeDialog',
                'class'          => 'customNarrativeDialog',
                'jQueryParams' => array(
                    'tabPosition' => 'top',
                    'modal' => true,
                    'autoOpen' => false,
                    'resizable' => false,
                    'width' => 800,
                    'title' => 'Build a custom narrative format',
                    'open' => new Zend_Json_Expr("function(event, ui) { $('button').css('color', '#000000'); }"),
                    'buttons' => array(array("text" => "Cancel", "className" => "gray-button", "click" => new Zend_Json_Expr(
                        "function() {
							$(this).dialog('close');
						}"
                    )),
                               array("text" => "Save", "id" => "save-btn", "class" => "gray-button small", "click" => new Zend_Json_Expr(
                        "function() {
							var postValues = $('#customNarrativeForm').serialize();
							$('#customNarrativeForm :input').attr('disabled', true);
							var saveBtn = $('#customNarrativeDialog').parent().find('.ui-dialog-buttonpane').find('button').hide();
							$('#section-button').hide();
							var throbber =  $(\"<img id='customNarrativeThrobber' src='/images/throbber_small.gif'>\");
							$('#preview_link').hide();
							
							saveBtn.parent().append(throbber);
							$.post(
								'/skills-tracker/settings/save-custom-narrative-settings',
								postValues,

								
								function (response){
								
									if(response === true){
										window.location = '/skills-tracker/settings';
									}
									else
									{
										htmlErrors = '<div id=\'customNarrativeErrors\' class=\'form-errors alert\'><ul>';
										
										$('label').removeClass('prompt-error');
										
										$.each(response, function(elementId, msgs) {
											$('label[for=' + elementId + ']').addClass('prompt-error');
											$.each(msgs, function(key, msg) {
												htmlErrors += '<li>' + msg + '</li>';
											});
											if(elementId == 'site_type'){
												$('#typeContainer').css('border-color','red');
											}
										});
										
										htmlErrors += '</ul></div>';
										
										$('.form-errors').remove();
										$('#customNarrativeDialog form').prepend(htmlErrors);
										$('#preview_link').show();
										saveBtn.show();
										saveBtn.parent().find('#customNarrativeThrobber').remove();	
									}
								}
							)
							
							
							
						}"
                               ))),
                ),
            )),
        ));
    }
    
    /**
     * Validate the form, if valid, save the narrative sections, if not, return the error msgs
     *
     * @param array $data the POSTed data
     * @return mixed either boolean true, or an array of error messages
     */
    public function process($data)
    {
        if ($this->isValid($data)) {
            $user = \Fisdap\Entity\User::getLoggedInUser();
            $program_id = $user->getProgramId();
            
            $section_ids = unserialize($data['section_ids']);
            
            foreach ($section_ids as $id) {
                if (substr($id, 0, 3) == 'new') {
                    $section = new \Fisdap\Entity\NarrativeSectionDefinition;
                } else {
                    $saved_section = \Fisdap\EntityUtils::getEntity('NarrativeSectionDefinition', $id);
                    
                    // if the user has changed the name, create a new section and
                    // inactivate the old one
                    if ($saved_section->name != $data[$id.'_name']) {
                        $saved_section->active = 0;
                        $saved_section->save();
                        $section = new \Fisdap\Entity\NarrativeSectionDefinition;
                    } else {
                        $section = $saved_section;
                    }
                }
                
                $section->program_id = $program_id;
                $section->name = $data[$id.'_name'];
                $section->size = $data[$id.'_size'];
                $section->seeded = $data[$id.'_seed'];
                $section->active = $data[$id.'_active'];
                $section->section_order = $data[$id.'_order'];
                $section->save();
            }
            
            return true;
        }

        return $this->getMessages();
    }
}
