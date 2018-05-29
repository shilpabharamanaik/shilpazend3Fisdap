<?php

class MyFisdap_Widgets_ProductivityWidgetThreeTab extends MyFisdap_Widgets_Base
{
    /* default properties */
    protected $registeredCallbacks = array('ajaxModifyWidgetSettings', 'ajaxGetMessages', 'ajaxSaveMessage', 'ajaxDoPendingDeletions');

    /* additional properties */
    protected $user = null;

    /* Constructor */
    public function __construct($widgetId){
        // include parent constructor's work
        parent::__construct($widgetId);

        // get the logged in user
        $this->user = $this->getWidgetUser();
    }

    /* default methods */
    public function getDefaultData(){
        return array(
            'selected' => '0',
            'inbox' => array(
                'filters' => array(
                    'message' => 1,
                    'todo' => 1
                ),
                'sort' => 'receivedDateDesc',
            ),
            'calendar' => array(
                'filters' => array(
                    'todo' => 1,
                    'shift' => 1
                ),
            ),
            'archive' => array(
                'filters' => array(
                    'message' => 1,
                    'todo' => 1,
                    'archived' => 1
                ),
                'sort' => 'receivedDateDesc',
            ),
        );
    }


    /* render methods */
    public function render(){
        $elemName = $this->getNamespacedName('productivity-widget-tt');

        $js = "
            <script type='text/javascript' src='/js/jquery.scrollTo-1.4.2-min.js'></script>
            <script type='text/javascript' src='/js/iscroll4/iscroll.js'></script>
            <script type='text/javascript' src='/js/library/MyFisdap/jquery.productivityWidget.js?120501'></script>
            ";

        // allow announcements? options depending on user role
        $isInstructor = $this->user->isInstructor();
        if ($isInstructor && $this->user->hasPermission('Edit Program Settings')) {
            $calFormat = 'calendar1';
            $messageCreateButton = "<a href='/my-fisdap/message/create' class='message-message-create'>+ Ancemt</a>";
        } else {
            $calFormat = 'calendar4';
            $messageCreateButton = '';
        }

        // calendar format based on role
        if ($isInstructor) {
            $calFormat = 'calendar1';
        } else {
            $calFormat = 'calendar4';
        }

        // if this user has access to scheduler or skills tracker, give calendar links.
        $calendarLinks = array();
        $serialNumber = $this->user->getCurrentUserContext()->getPrimarySerialNumber();
        if ($serialNumber) {
            if ($serialNumber->hasScheduler()) {
                $calendarLinks[] = '<a href="/skills-tracker/shifts/calendar">View Full Schedule</a>';
            }
            /* if ($serialNumber->hasProductAccess('tracking')) {
                $calendarLinks[] = '<a href="/skills-tracker/shifts">Patient Care Documentation</a>';
            }*/
        } else if ($this->user->isInstructor() && $this->user->hasPermission('View Schedules')) {
            if ($this->getWidgetProgram()->get_use_scheduler()) {
                // instructors who can view schedules in a program that uses scheduler
                $calendarLinks[] = '<a href="/skills-tracker/shifts/calendar">View Full Schedule</a>';
            }
        }
        if (!empty($calendarLinks)) {
            $calendarLinks = '<div class="calendar-view-links">' . implode(' ', $calendarLinks) . '</div>';
        } else {
            $calendarLinks = '';
        }

        $js .= "
            <script type='text/javascript'>
            	// Update the number of read messages on read...
                function updateInboxUnreadCount(){
                	var unreadCount = $('#" . $elemName . "-inbox').productivityWidget('getMessages', 'unread', true);

                	$('#" . $elemName . "-inbox-tab-text').text('(' + unreadCount.toString() + ')');
               	}

            $(document).ready(function() {
                $('#" . $elemName . "-inbox').productivityWidget({
                    'fisdapWidgetId' : {$this->widgetData->id},
                    'filters' : {
                        'message' : " . $this->_getDataOrDefault(array('inbox', 'filters', 'message')) . ",
                        'todo' : " . $this->_getDataOrDefault(array('inbox', 'filters', 'todo')) . "
                    },
                    'hideFilters' : {
                        'event' : 1,
                        'shift' : 1
                    },
                    'sort' : '" . $this->_getDataOrDefault(array('inbox', 'sort')) . "'
                });

                $('#" . $elemName . "-archive').productivityWidget({
                    'fisdapWidgetId' : {$this->widgetData->id},
                    'filters' : {
                        'message' : " . $this->_getDataOrDefault(array('archive', 'filters', 'message')) . ",
                        'todo' : " . $this->_getDataOrDefault(array('archive', 'filters', 'todo')) . ",
                        'archived' : " . $this->_getDataOrDefault(array('archive', 'filters', 'archived')) . "
                    },
                    'hideFilters' : {
                        'event' : 1,
                        'shift' : 1
                    },
                    'sort' : '" . $this->_getDataOrDefault(array('archive', 'sort')) . "'
                });

                $('#" . $elemName . "-calendar').productivityWidget({
                    'fisdapWidgetId' : {$this->widgetData->id},
                    'filters' : {
                        'todo' : " . $this->_getDataOrDefault(array('calendar', 'filters', 'todo')) . ",
                        'shift' : " . $this->_getDataOrDefault(array('calendar', 'filters', 'shift')) . ",
                        'message' : 0
                    },
                    'hideFilters' : {
                        'message' : 1,
                        'event' : 1,
                        'shift' : 0
                    },
                    'listFormat' : '" . $calFormat . "',
                    'calendarLinksHtml' : '" . $calendarLinks . "'
                });

                $('#" . $elemName . "-tabs').tabs({
                    'selected' : " . $this->_getDataOrDefault(array('selected')) . ",
                });

                $('#" . $elemName . "-tabs .message-todo-create').bind('click', function(event) {
                    event.preventDefault();
                    $('#" . $elemName . "-inbox').productivityWidget('displayMessageForm', null, 'todo');
                });

                $('#" . $elemName . "-tabs').bind('tabsselect', function(e, ui) {
                    var params = { 'selected' : ui.index }
                    routeAjaxRequest({$this->widgetData->id}, 'ajaxModifyWidgetSettings', params);
                });

                // handle a message created event in a way that switches tabs to the inbox
                $('#" . $elemName . "-tabs .productivityWidget-instance').bind('productivityWidgetMessageCreated', function(event, messageId) {
                    // switch to the inbox and do some animation calling attention to the new message
                    if (messageId > 0) {
                        // switch to the inbox
                        $('#" . $elemName . "-tabs').tabs('select', 0);

                        // scroll inbox to the message and animate
                        $('#" . $elemName . "-inbox div.viewport-content').scrollTo('li#message-' + messageId);
                        $('#" . $elemName . "-inbox li#message-' + messageId).hide();
                        $('#" . $elemName . "-inbox li#message-' + messageId).show('blind', {}, 500);
                    }
                });

                $('#" . $elemName . "-inbox').bind('productivityWidgetMessagesLoaded', function(event, data){
                	updateInboxUnreadCount();
                });
                $('#" . $elemName . "-inbox').bind('productivityWidgetMessageFieldModified', function(event, data){
                	updateInboxUnreadCount();
                });
            });
            </script>";

        $markup = "
        <div id='" . $elemName . "-tabs' class='productivity-widget-tt-tabs productivityWidget-tabs'>
            <div class='productivityWidget-tabs-buttons'>" . $messageCreateButton . "<a href='#' class='message-todo-create'>+ To-Do</a></div>
                <ul>
                    <li><a href='#" . $elemName . "-inbox'>Inbox <span class='unread_counter' id='" . $elemName . "-inbox-tab-text'></span></a></li>
                    <li><a href='#" . $elemName . "-calendar'>Calendar</a></li>
                    <li><a href='#" . $elemName . "-archive'>Archive</a></li>
                </ul>
                <div id='" . $elemName . "-inbox' class='productivity-widget-tt-inbox'></div>
                <div id='" . $elemName . "-calendar' class='productivity-widget-tt-calendar'></div>
                <div id='" . $elemName . "-archive' class='productivity-widget-tt-archive'></div>
            </div>
        ";

        $html = $js . $markup;
        return $html;
    }

