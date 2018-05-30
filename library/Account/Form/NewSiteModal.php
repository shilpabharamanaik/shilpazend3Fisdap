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
class Account_Form_NewSiteModal extends Fisdap_Form_BaseJQuery
{
    
        /**
     * @var Fisdap\Entity\ProgramLegacy
     */
    public $program;
    
    public static $gridElementDecorators = array(
        'ViewHelper',
        array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => 'grid_3 mostInputs')),
        array('Label', array('tag' => 'div', 'class' => 'grid_2 leftLabels', 'escape' => false)),
        
    );
    
    public static $activeElementDecorators = array(
        'ViewHelper',
        array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => 'grid_3 activateInput')),
        array('Label', array('tag' => 'div', 'class' => 'grid_2 activateLabel', 'escape' => false)),
        
    );
    
    public static $zipElementDecorators = array(
        'ViewHelper',
        array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => 'grid_3 zipInput')),
        array('Label', array('tag' => 'div', 'class' => 'grid_2 zipLabel', 'escape' => false)),
        
    );
    
    public static $typeElementDecorators = array(
        'ViewHelper',
        array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => '')),
        array('Label', array('tag' => 'div', 'class' => 'typeLabel', 'escape' => false)),
    );
        
    public function __construct($options = null)
    {
        $this->program = \Fisdap\Entity\ProgramLegacy::getCurrentProgram();
        parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();
        $this->setAttrib('id', 'siteForm');

        $name = new Zend_Form_Element_Text('site_name');
        $name->setLabel('Site Name:')
             ->setRequired(true)
             ->setDescription('(required)')
             ->addFilter('StripTags')
             ->addFilter('HtmlEntities')
             ->setAttrib('class', 'long name')
             ->addErrorMessage("Please enter a site name.");
             
        $abbrev = new Zend_Form_Element_Text('site_abbrev');
        $abbrev->setLabel('Site Abbreviation:')
             ->setRequired(true)
             ->setDescription('(required)')
             ->addFilter('StripTags')
             ->setAttrib('class', 'tiny abbrev')
             ->addFilter('HtmlEntities')
             ->addErrorMessage("Please enter an abbreviation.");
             
             
        $city = new Zend_Form_Element_Text('site_city');
        $city->setLabel('City:')
             ->setRequired(true)
             ->setAttrib('class', 'long city')
             ->setDescription('(required)')
            ->addErrorMessage("Please enter a city.");

        
        $state = new Fisdap_Form_Element_States('site_state');
        $state->setLabel('State:')
              ->setRequired(true)
              ->setDescription('(required)')
              ->addValidator('NotEmpty', false, array('string'))
                          ->setCountry($this->program->country)
              ->addErrorMessage('Please choose a state.');

            
        $type = new Zend_Form_Element_Radio('site_type');
        $type->setRequired(true)
             ->addErrorMessage("Please select a type.");

        $type->addMultiOptions(array(
                    'field' => 'Field',
                    'clinical' => 'Clinical',
                    'lab' => 'Lab'
                        ));
        
        
        $this->addElements(array(
            $name,
            $abbrev,
            $address,
            $city,
            $state,
            $zip,
            $type
        ));

        $this->setElementDecorators(self::$gridElementDecorators, array('site_name', 'site_abbrev', 'site_city', 'site_state'), true);
        $this->setElementDecorators(self::$zipElementDecorators, array('site_zip'), true);
        $this->setElementDecorators(self::$typeElementDecorators, array('site_type'), true);
        $viewscript = newSiteModal.phtml;

        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "newSiteModal.phtml")),
            'Form',
            array('DialogContainer', array(
                'id'          => 'newSiteDialog',
                'class'          => 'newSiteDialog',
                'jQueryParams' => array(
                    'tabPosition' => 'top',
                    'modal' => true,
                    'autoOpen' => false,
                    'resizable' => false,
                    'width' => 560,
                    'title' => 'Create New Site',
                    'open' => new Zend_Json_Expr("function(event, ui) { $('button').css('color', '#000000'); }"),
                    'buttons' => array(array("text" => "Cancel", "className" => "gray-button", "click" => new Zend_Json_Expr("function() { $(this).dialog('close'); }")),array("text" => "Save", "id" => "save-btn", "class" => "gray-button small", "click" => new Zend_Json_Expr(
                        "function() {
							var saveBtn = $('#newSiteDialog').parent().find('.ui-dialog-buttonpane').find('button').hide();
							var throbber =  $(\"<img id='createSiteThrobber' src='/images/throbber_small.gif'>\");
							saveBtn.parent().append(throbber);
							$.post(
								'/account/sites/save-new-site',
								$('form#siteForm').serialize(),

								
								function (response){
								
									if(typeof response == 'number'){
										window.location = '/account/sites/site/siteId/' + response;
									}
									else
									{
										htmlErrors = '<div id=\'newSiteErrors\' class=\'form-errors alert\'><ul>';
										
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
										$('#newSiteDialog form').prepend(htmlErrors);
										
										saveBtn.show();
										saveBtn.parent().find('#createSiteThrobber').remove();	
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
            $site = new \Fisdap\Entity\SiteLegacy;
            
            $user = \Fisdap\Entity\User::getLoggedInUser();
            $programId = $user->getProgramId();
            $program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $programId);

            $site->name = $data['site_name'];
            $site->abbreviation = $data['site_abbrev'];
            $site->city = $data['site_city'];
            $site->state = $data['site_state'];
            $site->type = $data['site_type'];
            $site->owner_program = $program;
            
            $site->save();
            $program->addSite($site, true);
            return $site->id;
        } else {
            return $this->getMessages();
        }
    }
}
