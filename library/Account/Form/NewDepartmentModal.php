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
 * This produces a modal form for adding/editing Airways
 */

/**
 * @package    Account
 * @subpackage Forms
 */
class Account_Form_NewDepartmentModal extends Fisdap_Form_BaseJQuery
{
    public static $gridElementDecorators = array(
        'ViewHelper',
        array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => 'grid_3 mostInputs')),
        array('Label', array('tag' => 'div', 'class' => 'grid_2 leftLabels', 'escape' => false)),
        
    );
    
    public $site_id;
    
    public function __construct($siteId = null, $options = null)
    {
        $this->site_id = $siteId;
        parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();
        $this->setAttrib('id', 'departmentForm');
        

        $name = new Zend_Form_Element_Text('depart_name');
        $name->setLabel('Department Name:')
             ->setRequired(true)
             ->addFilter('StripTags')
             ->addFilter('HtmlEntities')
             ->setAttrib('class', 'long baseName')
             ->addErrorMessage("Please enter a department name.");
             
        $siteId = new Zend_Form_Element_Hidden('depart_siteId');
        $siteId->setValue($this->site_id);
        
        $baseId = new Zend_Form_Element_Hidden('depart_id_input');
        $baseId->setValue("noBase");

        
        $this->addElements(array(
            $name,
            $siteId,
            $baseId
        ));

        $this->setElementDecorators(self::$gridElementDecorators, array('depart_name'), true);
        
        $viewscript = newDepartmentModal.phtml;

        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "newDepartmentModal.phtml")),
            'Form',
            array('DialogContainer', array(
                'id'          	=> 'newDepartDialog',
                'class'         => 'newDepartDialog',
                'jQueryParams' 	=> array(
                'tabPosition' 	=> 'top',
                'modal' 		=> true,
                'autoOpen' 		=> false,
                'resizable' 	=> false,
                'width' 		=> 500,
                'title' 		=> 'Add New Department',
                'open' 			=> new Zend_Json_Expr("function(event, ui) { $('button').css('color', '#000000'); }"),
                'buttons' 		=> array(array("text" => "Cancel", "className" => "gray-button", "click" => new Zend_Json_Expr("function() { $(this).dialog('close'); }")),array("text" => "Save", "id" => "save-btn", "class" => "gray-button small", "click" => new Zend_Json_Expr(
                        "function() {
							var saveBtn = $('#newDepartDialog').parent().find('.ui-dialog-buttonpane').find('button').hide();
							var throbber =  $(\"<img id='throbber' src='/images/throbber_small.gif'>\");
							saveBtn.parent().append(throbber);
							$.post(
								'/account/sites/add-new-depart',
								$('form#departmentForm').serialize(),
								
								function(response){
								
									if(response['optionText']){
										if(response['newBase']){
											$('#activeBase').append(response['optionText']);
											
											var my_options = $('#activeBase option');
		
											my_options.sort(function(a,b) {
												if (a.text.toUpperCase() > b.text.toUpperCase()) return 1;
												else if (a.text.toUpperCase() < b.text.toUpperCase()) return -1;
												else return 0
											})
											
											$('#activeBase').empty().append( my_options );
										}
										else
										{
										
											var fromInactiveList = $('#inactiveBase').find('option:selected').val();
											if(fromInactiveList == null){
												$('#activeBase').find('option:selected').remove();
												$('#activeBase').append(response['optionText']);
												
												var my_options = $('#activeBase option');
		
												my_options.sort(function(a,b) {
													if (a.text.toUpperCase() > b.text.toUpperCase()) return 1;
													else if (a.text.toUpperCase() < b.text.toUpperCase()) return -1;
													else return 0
												})
												
												$('#activeBase').empty().append( my_options );
											}
											else {
												$('#inactiveBase').find('option:selected').remove();
												$('#inactiveBase').append(response['optionText']);
												
												var my_options = $('#inactiveBase option');
		
												my_options.sort(function(a,b) {
													if (a.text.toUpperCase() > b.text.toUpperCase()) return 1;
													else if (a.text.toUpperCase() < b.text.toUpperCase()) return -1;
													else return 0
												})
												
												$('#inactiveBase').empty().append( my_options );
											}
										}
				
										$('#newDepartDialog').dialog('close');
										$('#newDepartDialog').parent().find('.ui-dialog-buttonpane').find('button').show();
										saveBtn.parent().find('#throbber').remove();
	
										
										$('#baseForm').val('');
									}
									else {
																			
										htmlErrors = '<div id=\'departFormErrors\' class=\'form-errors alert\'><ul>';
										
										$('label').removeClass('prompt-error');
										
										$.each(response, function(elementId, msgs) {
											$('label[for=' + elementId + ']').addClass('prompt-error');
											$.each(msgs, function(key, msg) {
												htmlErrors += '<li>' + msg + '</li>';
											});
										});
										
										htmlErrors += '</ul></div>';
										
										$('.form-errors').remove();
										$('#newDepartDialog form').prepend(htmlErrors);
										
										saveBtn.show();
										saveBtn.parent().find('#throbber').remove();
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
     * Validate the form, if valid, save the Airway, if not, return the error msgs
     *
     * @param array $data the POSTed data
     * @return mixed either boolean true, or an array of error messages
     */
    public function process($data)
    {
        if ($this->isValid($data)) {
            $user = \Fisdap\Entity\User::getLoggedInUser();
            $programId = $user->getProgramId();
            $program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $programId);
            
            $site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $data['depart_siteId']);
            
            $returnArray = array();
    
            if ($data['depart_id_input'] == "noBase") {
                $base = new \Fisdap\Entity\BaseLegacy;
                $base->name = $data['depart_name'];
                $base->site = $site;
                $base->save();
                $program->addBase($base, true);
                
                // if this is an admin program for this site, add this
                // department to the other programs, too
                if ($program->isAdmin($site->id)) {
                    foreach ($site->site_shares as $share) {
                        $share->program->addBase($base, true);
                    }
                }
            
                $returnArray['newBase'] = true;
            } else {
                $base = \Fisdap\EntityUtils::getEntity("BaseLegacy", $data['depart_id_input']);
                $base->name = $data['depart_name'];
                $base->save();
    
                $returnArray['newBase'] = false;
            }
            
            $returnArray['optionText'] = "<option value='" . $base->id . "'>" . $base->name . "</option>";
            $returnArray['baseId'] = $base->id;
            return $returnArray;
        } else {
            return $this->getMessages();
        }
    }
}
