<?php

use Fisdap\Data\Program\RequiredShiftEvaluations\ProgramRequiredShiftEvaluationsRepository;

/**
 * This produces a modal form for customizing a program's required shift evals
 */

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class SkillsTracker_Form_RequireEvalsModal extends Fisdap_Form_BaseJQuery
{
    /**
     * @var
     */
    public $field_evals;

    public $clinical_evals;

    public $lab_evals;

    /**
     * @var ProgramRequiredShiftEvaluationRepository
     */
    private $programReqRepo;

    /**
     *
     * @param $options mixed additional Zend_Form options
     */
    public function __construct(ProgramRequiredShiftEvaluationsRepository $programReqRepo, $options = null)
    {
        parent::__construct($options);
        $this->programReqRepo = $programReqRepo;
    }

    public function init()
    {
        parent::init();
        $this->addJsFile("/js/library/SkillsTracker/Form/require-evals-modal.js");

        $program_id = \Fisdap\Entity\User::getLoggedInUser()->getProgramId();

        $field_evals = \Fisdap\EntityUtils::getRepository("EvalDefLegacy")->getEvalsByHook(113, $program_id);
        $clinical_evals = \Fisdap\EntityUtils::getRepository("EvalDefLegacy")->getEvalsByHook(114, $program_id);
        $lab_evals = \Fisdap\EntityUtils::getRepository("EvalDefLegacy")->getEvalsByHook(115, $program_id);

        $this->field_evals = $field_evals;
        $this->clinical_evals = $clinical_evals;
        $this->lab_evals = $lab_evals;

        //get all required evals for program by requirement type
        $required_field_evals = \Fisdap\EntityUtils::getRepository('ProgramRequiredShiftEvaluations')->getByProgram($program_id, 'field');
        $required_clinical_evals = \Fisdap\EntityUtils::getRepository('ProgramRequiredShiftEvaluations')->getByProgram($program_id, 'clinical');
        $required_lab_evals = \Fisdap\EntityUtils::getRepository('ProgramRequiredShiftEvaluations')->getByProgram($program_id, 'lab');

        //we need to build arrays of the eval ids by type to check against for form defaults and form processing as a single eval id could be required for up to 3 shift types
        $required_field_eval_ids = array();
        $required_clinical_eval_ids = array();
        $required_lab_eval_ids = array();

        foreach ($required_field_evals as $required_eval) {
            $required_field_eval_ids[$required_eval->getId()] = $required_eval->getEvalDef()->id;
        }

        foreach ($required_clinical_evals as $required_eval) {
            $required_clinical_eval_ids[$required_eval->getId()] = $required_eval->getEvalDef()->id;
        }

        foreach ($required_lab_evals as $required_eval) {
            $required_lab_eval_ids[$required_eval->getId()] = $required_eval->getEvalDef()->id;
        }

        foreach ($field_evals as $eval) {

            $id = $eval['id'];

            $require = new Zend_Form_Element_Checkbox($id.'_field_require');

            $this->addElements(array($require));

            if (in_array($id, $required_field_eval_ids)) {
                $this->setDefaults(array($id.'_field_require' => 1));
            } else {
                $this->setDefaults(array($id.'_field_require' => null));
            }
        }

        foreach ($clinical_evals as $eval) {

            $id = $eval['id'];

            $require = new Zend_Form_Element_Checkbox($id.'_clinical_require');

            $this->addElements(array($require));

            if (in_array($id, $required_clinical_eval_ids)) {
                $this->setDefaults(array($id.'_clinical_require' => 1));
            } else {
                $this->setDefaults(array($id.'_clinical_require' => null));
            }
        }

        foreach ($lab_evals as $eval) {

            $id = $eval['id'];

            $require = new Zend_Form_Element_Checkbox($id.'_lab_require');

            $this->addElements(array($require));

            if (in_array($id, $required_lab_eval_ids)) {
                $this->setDefaults(array($id.'_lab_require' => 1));
            } else {
                $this->setDefaults(array($id.'_lab_require' => null));
            }
        }

        $req_field_eval_ids = new Zend_Form_Element_Hidden('required_field_eval_ids');
        $req_field_eval_ids->removeDecorator('label');
        $req_clinical_eval_ids = new Zend_Form_Element_Hidden('required_clinical_eval_ids');
        $req_clinical_eval_ids->removeDecorator('label');
        $req_lab_eval_ids = new Zend_Form_Element_Hidden('required_lab_eval_ids');
        $req_lab_eval_ids->removeDecorator('label');

        $this->addElements(array($req_field_eval_ids, $req_clinical_eval_ids, $req_lab_eval_ids));
        $this->setDefaults(array('required_field_eval_ids' => serialize($required_field_eval_ids), 'required_clinical_eval_ids' => serialize($required_clinical_eval_ids), 'required_lab_eval_ids' => serialize($required_lab_eval_ids)));

        $this->setAttrib('id', 'requireEvalsForm');

        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "requireEvalsModal.phtml")),
            'Form',
            array('DialogContainer', array(
                'id'          => 'requireEvalsDialog',
                'class'          => 'requireEvalsDialog',
                'jQueryParams' => array(
                    'tabPosition' => 'top',
                    'modal' => true,
                    'autoOpen' => false,
                    'resizable' => false,
                    'width' => 800,
                    'title' => 'Customize required evals',
                    'open' => new Zend_Json_Expr("function(event, ui) { $('button').css('color', '#000000'); }"),
                    'buttons' => array(
                        array("text" => "Save", "id" => "save-btn", "class" => "green-button small", "click" => new Zend_Json_Expr(
                            "function() {
							var postValues = $('#requireEvalsForm').serialize();
							$('#requireEvalsForm :input').attr('disabled', true);
							var saveBtn = $('#requireEvalsDialog').parent().find('.ui-dialog-buttonpane').find('button').hide();
							$('#section-button').hide();
							var throbber =  $(\"<img id='requireEvalsThrobber' src='/images/throbber_small.gif'>\");
							$('#preview_link').hide();

							saveBtn.parent().append(throbber);
							$.post(
								'/skills-tracker/settings/save-require-evals-settings',
								postValues,


								function (response){

									if(response === true){
										window.location = '/skills-tracker/settings';
									}
									else
									{
										htmlErrors = '<div id=\'requireEvalsErrors\' class=\'form-errors alert\'><ul>';

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
										$('#requireEvalsDialog form').prepend(htmlErrors);
										saveBtn.show();
										saveBtn.parent().find('#requireEvalsThrobber').remove();
									}
								}
							)



						}"))),
                ),
            )),
        ));

    }

    /**
     * Validate the form, if valid, save the required evals, if not, return the error msgs
     *
     * @param array $data the POSTed data
     * @return mixed either boolean true, or an array of error messages
     */
    public function process($data)
    {
        if ($this->isValid($data)) {
            $user = \Fisdap\Entity\User::getLoggedInUser();
            $program_id = $user->getProgramId();

            $required_field_eval_ids = unserialize($data['required_field_eval_ids']);
            $required_clinical_eval_ids = unserialize($data['required_clinical_eval_ids']);
            $required_lab_eval_ids = unserialize($data['required_lab_eval_ids']);


            foreach ($this->field_evals as $eval) {

                $id = (int)$eval['id'];

                //check if an eval marked as required is already known as a required eval, if not, create it.
                //if an eval is marked as not required on the form and is required by the system, delete that required eval entity for the program.
                //all other cases require no changes so can be ignored
                if ($data[$id . "_field_require"] == 1 && !in_array($id, $required_field_eval_ids)) {

                    //create required eval entity
                    $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $program_id);
                    $eval_def = \Fisdap\EntityUtils::getEntity('EvalDefLegacy', $id);

                    $new_required_eval = new \Fisdap\Entity\ProgramRequiredShiftEvaluations($program, $eval_def, 'field');

                    $this->programReqRepo->store($new_required_eval);

                } else if ($data[$id . "_field_require"] == 0 && in_array($id, $required_field_eval_ids)) {
                    $key = array_search($id, $required_field_eval_ids);

                    //delete existing required eval entity
                    $existing_required_eval = \Fisdap\EntityUtils::getEntity('ProgramRequiredShiftEvaluations', $key);
                    $this->programReqRepo->destroy($existing_required_eval);

                }
            }

            foreach ($this->clinical_evals as $eval) {

                $id = (int)$eval['id'];

                if ($data[$id . "_clinical_require"] == 1 && !in_array($id, $required_clinical_eval_ids)) {

                    //create required eval entity
                    $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $program_id);
                    $eval_def = \Fisdap\EntityUtils::getEntity('EvalDefLegacy', $id);

                    $new_required_eval = new \Fisdap\Entity\ProgramRequiredShiftEvaluations($program, $eval_def, "clinical");

                    $this->programReqRepo->store($new_required_eval);

                } else if ($data[$id . "_clinical_require"] == 0 && in_array($id, $required_clinical_eval_ids)) {
                    $key = array_search($id, $required_clinical_eval_ids);

                    //delete existing required eval entity
                    $existing_required_eval = \Fisdap\EntityUtils::getEntity('ProgramRequiredShiftEvaluations', $key);
                    $this->programReqRepo->destroy($existing_required_eval);

                }
            }

            foreach ($this->lab_evals as $eval) {

                $id = (int)$eval['id'];

                if ($data[$id . "_lab_require"] == 1 && !in_array($id, $required_lab_eval_ids)) {

                    //create required eval entity
                    $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $program_id);
                    $eval_def = \Fisdap\EntityUtils::getEntity('EvalDefLegacy', $id);

                    $new_required_eval = new \Fisdap\Entity\ProgramRequiredShiftEvaluations($program, $eval_def, "lab");

                    $this->programReqRepo->store($new_required_eval);

                } else if ($data[$id . "_lab_require"] == 0 && in_array($id, $required_lab_eval_ids)) {
                    $key = array_search($id, $required_lab_eval_ids);

                    //delete existing required eval entity
                    $existing_required_eval = \Fisdap\EntityUtils::getEntity('ProgramRequiredShiftEvaluations', $key);
                    $this->programReqRepo->destroy($existing_required_eval);

                }
            }

            return true;
        }

        return $this->getMessages();
    }
}