    public function renderHeader(){
            return "";
    }

    	/**
	 * This method renders the widget container- specifically the title bar and all
	 * available tools.
	 *
	 * @return String containing the HTML for the widget container.
	 */
	public function renderContainer(){
		$widgetContents = $this->render();

		$header = $this->renderHeader();

		$html = <<<EOF
			<div id='widget_{$this->widgetData->id}_container' class='widget-container widget-container-blank'>
				<div id='widget_{$this->widgetData->id}_render' class='widget-render'>
					{$widgetContents}
				</div>
			</div>
EOF;

		return $html;
	}


    /* custom AJAX callbacks */
    public function parseViewportSettings($viewportElemName, $viewportParams) {
        // strip off the element name crud to get the unique part of the viewport name
        $viewportName =  str_replace($this->getNamespacedName('productivity-widget-tt') . '-', '', $viewportElemName);

        // go through viewport params and save them to data
        foreach($viewportParams as $key => $value) {
            switch ($key) {
                case 'filters':
                    foreach($value as $filter => $state) {
                        if (isset($this->data[$viewportName]['filters'][$filter])) {
                            $this->data[$viewportName]['filters'][$filter] = intval($state);
                        }
                    }
                    break;
                case 'sort':
                    $this->data[$viewportName]['sort'] = $value;
                    break;
            }
        }
    }

