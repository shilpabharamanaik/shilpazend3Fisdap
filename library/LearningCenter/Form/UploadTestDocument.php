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
 * Form to upload/edit a Moodle Test Document
 */

/**
 * @package    LearningCenter
 */
class LearningCenter_Form_UploadTestDocument extends SkillsTracker_Form_Modal
{
    /**
     * @var \Fisdap\Entity\MoodleTestDocument
     */
    public $document;
    
    
    public function __construct($document = null, $options = null)
    {
        if ($document != null) {
            $this->document = \Fisdap\EntityUtils::getEntity("MoodleTestDocument", $document);
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
        
        // upload file field stuff
        $this->setAttrib('enctype', 'multipart/form-data');
        $upload = new Zend_Form_Element_File('upload');
        $upload->setLabel("File")
                ->addValidator('Count', false, 1)
                ->addValidator('Size', false, 104857600); // 100 MB sanity check
        if ($this->document == null) {
            $upload->setRequired(true);
        }
        $this->addElement($upload);
        
        $moodleRepos = \Fisdap\EntityUtils::getRepository('MoodleTestDataLegacy');
        $options = $moodleRepos->getMoodleTestList(array('active' => 1, 'extraGroups' => array('pilot_tests')), 'productArray');
        if (is_array($options)) {
            $options = array('' => '') + $options;
        } else {
            $options = array();
        }
        $test = new Zend_Form_Element_Select("test_id");
        $test->setLabel("Test with which this document will be associated")
             ->setRequired(true)
             ->addValidator(new Zend_Validate_NotEmpty())
             ->setDecorators(self::$elementDecorators)
             ->setMultiOptions($options);
        $this->addElement($test);
        
        $label = new Zend_Form_Element_Text("label");
        $label->setLabel("Name for this document")
            ->setDecorators(self::$elementDecorators);
        $this->addElement($label);
        
        $description = new Zend_Form_Element_Text("description");
        $description->setLabel("Description for this document")
            ->setDecorators(self::$elementDecorators);
        $this->addElement($description);
                
        $save = new Fisdap_Form_Element_SaveButton("save");
        $save->setDecorators(array("ViewHelper"));
        $this->addElement($save);
        
        if ($this->document) {
            $this->setDefaults(array(
                "test_document_id" => $this->document->id,
                "test_id" =>$this->document->test->moodle_quiz_id,
                "label" => $this->document->label,
                "description" => $this->document->description,
            ));
        }
    }
    
    public function process($post, $file = null)
    {
        if (is_numeric($post['test_document_id'])) {
            $upload = \Fisdap\EntityUtils::getEntity("MoodleTestDocument", $post['test_document_id']);
        } else {
            $upload = new \Fisdap\Entity\MoodleTestDocument();
            $upload->created = new \DateTime();
        }
        $upload->set_test($post['test_id']);
        $upload->description = $post['description'];
        $upload->label = $post['label'];
        $upload->updated = new \DateTime();
    
        // only handle the file if we received a new one
        $filePath = $file->getFileName('upload');
        if (!is_array($filePath) && $filePath != '') {
            // split up the filename to get original name
            $fileSplit = explode('/', $filePath);
            $upload->original_name = end($fileSplit);
            $upload->mime_type = $file->getMimeType('upload');
            $upload->content = base64_encode(gzcompress(file_get_contents($filePath)));
        }
                
        $upload->save();
    }
}
