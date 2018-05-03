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
class Account_Form_NewBaseModal extends Fisdap_Form_BaseJQuery
{

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
		array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => 'grid_3 baseZipInput')),
        array('Label', array('tag' => 'div', 'class' => 'grid_2 baseZipLabel', 'escape' => false)),

	);

	public static $typeElementDecorators = array(
		'ViewHelper',
		array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => '')),
        array('Label', array('tag' => 'div', 'class' => 'typeLabel', 'escape' => false)),
	);

	public $site_id;

	public $program;

	public function __construct($siteId = null, $options = null)
	{
		$this->site_id = $siteId;
		$this->program = \Fisdap\Entity\ProgramLegacy::getCurrentProgram();
		parent::__construct($options);
	}

	public function init()
	{

        parent::init();
		$this->setAttrib('id', 'baseForm');


	$site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $data['base_siteId']);
	$token = $site->name;

       $name = new Zend_Form_Element_Text('base_name');
		$name->setLabel('Name:')
			 //->addValidator('Identical', true, array('token' => $token))
			 ->setRequired(true)
			 ->setAttrib('class', 'long baseName')
			 ->addErrorMessage("Please enter a name. The base name cannot be the same as the name of the site.");

		$address = new Zend_Form_Element_Text('base_address');
		$address->setLabel('Address:')
			 ->setAttrib('class', 'long baseCity');

		$city = new Zend_Form_Element_Text('base_city');
		$city->setLabel('City:')
			 ->setAttrib('class', 'long baseCity');

		$state = new Fisdap_Form_Element_States('base_state');
		$state->setLabel('State:')
			  ->setCountry($this->program->country);

		$zip = new Zend_Form_Element_Text('base_zip');
		$zip->setLabel('Zip:')
			->addValidator('LessThan', true, array('max' => '99999'))
			->addErrorMessage('Please choose a valid zip code.');



		$siteId = new Zend_Form_Element_Hidden('base_siteId');
		$siteId->setValue($this->site_id);

		$baseId = new Zend_Form_Element_Hidden('base_id_input');
		$baseId->setValue("noBase");


		$this->addElements(array(
			$name,
			$address,
			$city,
			$state,
			$zip,
			$siteId,
			$baseId,
			$address
		));

		$this->setElementDecorators(self::$gridElementDecorators, array('base_name', 'base_city', 'base_address', 'base_state'), true);
		$this->setElementDecorators(self::$zipElementDecorators, array('base_zip'), true);

		$viewscript = newBaseModal.phtml;

		$this->setDecorators(array(
			'PrepareElements',
			array('ViewScript', array('viewScript' => "newBaseModal.phtml")),
			'Form',
			array('DialogContainer', array(
				'id'          => 'newBaseDialog',
				'class'          => 'newBaseDialog',
				'jQueryParams' => array(
					'tabPosition' => 'top',
					'modal' => true,
					'autoOpen' => false,
					'resizable' => false,
					'width' => 550,
					'title' => 'Add New Base',
					'open' => new Zend_Json_Expr("function(event, ui) { $('button').css('color', '#000000'); }"),
					'buttons' => array(array("text" => "Cancel", "className" => "gray-button", "click" => new Zend_Json_Expr("function() { $(this).dialog('close'); }")),array("text" => "Save", "id" => "save-btn", "class" => "gray-button small", "click" => new Zend_Json_Expr(
						"function() {
							var saveBtn = $('#newBaseDialog').parent().find('.ui-dialog-buttonpane').find('button').hide();
							var throbber =  $(\"<img id='throbber' src='/images/throbber_small.gif'>\");
							saveBtn.parent().append(throbber);
							$.post(
								'/account/sites/add-new-base',
								$('form#baseForm').serialize(),


								function(response){

									if (response['optionText']) {

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

										$('#newBaseDialog').dialog('close');
										$('#newBaseDialog').parent().find('.ui-dialog-buttonpane').find('button').show();
										saveBtn.parent().find('#throbber').remove();


										$('#baseForm').val('');


									} else {
										htmlErrors = '<div id=\'baseErrors\' class=\'form-errors alert\'><ul>';

										$('label').removeClass('prompt-error');

										$.each(response, function(elementId, msgs) {
											$('label[for=' + elementId + ']').addClass('prompt-error');
											$.each(msgs, function(key, msg) {
												htmlErrors += '<li>' + msg + '</li>';
											});
										});

										htmlErrors += '</ul></div>';

										$('.form-errors').remove();
										$('#newBaseDialog form').prepend(htmlErrors);

										saveBtn.show();
										saveBtn.parent().find('#throbber').remove();

									}
								}
							)



						}"))),
				),
			)),
		));

	}

	/**
	 * Validate the form, if valid, save the new base, if not, return the error msgs
	 *
	 * @param array $data the POSTed data
	 * @return mixed either boolean true, or an array of error messages
	 */
	public function process($data)
	{

		if($this->isValid($data)){
			$user = \Fisdap\Entity\User::getLoggedInUser();
			$programId = $user->getProgramId();
			$program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $programId);

			$site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $data['base_siteId']);

			$returnArray = array();

			if($data['base_id_input'] == "noBase"){
				$base = new \Fisdap\Entity\BaseLegacy;
				$returnArray['newBase'] = true;
			}
			else {
				$base = \Fisdap\EntityUtils::getEntity("BaseLegacy", $data['base_id_input']);
				$returnArray['newBase'] = false;
			}

			$base->name = $data['base_name'];
			$base->address = $data['base_address'];
			$base->city = $data['base_city'];
			$base->state = $data['base_state'];
			$base->zip = $data['base_zip'];
			$base->site = $site;
			$base->save();
			$program->addBase($base, true);

			// if this is an admin program for this site, add this
			// base to the other programs, too
			if ($program->isAdmin($site->id)) {
				foreach ($site->site_shares as $share) {
					$share->program->addBase($base, true);
				}
			}

			$returnArray['optionText'] = "<option value='" . $base->id . "'>" . $base->name . "</option>";
			$returnArray['baseId'] = $base->id;
			return $returnArray;


		}
		else {
			return $this->getMessages();
		}
	}
}