    /**
     * Modify the widget's settings (selected tab, filters, sort)
     *
     * @param array $params An array of parameters keyed as described below:
     *
     * array = (
        'global_property' => 'value',
        'viewport_name' => array(
            'viewport_property' => 'value'
        )
     )
     */
    public function ajaxModifyWidgetSettings($params) {
        $elemName = $this->getNamespacedName('productivity-widget-tt');
        foreach($params as $key => $value) {
            switch ($key) {
                case 'selected':
                    $this->data['selected'] = $value;
                    break;
                case $elemName . '-inbox':
                case $elemName . '-calendar':
                case $elemName . '-archive':
                    $this->parseViewportSettings($key, $value);
                    break;
            }
        }

        $this->widgetData->data = serialize($this->data);
        $this->widgetData->save();

        return true;
    }

    /**
     * Provide a list of messages for the current user. For now it provides all messages
     *
     * @param array $data Data sent by the ajax request
     */
    public function ajaxGetMessages($data = null) {
        // process incoming data
        if (isset($data['entityIds']) && isset($data['entityType'])) {
            if (is_array($data['entityIds'])) {
                $entityIds = $data['entityIds'];
                $entityType = $data['entityType'];
            } else {
                $entityIds = null;
                $entityType = $data['entityType'];
            }
        } else {
            $entityIds = null;
            $entityType = null;
        }

        if (isset($data['full']) && $data['full'] == TRUE) {
            $full = TRUE;
        } else {
            $full = FALSE;
        }

        if ($this->user->id) {
            $deliveryRepo = \Fisdap\EntityUtils::getRepository('MessageDelivery');

            // Get Message data if either NO IDs of either type are specified, or if message-specific IDs are specified
            $delivered = array();
            if ($entityIds == null || $entityType == 'message') {
                $delivered = $deliveryRepo->getMessagesByUser($this->user->id, $entityIds);
            }

            // In addition we want to get shift data, which is currently a separate query to legacy tables
            // results here are not actual messages, just data treated as pseudo messages.
            // get shifts if NO IDs of either type are specified, or if shift-specific IDs are specified
            $shifts = array();
            if ($entityIds == null || $entityType == 'shift' || $entityType == 'event') {
                if ($this->user->isInstructor() && $this->user->hasPermission('View Schedules')) {
                    // just one day worth of shifts for instructor, across the ENTIRE PROGRAM
                    $program = $this->user->getProgramId();
                    if ($program) {
                        $shifts = $deliveryRepo->getShiftPseudoMessagesByUser('program', $program, 1, $entityType, $entityIds);
                    }
                } else {
                    // get four days, just for this user
                    $shifts = $deliveryRepo->getShiftPseudoMessagesByUser('context', $this->user->getCurrentUserContext()->id, 4, $entityType, $entityIds);
                }
            }

            if (count($delivered) == 0 && count($shifts) == 0) {
                return false;
            }

            // Get proper messageDelivery entities. THis accounts for "true" messages in the Fisdap Messaging system
            $resultMsgs = new stdClass();
            foreach($delivered as $delivery) {
                // construct the message object as recognized by javascript
                $resultMsg = new stdClass();
                $resultMsg->id = $delivery['id'];
                $resultMsg->type = 'message';
                $resultMsg->title = $delivery['title'];
                $resultMsg->receivedDate = $delivery['updated']->format("Y-m-d H:i:s");
				//\Zend_Registry::get('logger')->debug('MyFisdap_Widgets_ProductivityWidgetThreeTab - Message delivery received date: ' . $resultMsg->receivedDate);
                $resultMsg->priority = $delivery['priority'];
                $resultMsg->deleted = 0;
                $resultMsg->read = $delivery['is_read'];
                $resultMsg->archived = $delivery['archived'];

                $resultMsg->subTypes = new StdClass();

                // Teaser. Make sure we snip HTML carefully
                if ($delivery['body'] == '') {
                    $resultMsg->teaser = '';
                } else {
                    $suffix = '';
                    if (substr($delivery['body'], 100, 1) !== FALSE) {
                        $suffix = '...';
                    }
                    // strip all but the allowed tags
                    $allowedTags = array('<a>', '<em>', '<strong>');
                    $filter = new \Fisdap\XssFilter($delivery['body'], $allowedTags);
                    $strippedBody = $filter->filter();
                    $resultMsg->teaser = substr($strippedBody, 0, 100) . $suffix;
                }

                // Full body ?
                if ($full) {
                    $resultMsg->body = $delivery['body'];
                }

                // derive formatted author
                if ($delivery['author_type'] == 'user' && $delivery['author'] == $this->user->id) {
                    $resultMsg->author = 'me';
                } else if ($delivery['author_type'] == 'system') {
                    $resultMsg->author = 'Fisdap Robot';
                } else {
                    $resultMsg->author = $delivery['author_first_name'] . ' ' . $delivery['author_last_name'];
                }

                // derive subtypes
                if ($delivery['completed'] != null) {
                    $resultMsg->type = 'todo';

                    $subType = new stdClass();
                    $subType->done = $delivery['completed'];

                    // full notes?
                    if ($full) {
                        $subType->notes = $delivery['notes'];
                    }

                    $resultMsg->subTypes->todo = $subType;
                }
                if ($delivery['due_start'] != null) {
                    $subType = new stdClass();
                    $subType->date = $delivery['due_start']->format("Y-m-d H:i:s");
                    $resultMsg->subTypes->due = $subType; // @todo timezone conversion??? $event->format() on entity is too heavy duty
                }
                if ($delivery['event_start'] != NULL) {
                    $resultMsg->type = 'event';

                    $subType = new stdClass();
                    $subType->date = $delivery['event_start']->format("Y-m-d H:i:s");
                    $resultMsg->subTypes->event = $subType; // @todo timezone conversion??? $event->format() on entity is too heavy duty
                }

                // add to messages object
                $resultMsgs->{$delivery['id']} = $resultMsg;
            }

            // Also get upcoming shifts. Thesse are not true messages, but we want them and it seems wasteful to replicate the data as messages
            foreach($shifts as $shift) {
                // If this is a scheduler shift (has an event_id), then it might be a duplicate and we want to compound duplicates
                $new = FALSE;
				if (is_numeric($shift['event_id']) && $shift['event_id'] > 0) {
                    $entityId = 'shiftevent_' . $shift['event_id'];
                    if (!isset($resultMsgs->{$entityId})) {
                        $new = TRUE;
                    }
                } else {
                    $entityId = 'shift_' . $shift['shift_id'];
                    $new = TRUE;
                }

                if ($new) {
                    // construct the message object as recognized by javascript
                    $resultMsg = new stdClass();
                    $resultMsg->id = $entityId;
                    $resultMsg->type = 'shift';
                    $resultMsg->title = $shift['site_abbreviation'] . ", " . $shift['base_name'];
                    $resultMsg->receivedDate = $shift['entry_time']->format("Y-m-d H:i:s");
                    $resultMsg->priority = 0;
                    $resultMsg->deleted = 0;
                    $resultMsg->read = 1;
                    $resultMsg->archived = 0;

                    // author
                    $resultMsg->author = 'Fisdap Robot';

                    // shift & event data
                    $resultMsg->subTypes = new StdClass();

                    $event = new StdClass();
                    $paddedTime = str_pad($shift['start_time'], 4, '0', STR_PAD_LEFT);
                    $hours = substr($paddedTime, 0, 2);
                    $minutes = substr($paddedTime, 2, 2);
                    $event->date = $shift['start_date'] . ' ' . $hours . ':' . $minutes . ':00';
                    $resultMsg->subTypes->event = $event;

                    $shiftData = new StdClass();
                    $shiftData->typeLabel = ucfirst($shift['type']);
                    $shiftData->icons = '';
                    $shiftData->students = array($shift['first_name'] . ' ' . $shift['last_name']);
                    $resultMsg->subTypes->shift = $shiftData;

                    // Teaser. Special format for shifts
                    if ($this->user->isInstructor()) {
                        $resultMsg->teaser = $this->_formatShiftTeaser($shift, $resultMsg->subTypes->shift->students);
                    } else {
                        $resultMsg->teaser = $this->_formatShiftTeaser($shift);
                    }

                    // Full body ?
                    if ($full) {
                        $resultMsg->body = $this->_formatShiftBody($shift, $resultMsg->subTypes->shift->students);
                    }

                    // add to messages object
                    $resultMsgs->{$entityId} = $resultMsg;
                } else {
                    // this is a scheduler shift that has already been added to the results. Instead of adding a duplicate, we just modify data
                    $resultMsgs->{$entityId}->subTypes->shift->students[] = $shift['first_name'] . ' ' . $shift['last_name'];
                    if ($full) {
                        $resultMsgs->{$entityId}->body = $this->_formatShiftBody($shift, $resultMsgs->{$entityId}->subTypes->shift->students);
                    }
                    if ($this->user->isInstructor()) {
                        $resultMsgs->{$entityId}->teaser = $this->_formatShiftTeaser($shift, $resultMsgs->{$entityId}->subTypes->shift->students);
                    } else {
                        $resultMsgs->{$entityId}->teaser = $this->_formatShiftTeaser($shift);
                    }
                }
            }

            return $resultMsgs;
        } else {
            return false;
        }
    }

