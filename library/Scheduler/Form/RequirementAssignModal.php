<?php

use Fisdap\Data\Requirement\RequirementRepository;
use Fisdap\Entity\ProgramLegacy;
use Fisdap\Entity\Requirement;
use Fisdap\Entity\User;

/**
 * This produces a modal form for assigning requirement(s) to student(s)
 *
 * @package    Scheduler
 * @subpackage Forms
 */
class Scheduler_Form_RequirementAssignModal extends Fisdap_Form_BaseJQuery
{
    /**
     * @var RequirementRepository
     */
    private $requirementRepository;

    /**
     * @var User
     */
    public $user;

    /**
     * @var array
     */
    public $requirement_ids;

    /*
     *
     */
    public $multistudent_picklist;

    /**
     * @var null The Zend Cache backend to use for retrieving information about queued jobs
     */
    private $cache = null;


    /**
     *
     * @param RequirementRepository $requirementRepository
     * @param null                  $msp
     * @param null                  $requirement_ids
     * @param                       $options mixed additional Zend_Form options
     */
    public function __construct(
        RequirementRepository $requirementRepository,
        $msp = null,
        $requirement_ids = null,
        $options = null
    ) {
        $this->requirementRepository = $requirementRepository;
        $this->user = User::getLoggedInUser();
        $this->multistudent_picklist = $msp;
        $this->requirement_ids = $requirement_ids;

        parent::__construct($options);
    }

    public function init()
    {
        parent::init();
        $this->addJsFile("/js/library/Scheduler/Form/requirement-assign-modal.js");
        $this->addCssFile("/css/library/Scheduler/Form/requirement-assign-modal.css");

        // initialize cache
        $cacheManager = \Zend_Registry::get('zendCacheManager');
        $this->cache = $cacheManager->getCache('default');

        $today = new DateTime();
        $due_date = new Zend_Form_Element_Text("due_date");
        $due_date->setAttribs(array("class" => "selectDate fancy-input"));
        $due_date->setValue($today->format("m/d/Y"));

        $account_type = new Zend_Form_Element_Hidden("accountType");
        $account_type->setValue(1);

        if ($this->requirement_ids) {
            $req_attachment_data = $this->requirementRepository->getAttachedUserContextIdsByRequirements(
                $this->requirement_ids,
                $this->user->getProgramId()
            );

            if ($req_attachment_data) {
                foreach ($req_attachment_data as $req_id => $userContextIds) {
                    $hidden_element = new Zend_Form_Element_Hidden("hidden_" . $req_id . "_attachments");
                    $hidden_element->setAttribs(array("class" => "assign_modal_hidden_attachments"));
                    $hidden_element->setValue(implode(",", $userContextIds));
                    $this->addElements(array($hidden_element));
                }
            }
        }

        $this->addElements(array($due_date, $account_type));

        //Set the decorators for the form
        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => "requirementAssignModal.phtml")),
        ));
    }


    /**
     * Process the form
     *
     * @param          $requirement_ids
     * @param          $userContextIds
     * @param          $due_date
     * @param int|null $program_id
     * @param int|null $assigner_userContextId
     *
     * @return array
     */
    public function process($requirement_ids, $userContextIds, $due_date, $program_id = null, $assigner_userContextId = null)
    {
        //Get the user's program if not provided, otherwise spin up the entity from the given ID
        if (is_null($program_id)) {
            $program = ProgramLegacy::getCurrentProgram();
        } else {
            $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $program_id);
        }

        // get the requirement entities
        $req_entities = [];
        $date_time_due_date = new DateTime($due_date . " 23:59:59");
        $compute_compliance_userContextIds = [];

        foreach ($requirement_ids as $id) {
            /** @var Requirement $requirement */
            $requirement = $this->requirementRepository->getOneById($id);
            $req_entities[$id] = array("entity" => $requirement, "sendNotification" => $program->sendNewRequirementNotification($id));

            $temp_userContextIds = $requirement->assignRequirementToUserContexts($userContextIds, $date_time_due_date, array(), $program->sendNewRequirementNotification($id), $assigner_userContextId);
            $compute_compliance_userContextIds = array_merge($compute_compliance_userContextIds, $temp_userContextIds);
            $requirement->save();
        }

        return array_unique($compute_compliance_userContextIds);
    }
}
