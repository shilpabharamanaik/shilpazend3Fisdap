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
 * This produces a form for adding/editing shifts attachments
 */
use Fisdap\Api\Client\Attachments\Gateway\AttachmentsGateway;
use Fisdap\Api\Client\Shifts\Attachments\Gateway\ShiftAttachmentsGateway;

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class SkillsTracker_Form_ShiftAttachmentForm extends Fisdap_Form_Base
{
    /**
     * @var ShiftAttachmentsGateway
     */
    protected $shiftAttachmentsGateway;

    protected $attachment;

    /**
     * @var \Fisdap\Entity\ShiftLegacy
     */
    public $shift;

    /**
     * @var string
     */
    protected $attachmentTableType;

    /**
     * @var string
     */
    public $shiftAttachmentsRemaining;

	/**
     * @param $shiftAttachmentsGateway mixed additional Zend_Form options
	 * @param int $attachmentId the id of the shift attachment to edit
	 */
	public function __construct($shiftAttachmentsGateway, $shift, $attachmentId = null, $tableType = null)
	{
		$this->shiftAttachmentsGateway = $shiftAttachmentsGateway;
        $this->shift = $shift;
        $this->shiftAttachmentsRemaining = $shiftAttachmentsGateway->getRemainingAllottedCount($shift->student->user_context->id);

        if ($attachmentId) {
            $this->attachment = $shiftAttachmentsGateway->getOne($shift->id, $attachmentId);
        }

        $this->attachmentTableType = $tableType;

		parent::__construct();
	}
	
	public function init()
	{
        parent::init();

        // this allows the form to deal with the file upload
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setAction("/attachments/save-attachment");

	    // if this is a new attachment, we need to let the user upload a file
        if (!$this->attachment) {
            // upload file field stuff
            $upload = new Zend_Form_Element_File('upload');
            $upload->setLabel("Attach a file:")
                ->setAttribs(array("class" => "hidden"));
            $this->addElement($upload);
        } else {
            // if we are editing an existing attachment, we need some info about it
            $attachmentId = new Zend_Form_Element_Hidden('attachmentId');
            $fileName = new Zend_Form_Element_Hidden('fileName');
            $this->addElements(array($attachmentId, $fileName));
        }

        // the attachment nickname
        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('Name:')
            ->setAttribs(array("class" => "modal-input fancy-input",
                "maxlength" => "128"));

        // the attachment category
        $category = new Zend_Form_Element_Select('category');
        $category->setLabel('Category:')
				  ->setDescription('(optional)')
                  ->setAttribs(array("class" => "chzn-select"));
        $categoryOptions = array_map(
            function($n) {
                return array("key" => $n->name, "value" => $n->name);
            },
            $this->shiftAttachmentsGateway->getCategories()
        );
        array_unshift($categoryOptions, array("key" => null, "value" => "(no category)"));
        $category->setMultiOptions($categoryOptions);

        // attachment notes
        $notes = new Zend_Form_Element_Textarea('notes');
        $notes->setLabel('Notes:')
            ->setDescription('(optional)')
            ->setAttribs(array('rows' => 7,
                "class" => "modal-input fancy-input",
                "maxlength" => "1024"));

        // some extra info we need to pass around
        $shiftId = new Zend_Form_Element_Hidden('shiftId');
        $attachmentType = new Zend_Form_Element_Hidden('attachmentType');
        $tableType = new Zend_Form_Element_Hidden('tableType');
		
		$this->addElements(array($category, $name, $notes, $attachmentType, $shiftId, $tableType));

        // set element and form decorators
        $this->setElementDecorators(self::$hiddenElementDecorators, array('name', 'category', 'notes', 'tableType'), true);
		$this->setElementDecorators(self::$hiddenElementDecorators, array('attachmentType', 'shiftId', 'attachmentId', 'fileName'), true);
        $this->setDecorators(array(
			'PrepareElements',
			array('ViewScript', array('viewScript' => "shiftAttachmentForm.phtml", 'viewModule' => 'skills-tracker')),
			array('Form', array('class' => 'standard-form'))
		));

        // set some defaults
        $this->setDefaults(array(
            'attachmentType' => "shift",
            'shiftId' => $this->shift->id,
            'tableType' => $this->attachmentTableType
        ));

        // if we are editing an existing attachment, lets grab the values
		if ($this->attachment) {
            $this->setDefaults(array(
                'attachmentId' => $this->attachment->id,
                'fileName' => $this->attachment->fileName,
                'name' => $this->attachment->nickname ? $this->attachment->nickname : $this->attachment->fileName,
                'category' => $this->attachment->categories,
				'notes' => $this->attachment->notes,
			));
		} else {
            $upload->removeDecorator("Label");
        }
	}
	
	/**
	 * Validate the form, if valid, save the attachment, if not, return the error msgs
	 *
	 * @param array $data the POSTed data
	 * @return mixed either boolean true, or an array of error messages
	 */
	public function process($data, $file = null)
	{
        // set up error data in case we need it
        $errorData = array("mode" => "error", "errors" => array());

        // run form validation
        if ($this->isValid($data)) {
            $nickname = $data['name'] ? $data['name'] : null;
            $notes = $data['notes'] ? $data['notes'] : null;
            $categories = $data['category'] ? array($data['category']) : null;
            $errorMessage = "There has been an error uploading your attachment. Please reload the page.";

            // if we're editing an existing attachment, get the entity and modify it via the gateway
            if ($this->attachment) {
                $savedAttachment = $this->shiftAttachmentsGateway->modify($this->shift->id, $this->attachment->id, $nickname, $notes, $categories);
                $mode = "edit";

            } else if ($this->shift->id > 0) {
                // otherwise, we're creating a new shift attachment

                // triple check to make sure we hit the limit while the modal was open
                if ($this->shiftAttachmentsRemaining <= 0) {
                    $limitMessage = $this->isInstructor ? $this->shift->student->user->getName() . "has" : "You've";
                    $limitMessage .= " hit the maximum number of shift attachments. This file will not be attached: ";
                    $limitMessage .= $filePath = $file->getFileName('upload');
                    $errorData['mode'] = "limit";
                    $errorData['errors']['attachmentModal'][] = $limitMessage;
                    return $errorData;
                } else {
                    $shiftId = $this->shift->id;
                    $userContextId = $this->shift->student->user_context->id;
                    $filePath = $file->getFileName('upload');
                    $mode = "add";

                    try {
                        $savedAttachment = $this->shiftAttachmentsGateway->create($shiftId, $userContextId, $filePath, null, $nickname, $notes, $categories);
                        unlink($filePath);
                    } catch (Exception $e) {
                        unlink($filePath);
                        $errorMessage = "You are not allowed to upload this attachment.";
                    }
                }
            }

            // if we successfully added/edited the attachment, send the updated html for the row
            if ($savedAttachment) {
                $view = $this->getView();
                $view->addScriptPath(APPLICATION_PATH . '/modules/skills-tracker/views/scripts');

                // figure out what kind of thumbnail to show
                $attachmentService = new \Fisdap\Service\AttachmentService();
                $savedAttachment->preview = $attachmentService->getPreview($savedAttachment);

                // get the updated mark-up for the shift attachment row
                if($data['tableType'] == 'signoff') {
                    $rows = $attachmentService->getCheckboxRows(array($savedAttachment), $this->shift->type);
                    $viewHelper = new \Fisdap_View_Helper_CheckmarkTableGeneric();
                    $html = $viewHelper->renderCheckmarkTableRows($rows);
                } else {
                    $html = $view->partial('shiftAttachmentRow.phtml',
                        array("attachment" => $savedAttachment,  "associatedEntityId" => $this->shift->id, "titleClass" => $this->shift->type));
                }

                return array("mode" => $mode, "html" => $html, "attachmentId" => $savedAttachment->id);
            }

            $errorData['errors']['attachmentModal'][] = $errorMessage;


		} else {
            $errorData['errors'] = $this->getMessages();
        }

        // if we've gotten this far, something went wrong
        return $errorData;
	}
}