    /**
     * Save a modified or new message.
     * Anything that goes through this function is assumed  to be user-supplied, so we run it through XSS checks if text
     *
     * @param array $message Data sent by the ajax request, in this case a message object
     */
    public function ajaxSaveMessage($message = null) {
	if (\Zend_Auth::getInstance()->hasIdentity() && $this->user->id > 0) {
            // existing or new message?
            if (isset($message['id']) && $message['id'] != 'new') {
                // check if the user has permission to modify this message.
                // first load the existing message
                $existDelivery = \Fisdap\EntityUtils::getEntity('MessageDelivery', $message['id']);
                // determine message type from the existing delivery
                if ($existDelivery->todo != NULL && $existDelivery->todo instanceof \Fisdap\Entity\Todo) {
                    $type = 'todo';
                } else if ($existDelivery->message->event != NULL && $existDelivery->message->event instanceof \Fisdap\Entity\Event) {
                    $type = 'event';
                } else {
                    $type = 'message'; //fall back to regular message
                }
                $canEditMessage = \Fisdap\Entity\Message::checkPermission('modify', $type, $existDelivery->message, $this->user);
                $canEditDelivery = $existDelivery->checkPermission();

                if ($canEditMessage || $canEditDelivery) {
                    // get the XSS filter running
                    $filter = new \Fisdap\XssFilter();

                    // modify the message!
                    // lookup this particular message deliveyr

                    // first, update hte updated value
                    if ($canEditMessage) {
                        $existDelivery->message->set_updated(new DateTime('now'));
                    }

                    // check if this message needs to be deleted OR UNdeleted. If so, special handling
                    if (isset($message['deleted'])) {
                        if ($canEditMessage) {
                            // Soft delete the message itself (and by consequence the associated deliveries)
                            $existDelivery->message->set_soft_delete($message['deleted']);
                        } else if ($canEditDelivery) {
                            // SOft delete only the delivery of the message
                            $existDelivery->set_soft_delete($message['deleted']);
                        }
                        $existDelivery->save();
                    }

                    // as long as the message is not being deleted, we can check for other properties
                    if ($message['deleted'] != 1) {
                        // look through properties to update
                        foreach($message as $key => $value) {
                            switch($key) {
                                // simple modifications to the message entity
                                case 'title':
                                    if ($canEditMessage) { $existDelivery->message->set_title($filter::checkPlain($value)); }
                                    break;
                                case 'body':
                                    if ($canEditMessage) {
                                        $filter->setInputString = $value;
                                        $existDelivery->message->set_body($filter->filter());
                                    }
                                    break;
                                // simple modifications to the messageDelivery entity
                                case 'priority':
                                    if ($canEditDelivery) { $existDelivery->set_priority($value); }
                                    break;
                                case 'read':
                                    if ($canEditDelivery) { $existDelivery->set_is_read($value); }
                                    break;
                                case 'archived':
                                    if ($canEditDelivery) { $existDelivery->set_archived($value); }
                                    break;
                                case 'priority':
                                    if ($canEditDelivery) { $existDelivery->set_priority($value); }
                                    break;

                                // subtype handling
                                case 'subTypes':
                                    foreach($value as $subtype => $subvalues) {
                                        switch ($subtype) {
                                            case 'todo':
                                                if ($canEditDelivery) {
                                                    if (isset($subvalues['notes'])) {
                                                        $filter->setInputString($subvalues['notes']);
                                                        $existDelivery->todo->set_notes($filter->filter());
                                                    }
                                                    if (isset($subvalues['done'])) {
                                                        $existDelivery->todo->set_completed($subvalues['done']);
                                                    }
                                                }
                                                break;
                                            case 'due':
                                                if ($canEditMessage && isset($subvalues['date'])) {
                                                    // @todo do we need to convert?
                                                    $dueDate = new \DateTime($subvalues['date']);
                                                    // are we creating a new subType or modifying existing?
                                                    if (isset($existDelivery->message->{$subtype})) {
                                                        $existDelivery->message->{$subtype}->set_start($dueDate);
                                                    } else {
                                                        // we need to create a new subtype entity
                                                        $due = new \Fisdap\Entity\Event();
                                                        $due->set_start($dueDate);
                                                        $existDelivery->message->set_due($due);
                                                    }
                                                }
                                                break;
                                            // @todo event handling
                                        }
                                    }
                                    break;
                            }
                        }

                        // save the messageDelivery
                        $existDelivery->save();
                    }
                } else {
                    return false; // unable to modify the message.
                }

            } else {
                $message['id'] = 'new';
                // check if the user has permission to create this type of message.
                // for now we only support creation of todo items in the widget that are hard-coded with the author as recipient
                $check = \Fisdap\Entity\Message::checkPermission('create', $message['type'], null, $this->user);

                // need permissions check to pass and $message to contain minimal info
                if ($check && !($message['title'] == ''&& $message['body'] == '')) {
                    // create the message!
                    $messageEntity = new \Fisdap\Entity\Message();
                    $messageEntity->set_title($message['title']);
                    $messageEntity->set_body($message['body']);
                    $messageEntity->set_author_type(3); //user account is the sender
                    $messageEntity->set_author($this->user);
                    $now = new \DateTime('now');
                    $messageEntity->set_updated($now);
                    $messageEntity->set_created($now);

                    // if due date
                    if (isset($message['subTypes']['due']['date'])) {
                        $dueDate = new \DateTime($message['subTypes']['due']['date']);
                        $due = new \Fisdap\Entity\Event();
                        $due->set_start($dueDate);
                        $messageEntity->set_due($due);
                    }

                    // if todo, send with subtypes. Othewrise just deliver.
                    //
                    if (isset($message['subTypes']['todo'])) {
                        $todo = new \Fisdap\Entity\Todo();
                        $todo->set_notes($message['subTypes']['todo']['notes']);
                        $todo->set_completed($message['subTypes']['todo']['done']);
                        $successfulDeliveries = $messageEntity->deliver(array($this->user), 1, $message['priority'], array('todo' => $todo));
                    } else {
                        $successfulDeliveries = $messageEntity->deliver(array($this->user), 1, $message['priority']);
                    }

                    // return the proper json structure to javascript for the created message
                    // for now we assume that JS UI users can only create proper messages
                    $result = $this->ajaxGetMessages(array('entityType' => 'message', 'entityIds' => array($successfulDeliveries[$this->user->id]->id)));

                    return $result;

                } else {
                    return false; // unabel to create the message
                }
            }
        }
    }

