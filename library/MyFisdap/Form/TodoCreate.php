<?php
class MyFisdap_Form_TodoCreate extends Fisdap_Form_BaseJQuery
{

    // Static variables below are mostly from Modal.php, Base.php. If form is re-based off of Modal.php, can probably remove them

    /**
    * @var array decorators for checkbox elements
    */
    public static $checkboxDecorators = array(
           'ViewHelper',
           array('Label', array('placement' => 'APPEND')),
    );

    /**
     * @var array decorators for buttons
     */
    public static $buttonDecorators = array(
            'ViewHelper',
    );
    
    /**
     * @var array decorators for buttons
     */
    public static $hiddenElementDecorators = array(
            'ViewHelper',
    );

    /**
     * @var array decorators for individual elements
     */
    public static $elementDecorators = array(
        'ViewHelper',
        array(array('break' => 'HtmlTag'), array('tag' => 'br', 'openOnly' => true, 'placement' => 'PREPEND')),
        array('LabelDescription', array('escape' => false)),
        array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt')),
    );
    
    
    /**
     * @var array decorators for jQuery form elements
     */
    public static $formJQueryElements = array(
    'ErrorHighlight',
    array('UiWidgetElement', array('tag' => '')), // it necessary to include for jquery elements
            array(array('break' => 'HtmlTag'), array('tag' => 'br', 'openOnly' => true, 'placement' => 'PREPEND')),
    array('LabelDescription', array('class' => '', 'escape' => false)),
    array('HtmlTag', array('tag'=>'div', 'class'=>'form-prompt')),
    );
    
    /**
     * Initialize and build the form
     */
    public function init()
    {
        $this->addElementPrefixPath('Fisdap_Form_Decorator', 'Fisdap/Form/Decorator/', 'decorator');

        parent::init();
        
        //initialize form
        $this->setMethod('post');

        // start collecting elements
        $elements = array();
        $toBeDecorated = array();
        
        // Todo/Message Subject
        $subject = new Zend_Form_Element_Text('subject');
        $subject->setLabel("Subject/task")
            ->setRequired(true)
            ->addFilter('StringTrim')
            ->setAttrib('maxlength', 256)
            ->setDecorators(self::$elementDecorators);
        $elements[] = $subject;
        $toBeDecorated[] = 'subject';
        
        // Todo/Message Notes
        // since this form just creates a message for hte user, only the always-editable "notes" is saved (body = null)
        $notes = new Zend_Form_Element_Textarea('notes');
        $notes->setLabel("Notes")
            ->setAttrib('rows', 20)
            ->addFilter('StringTrim')
            ->setDecorators(self::$elementDecorators);
        $elements[] = $notes;
        $toBeDecorated[] = 'notes';
        
        // Due date?
        $date = new ZendX_JQuery_Form_Element_DatePicker('date');
        $date->setLabel('Due Date')
                 ->addValidator('Date', false, array('format' => 'MM/dd/yyyy'));
        $elements[] = $date;
        $toBeDecorated[] = 'date';

        
        // submit
        $submit = new Fisdap_Form_Element_Submit('submit');
        $submit->setLabel('Create Todo')
                ->setAttrib('class', 'green-button medium');
        $elements[] = $submit;
        $toBeDecorated[] = 'submit';
        
        
        //attach elements to form
        $this->addElements($elements);
        
        //Set some decorators to add a description
        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "form/TodoCreate.phtml")),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));

        
        // set element decorators
        $this->setElementDecorators(self::$elementDecorators, $toBeDecorated, false);
        
        // date jquery decorator
        $this->setElementDecorators(self::$formJQueryElements, array('date'), true);
    }
}
