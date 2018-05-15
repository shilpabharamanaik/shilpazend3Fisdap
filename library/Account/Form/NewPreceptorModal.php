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
class Account_Form_NewPreceptorModal extends Fisdap_Form_BaseJQuery
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
        $this->setAttrib('id', 'preceptorForm');
        

        $first_name = new Zend_Form_Element_Text('preceptor_first');
        $first_name->setLabel('First Name:')
             ->setRequired(true)
             ->addValidator('regex', false, array("/^[-_a-zA-Z\s.]+$/"))
             ->setAttrib('class', 'long precepName')
             ->addErrorMessage("Please provide a valid first name. Names can only contain letters and dashes.");
             
        $last_name = new Zend_Form_Element_Text('preceptor_last');
        $last_name->setLabel('Last Name:')
             ->setRequired(true)
             ->setDescription('(required)')
             ->setAttrib('class', 'long precepName')
             ->addValidator('regex', false, array("/^[-_a-zA-Z\s.]+$/"))
             ->addErrorMessage("Please provide a valid last name. Names can only contain letters and dashes.");
             
        $work_phone = new Zend_Form_Element_Text('preceptor_work');
        $work_phone->setLabel('Work Phone:')
             ->setAttrib('class', 'long precepPhone');
             
        $home_phone = new Zend_Form_Element_Text('preceptor_home');
        $home_phone->setLabel('Home Phone:')
             ->setAttrib('class', 'long precepPhone');
             
        $pager = new Zend_Form_Element_Text('preceptor_pager');
        $pager->setLabel('Pager:')
             ->setAttrib('class', 'long precepPhone');
        
        $email = new Zend_Form_Element_Text('preceptor_email');
        $email->setLabel('Email:')
             ->setAttrib('class', 'long precepName');
            
        $siteId = new Zend_Form_Element_Hidden('preceptor_siteId');
        $siteId->setValue($this->site_id);
        
        $preceptorId = new Zend_Form_Element_Hidden('preceptor_id_input');
        $preceptorId->setValue("noPreceptor");

        
        $this->addElements(array(
            $first_name,
            $last_name,
            $work_phone,
            $home_phone,
            $pager,
            $email,
            $siteId,
            $preceptorId
        ));

        $this->setElementDecorators(self::$gridElementDecorators, array('preceptor_first', 'preceptor_last', 'preceptor_work', 'preceptor_home', 'preceptor_pager', 'preceptor_email'), true);

        $viewscript = newPreceptorModal.phtml;

        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "newPreceptorModal.phtml")),
            'Form',
            array('DialogContainer', array(
                    'id'          	=> 'newPreceptorDialog',
                    'class'         => 'newPreceptorDialog',
                    'jQueryParams' 	=> array(
                    'tabPosition' 	=> 'top',
                    'modal' 		=> true,
                    'autoOpen' 		=> false,
                    'resizable' 	=> false,
                    'width' 		=> 420,
                    'title' 		=> 'Add New Preceptor',
                    'open' 			=> new Zend_Json_Expr("function(event, ui) { $('button').css('color', '#000000'); }"),
                    'buttons' 		=> array(array("text" => "Cancel", "className" => "gray-button", "click" => new Zend_Json_Expr("function() { $(this).dialog('close'); }")),array("text" => "Save", "id" => "save-btn", "class" => "gray-button small", "click" => new Zend_Json_Expr(
                        "function() {
							var saveBtn = $('#newPreceptorDialog').parent().find('.ui-dialog-buttonpane').find('button').hide();
							var throbber =  $(\"<img id='preceptorModalThrobber' src='/images/throbber_small.gif'>\");
							saveBtn.parent().append(throbber);
							$.post(
								'/account/sites/add-new-preceptor',
								$('form#preceptorForm').serialize(),
								
								function(response){
									if (response['optionText']) {
										if(response['newPreceptor']){
											$('#activePreceptors').append(response['optionText']);
											
											var my_options = $('#activePreceptors option');
		
											my_options.sort(function(a,b) {
												if (a.text.toUpperCase() > b.text.toUpperCase()) return 1;
												else if (a.text.toUpperCase() < b.text.toUpperCase()) return -1;
												else return 0
											})
											
											$('#activePreceptors').empty().append( my_options );
										}
										else
										{
										
											var fromInactiveList = $('#inactivePreceptors').find('option:selected').val();
											if(fromInactiveList == null){
												$('#activePreceptors').find('option:selected').remove();
												$('#activePreceptors').append(response['optionText']);
												
												var my_options = $('#activePreceptors option');
		
												my_options.sort(function(a,b) {
													if (a.text.toUpperCase() > b.text.toUpperCase()) return 1;
													else if (a.text.toUpperCase() < b.text.toUpperCase()) return -1;
													else return 0
												})
												
												$('#activePreceptors').empty().append( my_options );
											}
											else {
												$('#inactivePreceptors').find('option:selected').remove();
												$('#inactivePreceptors').append(response['optionText']);
												
												var my_options = $('#inactivePreceptors option');
		
												my_options.sort(function(a,b) {
													if (a.text.toUpperCase() > b.text.toUpperCase()) return 1;
													else if (a.text.toUpperCase() < b.text.toUpperCase()) return -1;
													else return 0
												})
												
												$('#inactivePreceptors').empty().append( my_options );
											}
										}
				
										$('#newPreceptorDialog').dialog('close');
										$('#newPreceptorDialog').parent().find('.ui-dialog-buttonpane').find('button').show();
										saveBtn.parent().find('#preceptorModalThrobber').remove();
	
										
										$('#preceptorForm').val('');
									}
									else {
										htmlErrors = '<div id=\'newPreceptorErrors\' class=\'form-errors alert\'><ul>';
										
										$('label').removeClass('prompt-error');
										
										$.each(response, function(elementId, msgs) {
											$('label[for=' + elementId + ']').addClass('prompt-error');
											$.each(msgs, function(key, msg) {
												htmlErrors += '<li>' + msg + '</li>';
											});
										});
										
										htmlErrors += '</ul></div>';
										
										$('.form-errors').remove();
										$('#newPreceptorDialog form').prepend(htmlErrors);
										
										saveBtn.show();
										saveBtn.parent().find('#preceptorModalThrobber').remove();

									}

									
								}
							)
							
							
							
						}"
                    ))),
                ),
            )),
        ));
    }
    

    public function process($data)
    {
        if ($this->isValid($data)) {
            $user = \Fisdap\Entity\User::getLoggedInUser();
            $programId = $user->getProgramId();
            $program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $programId);
            
            $site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $data['preceptor_siteId']);
            
            $returnArray = array();
            
            
            if ($data['preceptor_id_input'] == "noPreceptor") {
                $preceptor = new \Fisdap\Entity\PreceptorLegacy;
                $returnArray['newPreceptor'] = true;
            } else {
                $preceptor = \Fisdap\EntityUtils::getEntity("PreceptorLegacy", $data['preceptor_id_input']);
                $returnArray['newPreceptor'] = false;
            }
            
            $preceptor->first_name = $data['preceptor_first'];
            $preceptor->last_name = $data['preceptor_last'];
            $preceptor->work_phone = $data['preceptor_work'];
            $preceptor->home_phone = $data['preceptor_home'];
            $preceptor->pager = $data['preceptor_pager'];
            $preceptor->email = $data['preceptor_email'];
            $preceptor->site = $site;
            $preceptor->save();
            $program->addPreceptor($preceptor, true);
            
            
            $returnArray['optionText'] = "<option value='" . $preceptor->id . "'>" . $preceptor->first_name . " " . $preceptor->last_name . "</option>";
            $returnArray['preceptorId'] = $preceptor->id;
            
            return $returnArray;
        } else {
            return $this->getMessages();
        }
    }
}