    /**
     * Hard-delete any pending soft deletions identified by the frontend.
     *
     * @param array $deliveries Array of delivery IDs submitted by JS as ones that should be hard deleted
     */
    public function ajaxDoPendingDeletions($deliveries) {
	if (\Zend_Auth::getInstance()->hasIdentity() && $this->user->id > 0) {
            // check through the supplied messages
            foreach($deliveries as $deliveryId) {
                // check if the user has permission to modify this message.
                // first load the existing message
                $existDelivery = \Fisdap\EntityUtils::getEntity('MessageDelivery', $deliveryId);

                // make sure this has already been marked as soft deleted
                if ($existDelivery->soft_delete == 1) {

                    // check permissions for editing message and/or delivery
                    if ($existDelivery->todo != NULL && $existDelivery->todo instanceof \Fisdap\Entity\Todo) {
                        $type = 'todo';
                    } else if ($existDelivery->message->event != NULL && $existDelivery->message->event instanceof \Fisdap\Entity\Event) {
                        $type = 'event';
                    } else {
                        $type = 'message'; //fall back to regular message
                    }
                    $canEditMessage = \Fisdap\Entity\Message::checkPermission('modify', $type, $existDelivery->message, $this->user);
                    $canEditDelivery = $existDelivery->checkPermission();

                    if ($canEditMessage) {
                        // Hard delete the message itself (and by consequence the associated deliveries)
                        $existDelivery->message->delete();
                    } else if ($canEditDelivery) {
                        // Hard delete only the delivery of the message
                        $existDelivery->delete();
                    }
                }
            }
        }
    }


