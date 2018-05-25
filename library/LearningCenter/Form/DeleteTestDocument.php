<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * /


/**
 * Form to delete a Moodle Test Document
 */

/**
 * @package    LearningCenter
 */
class LearningCenter_Form_DeleteTestDocument extends SkillsTracker_Form_Modal
{
	/**
	 * @var \Fisdap\Entity\MoodleTestDocument
	 */
	public $document;
	
	
	public function __construct($document = null, $options = null)
	{
		if ($document instanceof \Fisdap\Entity\MoodleTestDocument) {
			$this->document = $document;
		} else {
			$this->document = null;
		}
		
		return parent::__construct($options);
	}
	
	public function init()
	{
		parent::init();
		
		// hidden field for document ID (in case of modify)
		$moodleDocumentId = new Zend_Form_Element_Hidden("test_document_id");
		$moodleDocumentId->setDecorators(array("ViewHelper"));
		$this->addElement($moodleDocumentId);
		
		$delete = new Fisdap_Form_Element_GrayButton("Delete");
		$delete->setDecorators(array("ViewHelper"));
		$this->addElement($delete);	
	
		$this->setDefaults(array(
			"test_document_id" => $this->document->id,
		));

	}
	
	public function process($post) {
		$em = \Fisdap\EntityUtils::getEntityManager();
		$em->remove($this->document);
		$em->flush();
	}
	
}