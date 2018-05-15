<?php
class MyFisdap_Form_MessageCreate extends Fisdap_Form_Base
{
    /**
     * @var integer The current step in the multistep process
     */
    public $step;
    
    /**
     * @var User the user whose permissions should be evaluated in rendering the form
     */
    public $user;
    
    /**
     * @var integer The ID of a Program, if you want to limit message recipients users in that program
     */
    public $programId;
    
    /**
     * @var boolean Whether UserGroupFilter checkboxes are visible/usable or not
     */
    public $userGroupFilterCheckboxes = false;
    
    /**
     * Constructs the Message Creation form
     * @param integer $step The current step in the multistep process
     * @param User $user the user whose permissions should be evaluated in rendering the form
     * @param integer $programId The ID of a Program, if you want to limit message recipients users in that program
     */
    public function __construct($step = 1, \Fisdap\Entity\User $user, $programId = null, $option = null)
    {
        if (is_numeric($step)) {
            $this->step = $step;
        }
        $this->user = $user;
        if (is_numeric($programId)) {
            $this->programId = $programId;
        } else {
            $this->programId = null;
        }
        parent::__construct($option);
    }
    
    /**
     * Set the step number
     */
    public function set_step($step)
    {
        if (is_numeric($step)) {
            $this->step = $step;
        }
    }
    
    /**
     * Initialize and build the form
     */
    public function init()
    {
        parent::init();
        
        //initialize form
        $this->setMethod('post');

        // start collecting elements
        $elements = array();
        $toBeDecorated = array();
        
        // If this is step 2 or later, add a confirmation checkbox and tell student picker not to do onload
        if ($this->step > 1) {
            if ($this->user->staff != null && $this->user->staff->isStaff()) {
                // staff get a checkbox
                $confirmation = new Zend_Form_Element_Checkbox('confirmation');
                $confirmation->setLabel("Are you sure you want to send this message?")
                        ->setDecorators(array(
                                'ViewHelper',
                                array(array('break' => 'HtmlTag'), array('tag' => 'br', 'openOnly' => true, 'placement' => 'PREPEND')),
                                array('LabelDescription', array('escape' => false)),
                                array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt', 'id' => 'message-create-confirmation')),
                            ));
            } else {
                // instructors get a hidden/implicit value (we assume that submitting on step 2 means they want to actually send the message)
                $confirmation = new Zend_Form_Element_Hidden('confirmation');
                $confirmation->setValue('1');
                $elements[] = $confirmation;
                $toBeDecorated[] = 'confirmation';
            }
            $elements[] = $confirmation;
           
            // preview submit
            $confSubmit = new Fisdap_Form_Element_Submit('confirmation_submit');
            $confSubmit->setLabel('Post')
                    ->setAttrib('class', 'green-button medium')
                    ->setAttrib('style', 'float: right');
            $elements[] = $confSubmit;
            $toBeDecorated = array('confirmation_submit');
            
            // hidden field to tell student pick not to reset field values
            $pickerOnload = new Zend_Form_Element_Hidden('picker_do_not_onload');
            $pickerOnload->setValue('1');
            $elements[] = $pickerOnload;
            $toBeDecorated[] = 'picker_do_not_onload';
        }
        
        // Message Subject
        $subject = new Zend_Form_Element_Text('subject');
        $subject->setLabel("Subject:")
            ->setDescription('(required)')
            ->setRequired(true)
            ->addErrorMessage('This field is required.')
            ->addFilter('StringTrim')
            ->setAttrib('maxlength', 256)
            ->setAttrib('size', 60)
            ->setDecorators(self::$elementDecorators);
        $elements[] = $subject;
        $toBeDecorated[] = 'subject';
            
        // Message Body
        $body = new Zend_Form_Element_Textarea('body');
        $body->setLabel("Body:")
            ->setDescription('(required)')
            ->setRequired(true)
            ->addErrorMessage('This field is required.')
            ->setAttrib('rows', 20)
            ->addFilter('StringTrim')
            ->setDecorators(self::$elementDecorators);
        $elements[] = $body;
        $toBeDecorated[] = 'body';
            
        // staff options
        if ($this->user->staff != null && $this->user->staff->isStaff()) {
            // Big textarea for dumping a comma-separated list of user IDs
            $userIds = new Zend_Form_Element_Textarea('userIds');
            $userIds->setLabel("Comma-separated list of user IDs")
                ->setDescription("<span style='background-color: yellow; text-decoration: blink;'>MUST BE NUMERIC USER IDs FROM A MYSQL QUERY. ASK A DEVELOPER.</span>, not instructor IDs or student IDs or usernames. No, seriously, this needs to be ONLY numbers and commas. And the numbers must be UserLegacy-idx-column USER IDs")
                ->setAttrib('rows', 20)
                ->addFilter('StringTrim')
                ->addFilter('PregReplace', array('match' => '/ +/', 'replace' => ''))
                ->setDecorators(self::$elementDecorators);
            
            $elements[] = $userIds;
            $toBeDecorated[] = 'userIds';
        }
        
        // Staff get full array of filters
        if ($this->user->staff != null && $this->user->staff->isStaff()) {
            $defaultFilters = array(
                                'StudentFilter',
                                'RoleFilter',
                                'Everyone',
                                'ProgramFilter'
                            );
            
            // Let's also allow staff to use the checkboxes on the user group filter
            $this->userGroupFilterCheckboxes = true;
        } elseif ($this->user->getCurrentRoleName() == 'instructor') {
            // instructors are not allowed to change program or use the Everyone option
            $defaultFilters = array(
                                'RoleFilter',
                                'StudentFilter'
                            );
        }
        $this->addSubForm(new MyFisdap_Form_UserGroupFilter($defaultFilters, $this->programId), 'userGroupFilter');
        $this->getSubForm('userGroupFilter')->setDecorators(array(
            'FormElements',
        ));

        
        // preview submit
        $submit = new Fisdap_Form_Element_Submit('preview_submit');
        $submit->setLabel('Preview')
                ->setAttrib('class', 'green-button medium')
                ->setAttrib('style', 'float: right');
        $elements[] = $submit;
        $toBeDecorated[] = 'preview_submit';
                
        //attach elements to form
        $this->addElements($elements);
            
        //Set some decorators to add a description
        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => "form/MessageCreate.phtml")),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));

        
        // set element decorators
        $this->setElementDecorators(self::$elementDecorators, $toBeDecorated, false);
    }
}