    // $selector: $this->data['inbox']['filters']['message'] === array('inbox', 'filters', 'message')
    private function _getDataOrDefault($selector = array(), $useDefault = FALSE) {
        $data = FALSE;
        if (!empty($selector)) {
            // loop through parts of the selector successfully drilling deeper
            foreach($selector as $part) {
                if ($data) {
                    // we are partly down the selector list and have successfully found data so far
                    if (isset($data[$part])) {
                        $data = $data[$part];
                        continue;
                    } else {
                        // ran into a dead-end try again, but go straight for the defaults
                        return $this->_getDataOrDefault($selector, TRUE);
                    }
                } else {
                    if ($useDefault == TRUE) {
                        // we have been instructed to get the default value
                        $defaults = $this->getDefaultData();
                        if (isset($defaults[$part])) {
                            $data = $defaults[$part];
                            continue;
                        } else {
                            // wow, not even the default exists
                            return null;
                        }
                    } else if (isset($this->data[$part])) {
                        // we're on the first selector part and have found data
                        $data = $this->data[$part];
                        continue;
                    } else {
                        // ran into a dead-end try again, but go straight for the defaults
                        return $this->_getDataOrDefault($selector, TRUE);
                    }
                }
            }

            // if we got through the foreach, $data should be what we want.
            return $data;
        } else {
            return null;
        }
    }

