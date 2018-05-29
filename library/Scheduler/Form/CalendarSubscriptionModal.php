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
 * This produces a modal form for creating a pdf for scheduler
 */

/**
 * @package    Scheduler
 * @subpackage Forms
 */
class Scheduler_Form_CalendarSubscriptionModal extends Fisdap_Form_BaseJQuery
{
    /**
     * @var \Fisdap\Entity\CalendarFeed
     */
    public $calendarFeed;

    /**
     * @var \Fisdap\Entity\User
     */
    public $user;

    /**
     * @var bool
     */
    public $isInstructor;

    /**
     * @var bool
     */
    public $isStaff;

    /**
     * @var array decorators for individual elements
     */
    public static $basicDecorators = array(
        'ViewHelper',
        array('Description', array('tag' => 'span', 'openOnly' => true, 'placement' => 'prepend', 'class' => 'description')),
        'Errors',
        array('Label', array('tag' => 'span', 'openOnly' => true, 'placement' => 'prepend', 'class' => 'section-header no-border', 'escape'=>false)),
    );

    /**
     * @var array decorators for hidden elements
     */
    public static $hiddenDecorators = array(
        'ViewHelper',
    );


    /**
     * @param $calendarFeedId integer
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($calendarFeedId = null, $options = null)
    {
        if ($calendarFeedId) {
            $this->calendarFeed = \Fisdap\EntityUtils::getEntity('CalendarFeed', $calendarFeedId);
        }

        $this->user = \Fisdap\Entity\User::getLoggedInUser();
        $this->isInstructor = $this->user->isInstructor();
        $this->isStaff = $this->user->isStaff();

        parent::__construct($options);
    }

    public function init()
    {
        parent::init();

        $this->addJsFile("/js/library/Scheduler/Form/calendar-subscription-modal.js");
        $this->addCssFile("/css/library/Scheduler/Form/calendar-subscription-modal.css");

        // create form elements
        $calendarName = new \Zend_Form_Element_Text("calendarName");
        $calendarName->setLabel("Calendar name:")
            ->setAttribs(array("class" => "fancy-input"));
        $this->addElement($calendarName);

        $dateRange = new \Fisdap_Form_Element_DateRange("dateRange");
        $dateRange->setDefaultEnd()->removeDecorator("Label");
        $this->addElement($dateRange);

        $url = new \Zend_Form_Element_Text("url");
        $url->setLabel("URL:")
            ->setAttribs(array("class" => "fancy-input", "style" => ";"));
        $this->addElement($url);

        // Staff only options
        if($this->isStaff){
            // Get an array of non-staff instructors who have "View Schedules" access
            $instructors = \Fisdap\EntityUtils::getRepository('ProgramLegacy')->getNonStaffInstructorsByPermission($this->user->getProgramId(), 128);

            // Iterate through the result array and format it for use in a chosen
            $instructorOptions = array(""=>"");
            foreach ($instructors as $instructor) {
                $instructorOptions[$instructor['instructor_id']] = $instructor['first_name'] . " " . $instructor['last_name'];
            }

            // Make the chosen
            $instructor = new Zend_Form_Element_Select("instructor");
            $instructor->setMultiOptions($instructorOptions)
                ->setLabel("Instructor (staff only):")
                ->setAttribs(array("class" => "chzn-select",
                                   "data-placeholder" => "Choose an instructor",
                                   "style" => "width:250px;"));
            $instructor->setValue('');
            $this->addElement($instructor);
        }

        $calendarId = new \Zend_Form_Element_Hidden("calendarId");
        $calendarId->setDecorators(self::$hiddenDecorators);
        $this->addElement($calendarId);

        // set defaults
        $this->setDefaults(array(
                "dateRange" => array("startDate" => date("m/d/Y"))
            ));

        // if we're editing a recurring email, set some other defaults
        if ($this->calendarFeed->id) {
            $this->setDefaults(array(
                    'calendarId' => $this->calendarFeed->id,
                    'calendarName' => $this->calendarFeed->name,
                    "dateRange" => array("endDate" => $this->calendarFeed->end_date, "startDate" => $this->calendarFeed->start_date),
                    "url" => $this->calendarFeed->getUrl(),
                ));
        }

        $this->setDecorators(array(
                'PrepareElements',
                array('ViewScript', array('viewScript' => "forms/calendarSubscriptionModal.phtml")),
                'Form',
                array('DialogContainer',
                      array(
                          'id'			=> 'calendarSubDialog',
                          'class'			=> 'calendarSubDialog',
                          'jQueryParams' 	=> array(
                              'tabPosition' 	=> 'top',
                              'modal' 		=> true,
                              'autoOpen' 		=> false,
                              'resizable' 	=> false,
                              'width' 		=> 800,
                              'title' 		=> 'Subscribe to ' . ($this->isInstructor ? 'chosen' : 'your') . ' shifts',
                          ),
                      )
                ),
            ));

    }

    public function process($form_data)
    {
        if ($form_data['calendarId']) {
            $calendarFeed = \Fisdap\EntityUtils::getEntity("CalendarFeed", $form_data['calendarId']);
        } else {
            $calendarFeed = \Fisdap\EntityUtils::getEntity("CalendarFeed");
        }

        $calendarFeed->name = $form_data['calendarName'];
        //Not using these fields for MVP
        //$calendarFeed->start_date = $form_data['dateRange']['startDate'];
        //$calendarFeed->end_date = $form_data['dateRange']['endDate'];
        $auth_key = $calendarFeed->generateAuthKey();
        $calendarFeed->filters = $form_data['filters'];

        if ($this->isStaff) {
            $calendarFeed->user_context = \Fisdap\EntityUtils::getEntity("InstructorLegacy", $form_data['instructor'])->user_context;
        } else {
            $calendarFeed->user_context = \Fisdap\Entity\User::getLoggedInUser()->getCurrentUserContext();
        }

        $calendarFeed->save();

        return $auth_key;
    }
}