    private function _formatShiftTeaser($shift, $students = array()) {
        $teaser = '';

        $teaser .= '<div class="message-shift-teaser-top">';

        // check some permissions useful for displaying icons:
        $role = $this->user->getCurrentRoleData();
        $program = $role->program;
        $now = new \DateTime();
        $nowString = $now->format('Y-m-d');

        // icons
        $teaser .= '<span class="message-shift-teaser-icons">';
        $icons = array(
            'schedulerShared' => '<img src="/images/icons/share.png" title="Shared shift" alt="Shared shift" />',
            'scheduler' => '<img src="/images/icons/Scheduler.png" title="Scheduler shift" alt="Scheduler shift" />',
            'student' => '<img src="/images/icons/Student.png" title="Student-created shift" alt="Student-created shift" />',
            'slotOpen' => '<img src="/images/icons/open.png" title="Open slot" alt="Open slot" />',
            'slotClosed' => '<img src="/images/icons/closed.png" title="Closed slot" alt="Closed slot" />',
            'slotInvisible' => '<img src="/images/icons/openI.png" title="Open invisible slot" alt="Open invisible slot" />',
            'more' => '<img src="/images/icons/seat_plus.png" title="Show more slots" alt="Show more slots" />',
        );
        if (is_numeric($shift['event_id']) && $shift['event_id'] > 0) {
            $teaser .= ($shift['share_program_name']) ? $icons['schedulerShared'] : $icons['scheduler'];
            // if total slots is more than 4, we want to avoid display bloat and thus should hide some
            $slotsCount = 0;
            $displaySlots = $displaySlotsHidden = '';
            for ($i = 0; $i < $shift['occupied_slots']; $i++) {
                if ($slotsCount < 4) {
                    $displaySlots .= ' ' . $icons['slotClosed'];
                } else {
                    $displaySlotsHidden .= ' ' . $icons['slotClosed'];
                }
                $slotsCount++;
            }
            // pick whether open slots are invisible or not
            if ($nowString < $shift['release_date'] || $nowString > substr($shift['expiration_date'], 0, 10) || ($shift['type'] == 'clinical' && $program->program_settings->student_pick_clinical == 0) || ($shift['type'] == 'lab' && $program->program_settings->student_pick_lab == 0) || ($shift['type'] == 'field' && $program->program_settings->student_pick_field == 0)) {
                if ($this->user->getCurrentRoleName() == 'instructor') {
                    $openIcon = ' ' . $icons['slotInvisible'];
                } else {
                    $openIcon = ''; // do not display
                }
            } else {
                $openIcon = '' . $icons['slotOpen'];
            }
            for($i = 0; $i < ($shift['total_slots'] - $shift['occupied_slots']); $i++) {
                if ($slotsCount < 4) {
                    $displaySlots .= ' ' . $openIcon;
                } else {
                    $displaySlotsHidden .= ' ' . $openIcon;
                }
                $slotsCount++;
            }
            // put the output together, shown and (initially) hidden
            $teaser .= $displaySlots;
            if ($displaySlotsHidden != '') {
                $teaser .= '<span class="message-shift-teaser-hidden-slots"><strong>' . $icons['more']  . '</strong><span>' . $displaySlotsHidden . '</span></span>';
            }
        } else {
            $teaser .= $icons['student'] . ' ' . $icons['slotClosed'];
        }
        $teaser .= '</span>';

        $teaser .= '<span class="message-shift-teaser-time">' . $shift['start_time'] . ' (' . $shift['hours'] . ' hrs)' . '</span>';
        $teaser .= '</div>';

        if ($shift['preceptor_name'] != '') {
            $teaser .= '<div class="message-shift-teaser-preceptor"><strong>Preceptor:</strong> ' . $shift['preceptor_name'] . '</div>';
        }

        if (!empty($students)) {
            $teaser .= '<div class="message-shift-teaser-students"><strong>Students:</strong> ' . implode(', ', $students) . '</div>';
        }

        return $teaser;
    }

    private function _formatShiftBody($shift, $students = array()) {

        $body = '<div class="message-shift-details">';

        $body .= '<div class="message-shift-details-site message-shift-details-item">' . ucfirst($shift['type']) . ' Shift -- ' . $shift['site_name'] . ', ' . $shift['base_name'] . '</div>';
        $startString = $shift['start_date'] . ' ' . substr(str_pad($shift['start_time'], 4, '0', STR_PAD_LEFT), 0, 2) . ':' . substr(str_pad($shift['start_time'], 4, '0', STR_PAD_LEFT), 2, 2) . ':00';
        $start = new \DateTime($startString);
		$endMilTime = (int)$shift['start_time'] + (int)(floor($shift['hours']) * 100) + (int)(($shift['hours'] - floor($shift['hours'])) * 60);
        $body .= '<div class="message-shift-details-time message-shift-details-item">' . $start->format('M j, Y') . ', ' . $shift['start_time'] . ' - ' . $endMilTime . '</div>';

        // add relevant links
        $utilityLinks = array();
        $isInstructor = $this->user->isInstructor();
        if (!$isInstructor) {
            if ($shift['event_id'] > 0) {
                // scheduler shift
                //$body .= '<p><a href="/oldfisdap/redirect?loc=scheduler/logic.html@Event_id=' . $shift['event_id'] . '">View in Fisdap Scheduler</a></p>';
				if ($this->user->getCurrentProgram()->scheduler_beta) {
	                $utilityLinks[] = '<a href="/scheduler/shift/details/event/' . $shift['event_id'] . '">View in Fisdap Scheduler</a>';
				} else {
	                $utilityLinks[] = '<a href="/oldfisdap/redirect?loc=index.html@target_pagename=scheduler/schedulercont.html@goToPage=logic.html%26name0=Event_id%26val0=' . $shift['event_id'] . '">View in Fisdap Scheduler</a>';
				}
            } else {
                // skills tracker shift
                $utilityLinks[]= '<a href="/skills-tracker/shifts/my-shift/shiftId/' . $shift['shift_id'] . '">Patient Care documentation</a>';
            }
        }

        // load shift so that we can add map link
        $shiftEntity = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shift['shift_id']);
        $mapLink = $this->view->mapLinkHelper($shiftEntity);
        if ($mapLink) {
            $utilityLinks[] = $mapLink;
        }

        if (!empty($utilityLinks)) {
            $body .= '<div class="message-shift-details-links">' . implode(' ', $utilityLinks) . '</div>';
        }

        $body .= '</div>';


        // @todo preceptor
        // @todo notes $body .= '<p>notes notes notes</p>';

        if ($isInstructor) {
            // instructor view
            $body .= '<div class="message-shift-details-students"><strong>Students Attending:</strong><div class="message-shift-details-students-list">' . implode(', ', $students) . '</div></div>';

        }


        return $body;
    }
}
