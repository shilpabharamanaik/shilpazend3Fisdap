(function($) {
    // Create productivityWidget plugin using jQuery's widget factory
    // http://wiki.jqueryui.com/w/page/12138135/Widget%20factory
    
    $.widget("fisdap.productivityWidget", {
        // INIT STUFF
        // These options will be used as defaults
        options: {
            listFormat : 'standard', // available options: standard, calendar4 and calendar1
            filters : { // available filters and whether they are on or off at init
                'message' : 1,
                'todo' : 1,
                'shift' : 0,
                'event' : 0,
                'archived' : 0
            },
            hideFilters : { // allows some filters to be hidden (prevents user from setting/unsetting them)
                'message' : 0,
                'todo' : 0,
                'shift' : 1,
                'event' : 1,
                'archived' : 1 
            },
            sort : "receivedDateDesc", // receivedDateDesc, logicalDateAsc, priority
            hashId : '',
            fisdapWidgetId : null,
            messageOrder : [], // array of message IDs in the order they should appear in this instance of the widget
            width : 0,
            iscroll : false,
            selectedMessages : [],
            undoQueue : { 'archived1' : [], 'deleted1' : [], 'archived0' : [] },
            calendarLinksHtml : ''
        },
        
        
        _create: function() {
            // The _create method is where you set up the widget
            
            // do browser detection: we care about certain mobile browsers re: overflow:auto support
            if (this.browser == null) {
                var ua = navigator.userAgent;
                if (ua.match(/(iPhone|iPod|iPad)/)) {
                    if (ua.match(/OS 4_/)) {
                        this.browser = 'ios4';
                    } else if (ua.match(/OS 5_/)) {
                        this.browser = 'ios5';
                    }
                } else if (ua.match(/BlackBerry/)) {
                    this.browser = 'blackberry';
                } else if (ua.match(/Android/)) {
                    this.browser = 'android';
                }
            }

            // give this widget a unique hash ID. Necessary for supporting UI elements that are out of the DOM scope of this.element
            // specifically jquery.ui's dialog. Want each widget to be as modular/isolated as possible
            this.hashId = Math.random().toString(36).substring(7);
            
            // set the width, if not explicitly set. necessary for computing calendar display columns
            if (this.options.width == 0) {
                this.options.width = $(this.element).parent().width();
            }
            
            // get messages
            if (typeof(window.productivityWidgetMessageRequestPending) == 'undefined') {
                window.productivityWidgetMessageRequestPending = false;
            }
            if ($.isEmptyObject(this.messages) && window.productivityWidgetMessageRequestPending == false) {
                window.productivityWidgetMessageRequestPending = true;
                this.element.html(this._renderLoadingStatus());
                routeAjaxRequest(this.options.fisdapWidgetId, 'ajaxGetMessages', {}, $.proxy(function(data){
                    this._processAjaxMessages(data);
                    window.productivityWidgetMessageRequestPending = false;
                    
                    // render the viewport
                    this.refreshViewport();
                    
                    // tell other viewports that they are allowed to go ahead and refresh now that messages are received
                    $('.productivityWidget-instance').not(this.element).productivityWidget('refreshViewport');

                }, this));
                //console.log('just ran getmessages');
                //this.testData();
            }
            
            // Attach window unload event that takes care of hard deletion cleanup
            // Hard deletion trigger via unload event
            // but only need to bind this once per window, so check for existing event
            var unloadAlreadyBound = false;
			
            var windowEvents = $._data($(window)[0], "events");

            if (windowEvents && windowEvents.unload.length > 0) {
                $.each(windowEvents.unload, $.proxy(function(i, val) {
                    if (val.namespace == 'productivityWidget') {
                        unloadAlreadyBound = true;
                    }
                }, this));
            }
			
            if (unloadAlreadyBound == false) {
                $(window).bind('unload.productivityWidget', $.proxy(function(event) {
                    if (this.hardDeletePending.length > 0) {
                        // make ajax request to server to delete messages
                        $.ajaxSetup({async:false});
                        routeAjaxRequest(this.options.fisdapWidgetId, 'ajaxDoPendingDeletions', this.hardDeletePending);
                    }
                }, this));
            }
                                    
            // create the markup to be used for modal interactions
            this._renderModal();
            
            // add an identifying class to the target element
            $(this.element).addClass('productivityWidget-instance');
            
        },
        
        // VARS
        /* messages format:
            messages : {
                1 : {
                    id : 1, //server
                    type : 'type',
                    title : 'title'
                    author : 'author', // server
                    receivedDate : '2012-03-13 11:23:45', // server
                    teaser : 'teaser teaser teaser...', // server
                    priority : 0, // integer
                    deleted : 0, // bool
                    read : 0, // bool
                    archived : 0, // bool
                    subTypes : {
                        todo : {
                            done : 0, // bool
                            notes : '', // string
                            //todoId : 1 //deprecated for now
                        },
                        due : {
                            date : '2012-03-13 11:23:45',
                            //eventId : 1 //deprecated for now
                        },
                        event : {
                            date : '2012-03-13 11:23:45',
                            //eventId : 1 //deprecated for now
                        },
                        shift : {
                            typeLabel : 'Field',
                            icons : '<img src=... />',
                            students : []
                            //shiftId : 1 // deprecated for now
                        }
                    }
                }
            }
        */
        messages : {},
        
        filterLabels : {
            'message' : 'Messages',
            'todo' : 'To-Dos',
            'shift' : 'Shifts',
            'event' : 'Events',
            'archived' : 'Archived'
        },
        sortLabels : {
            'logicalDateAsc' : 'Date',
            'receivedDateDesc' : 'New',
            'priority' : 'Priority'
        },
        typeLabels : {
            'message' : 'Message',
            'todo' : 'To-Do',
            'event' : 'Event',
            'shift' : 'Shift'
        },
        browser : null,
        hardDeletePending : [],
        
        // PUBLIC METHODS
        getMessages : function(filter, returnCount) {
        	var filteredMessages = {};
        	
        	switch(filter){
	        	case 'read':
	    			for(var messageIndex in this.messages){
	    				var msg = this.messages[messageIndex];
	    				
	    				if(msg.read == 1 && msg.type=='message'){
	    					filteredMessages[messageIndex] = msg;
	    				}
	    			}
    			break;
    			
        		case 'unread':
	    			for(var messageIndex in this.messages){
	    				var msg = this.messages[messageIndex];
	    				
	    				if(msg.read == 0 && (msg.type=='message' || msg.type=='todo')){
	    					filteredMessages[messageIndex] = msg;
	    					//console.log(msg)
	    				}
	    			}
    			break;
        		default:
        			filteredMessages = this.messages;
        	}

        	if(returnCount == true){
        		curCount = 0;
        		for(messageIndex in filteredMessages){
        			curCount++;
        		}
        		
        		return curCount;
        	}else{
        		return filteredMessages;//this.messages;
        	}
        },
        
        /*
         * Set filters by sending an array of the filter types and their statuses
         *
         * @param {object} filters An object containing a property for each filter that should be affected, with boolean status values
         *
         * filters = {
         *   message : 1,
         *   todo : 0,
         *   event : 1
         * }
         */
        filter: function(filters) {
            var currentFilters = this.options.filters;
            $.each(filters, function(filter, status) {
                if (typeof(currentFilters[filter]) != 'undefined') {
                    // set the submitted filter status
                    currentFilters[filter] = status;
                }
            });
            
            // update options
            this.option('filters', currentFilters);
        },
        
        /*
         * Set the sorting of currently-visible messages
         *
         * @param {string} sort The string name of the sort option.
         */
        sort : function(sort) {
            if (typeof(this.sortLabels[sort]) != 'undefined') {
                // update options
                this.option('sort', sort);
            }
        },
        
        /*
         * Refresh the viewport of this productivity widget, re-checking filters, sorters, and message list. attaching events.
         */
        refreshViewport : function() { //@todo might need in future to define which viewport (main, events, archive)
            // set a loading status
            this.element.html(this._renderLoadingStatus());
            
            // sort messages
            this._sortMessages();

            
            // render the widget viewport
            var widgetHtml = '<div class="viewport-controls">';
            if (this.options.listFormat == 'standard') {
                // only the standard listFormat includes sorters, calendars do not neet sorting
                widgetHtml = widgetHtml + this._renderSorters();
            }
            widgetHtml = widgetHtml + this._renderFilters() + '</div>' + '<div class="viewport-content list-format-' + this.options.listFormat + '">' + this._renderList() + '</div>';
            if (this.options.listFormat == 'standard') {
                // only standard list view gets the utility tray
                widgetHtml = widgetHtml + this._renderUtilityTray();
            }
            if (this.options.listFormat == 'calendar1' || this.options.listFormat == 'calendar4') {
                // only calendar list views get the additional links at the bottom
                widgetHtml = widgetHtml + this.options.calendarLinksHtml;
            }
            this.element.html(widgetHtml);

            // attach events
            this._attachViewportEvents();
            
            // make sure mobile overflow:auto scrolling works
            if (this.browser == 'android' || this.browser == 'ios4') {
                setTimeout($.proxy(function () {
                    this.options.iscroll = new iScroll('viewport'); // @todo the viewport ID won't work, should be hashed prob
                }, this), 0);
            }
        },

        
        /*
         * Refresh display of a message
         * It may no longer fit our filters, or its properties may have changed
         */
        refreshMessage : function(messageId) {
            if (typeof(this.messages[messageId]) != 'undefined') {
                // check if it still matches present filters, and display/hide appropriately
                if (this._checkFilters(this.messages[messageId])) {
                    var exists = $('li#message-' + messageId, this.element);
                    if ($(exists).length > 0) {
                        // re-render the message and re-attach events
                        $('li#message-' + messageId, this.element).replaceWith(this._renderMessage(this.messages[messageId]));
                        this._attachMessageEvents(messageId);
                    } else {
                        // refresh the viewport, because we need to insert the message and reset the order
                        this.refreshViewport();
                    }
                } else {
                    // DESTROY the message, it doesn't match filters
                    $('li#message-' + messageId, this.element).remove();
                }
            }
        },
         
        /*
         * Create a new message
         *
         * @param {Object} message The message object to be saved
         *
         * @return {Object} message the Message that was created
         */
        createMessage : function(message) {
            // set some defaults
            if (typeof(message.type) == 'undefined') {
                message.type = 'message';
            }
            if (typeof(message.priority) == 'undefined') {
                message.priority = 0;
            }
            if (typeof(message.archived) == 'undefined') {
                message.archived = 0;
            }
            if (typeof(message.read) == 'undefined') {
                message.read = 1;
            }
            message.deleted = 0; // new message can't start out deleted
            
            // send the message to the server for permisisons check, ID, author, etc. and persistence
            routeAjaxRequest(this.options.fisdapWidgetId, 'ajaxSaveMessage', message, $.proxy(function(data) {
                // Put the resulting message in the array
                this._processAjaxMessages(data);
                var messageId = 0;
                $.each(data, function(i, message) {
                    messageId = message.id;
                });
                
                // refresh viewport and tell other instances to do the same
                // @todo more sophisticated handling of whether to refresh or not... if this message doesn't meet current filters we should instead display a notice
                this.refreshViewport();
                
                // refresh other viewports
                $('.productivityWidget-instance').not(this.element).productivityWidget('refreshViewport');
                
                // fire a custom event to allow any parent script (like the 3-tab widget) to react to it.
                $(this.element).trigger( 'productivityWidgetMessageCreated', [ messageId ] );
                
            }, this));

            
            
            // TESTING ONLY: assign server values with test data
            /*
            var highId = 0;
            $.each(this.messages, function(id, message) {
                if (parseInt(id) > highId) {
                    highId = id;
                }
            });
            message.id = parseInt(highId) + 1;
            message.author = 'me';
            var now = new Date();
            message.receivedDate = now;
            message.teaser = message.body;
                        
            // Insert the message into message pool
            this.messages[message.id] = message;
            */
            
        },
        
        /*
         * Modify a message property
         * Handles checking if message exists, setting property, triggering markup changes and signalling other widgets
         *
         * @param {String} messageId The ID of the message
         * @param {Object} data Object where keys are properties to be changed and values are the new value
         *
         * @return {Object} Message if successful, FALSE if not
         */
        modifyMessage : function(messageId, data) {
            var othersRefreshMessage = false; // do we need to signal other productivity widgets about the change?
            var othersRefreshViewport = false;
            
            var thisRefreshMessage = false; // do we need to refresh the message LI?
            var thisRefreshViewport = false; // do we need to refresh the entire viewport?
            
            var submitData = { subTypes : {} } // data we will submit to the server via AJAX
            
            if (typeof(this.messages[messageId]) != 'undefined') {
                $.each(data, $.proxy(function(key, value) {
                    // do different things dependign on the property being changed.
                    // properties that can affect filtering or LI markup only need refreshMessage
                    // properties that affect sorting need refreshViewport
                    switch (key) {
                        case 'title':
                            this.messages[messageId].title = submitData.title = value;
                            thisRefreshMessage = othersRefreshMessage = true;
                            break;
                        case 'body':
                            this.messages[messageId].body = submitData.body = value;
                            break;
                        case 'priority':
                        case 'read':
                            if (value != this.messages[messageId][key] && (value == 1 || value == 0)) {
                                // modify if it is a real change, and boolean
                                this.messages[messageId][key] = submitData[key] = value;
                                thisRefreshMessage = othersRefreshMessage = true;
                                
                                if(value == 1){
                            		$(this.element).trigger( 'productivityWidgetMessageFieldModified', {messageId: messageId, action: 'marked-read' });
                            	}
                            } else if (value == 'toggle') {
                                // OR, we're being instructed to toggle the existing value
                                if (this.messages[messageId][key] == 0) {
                                    this.messages[messageId][key] = submitData[key] = 1;
                                } else {
                                    this.messages[messageId][key] = submitData[key] = 0;
                                }
                                thisRefreshMessage = othersRefreshMessage = true;
                            }
                            if (key == 'priority' && this.options.sort == 'priority') { // priority can affect sorting
                                thisRefreshMessage = false;
                                thisRefreshViewport = true;
                            }
                            break;
                        case 'deleted':
                        case 'archived':
                            // only modify if it is a real change, and boolean
                            if (value != this.messages[messageId][key] && (value == 1 || value == 0)) {
                                this.messages[messageId][key] = submitData[key] = value;
                                othersRefreshViewport = true;
                                if (value == 1 || (value == 0 && key == 'archived' && this.options.filters.archived == 1)) {
                                    // we are deleting or archiving or UNarchiving the message
                                    // don't refresh the message, instead replace with a warning/notice
                                    thisRefreshViewport = false;
                                    $('li#message-' + messageId, this.element).hide();
                                    
                                    /* PRIOR INLINE DELETE/UNDELETE FUNCTIONALITY COMMENTED OUT... as requested
                                      
                                      //thisRefreshMessage = false;
                                    $('li#message-' + messageId, this.element).css('background-color', '#FFEFD9').html(this._renderMessageNotice(key, value));
                                    // bind action to the newly created notice so this action can be undone
                                    $('li#message-' + messageId + ' .message-notice-' + key + ' a', this.element).bind('click.productivityWidget', $.proxy(function(event) {
                                        var type = $(event.target).parent('span').attr('class').replace('message-notice-', '');
                                        if (type == 'archived') {
                                            this.modifyMessage(messageId, { 'archived' : 0 });
                                        } else if (type == 'deleted') {
                                            this.modifyMessage(messageId, { 'deleted' : 0 });
                                        }
                                        
                                        // we need to clear this otu of the appropriate undo queue
                                        var index = $.inArray(messageId, this.options.undoQueue[type]);
                                        if (index != -1) {
                                            this.options.undoQueue[type].splice(index, 1);
                                        }
                                        
                                        // if both undo queues are empty (possibly because of clearing above), kill the status message
                                        if (this.options.undoQueue.archived.length == 0 && this.options.undoQueue.deleted == 0) {
                                            $('div.utility-tray-status', this.element).html('');
                                        }
                                        
                                        event.preventDefault();  
                                    }, this));
                                    */
                                } else {
                                    // we are UNdeleting or UNarchiving
                                    thisRefreshMessage = othersRefreshViewport = true;
                                }
                            }
                            break;
                        // subtype data is passed as an object
                        case 'todo':
                            submitData.subTypes.todo = {}
                            if (typeof(value.done) != 'undefined') {
                                if (value.done != this.messages[messageId].subTypes.todo.done && (value.done == 0 || value.done == 1)) {
                                    this.messages[messageId].subTypes.todo.done = submitData.subTypes.todo.done = value.done;
                                    thisRefreshMessage = othersRefreshMessage = true;
                                }
                            }
                            if (typeof(value.notes) != 'undefined') {
                                this.messages[messageId].subTypes.todo.notes = submitData.subTypes.todo.notes = value.notes;
                            }
                            break;
                        case 'event':
                        case 'due':
                            submitData.subTypes[key] = {}
                            if (typeof(value.date) != 'undefined') {
                                if (value.date != '') {
                                    // we assume here that the incoming date is a string from the Zend datepicker form element
                                    submitData.subTypes[key].date = value.date;
                                    
                                    // make user that the subType is created on the message
                                    if (typeof(this.messages[messageId].subTypes[key]) == 'undefined') {
                                        this.messages[messageId].subTypes[key] = {}
                                    }
                                    // convert the date value that is stored in JS to a JS date object
                                    this.messages[messageId].subTypes[key].date = this._convertDatePickerDate(value.date);
                                    othersRefreshViewport = true;
                                    if (this.options.sort == 'logicalDateAsc' || this.options.listFormat == 'calendar4' || this.options.listFormat == 'calendar1') {
                                        thisRefreshViewport = true;
                                    }
                                }
                            }
                            break;
                    }
                    
                    
                }, this));
                
                // Do any UI refreshing we need based on changes
                if (thisRefreshMessage == true) {
                    this.refreshMessage(messageId);
                }
                if (othersRefreshMessage == true) {
                    $('.productivityWidget-instance').not(this.element).productivityWidget('refreshMessage', messageId);
                }
                if (thisRefreshViewport == true) {
                    this.refreshViewport();
                }
                if (othersRefreshViewport == true) {
                    $('.productivityWidget-instance').not(this.element).productivityWidget('refreshViewport');
                }
                    
                // Check if submitData contains info. If so, submit to the server via AJAX
                if ($.isEmptyObject(submitData) == false) {
                    // make sure it has at least message ID and type
                    submitData.id = messageId;
                    submitData.type = this.messages[messageId].type;
                    
                    // if deleted == 1 then we need to set the pendingSoftDelete flag so the unload event can trigger full deletion
                    //console.log(submitData);
                    if (submitData.deleted == 1) {
                        this.hardDeletePending.push(submitData.id);
                    } else if (submitData.deleted == 0 && this.hardDeletePending.length > 0) {
                        $.each(this.hardDeletePending, $.proxy(function(i, val) {
                            if (val == submitData.id) {
                                this.hardDeletePending.splice(i, 1);
                            }
                        }, this));
                    }
                    
                    // make sure that we drop subTypes property if it is still empty
                    if ($.isEmptyObject(submitData.subTypes)) {
                        delete submitData.subTypes;
                    }
                    
                    routeAjaxRequest(this.options.fisdapWidgetId, 'ajaxSaveMessage', submitData);

                }
                return this.messages[messageId];
            
            } else {
                return false;
            }
        },
        
        /*
         * Display a message form as a modal dialog. 
         *
         * @param {string} messageId The ID of the message. If null, we are creating a new message
         * @param {string} msgType Optional: The type of message to be created (if messageId is null)
         */
        displayMessageForm : function(messageId, msgType) {
            var message = {}
            
            // check if we are loading an existing message or creating new
            if (messageId == null) {
                // creating a new message
                var now = new Date();
                message = {
                    'id' : 'new',
                    'author' : 'me', // @todo need to be able to compare 
                    'type' : msgType,
                    'receivedDate' : now, 
                    'title' : '',
                    'body' : '',
                    'priority' : 0,
                    'deleted' : 0,
                    'read' : 1,
                    'archived' : 0                    
                }
                switch(msgType) {
                    case 'todo':
                        message.subTypes = {
                            'todo' : {
                                'done' : 0,
                                'notes' : '',
                            }
                        }
                        break;
                    // @todo event
                }
                
                // we have the protoype message ready, so go ahead and render
                this._renderMessageForm(message);
                
            } else if (typeof(this.messages[messageId]) != 'undefined') {
                // load the message
                message = this.messages[messageId];
                
                // if we don't have the message body, let's fetch it
                if (typeof(message.body) == 'undefined') {
                    // @todo loading icon
                    var requestData = {
                        'entityType' : 'message',
                        'entityIds' : [ message.id ],
                        'full' : 1
                    }
                        
                    // handle the special case of the shift pseudo-message
                    if (message.type == 'shift') {
                        // this message's ID is either prefixed with shift_ or shiftevent_ to denote whether it is a single shift or representation of an shift event (multiple shifts bound by event_id)
                        var entityType = message.id.split('_');
                        if (entityType[0] == 'shift') {
                            requestData.entityIds = [ message.id.replace(/shift_/, '') ] ;
                            requestData.entityType = 'shift';
                        } else if (entityType[0] == 'shiftevent') {
                            requestData.entityIds = [ message.id.replace(/shiftevent_/, '') ];
                            requestData.entityType = 'event';
                        }
                    }

                    routeAjaxRequest(this.options.fisdapWidgetId, 'ajaxGetMessages', requestData, $.proxy(function(data){
                        // load message from server into the messages object and render
                        this._processAjaxMessages(data);
                        this._renderMessageForm(this.messages[messageId]);
                    }, this), this.element.attr('id'));
                } else {
                    // otherwise just render immediately
                    this._renderMessageForm(message);
                }
            } 
        },
        
        
        // PRIVATE METHODS
        
        // process incoming messages from AJAX request into the class' messages array
        _processAjaxMessages : function(messages) {
            $.each(messages, $.proxy(function(i, message) {
                // change dates into Javascript dates
                message.receivedDate = this._convertMysqlDate(message.receivedDate);
                if (typeof(message.subTypes.due) != 'undefined') {
                    message.subTypes.due.date = this._convertMysqlDate(message.subTypes.due.date);
                }
                if (typeof(message.subTypes.event) != 'undefined') {
                    message.subTypes.event.date = this._convertMysqlDate(message.subTypes.event.date);
                }
                
                // insert into the messages array
                this.messages[message.id] = message;
            }, this));
            
            $(this.element).trigger( 'productivityWidgetMessagesLoaded', {});
        },
        
        _sortMessages : function() {
            // if the options.messageOrder array is empty, or doesn't match num of messages, we need to fill it up
            var messageKeys = this._objectKeys(this.messages);
            if (this.options.messageOrder.length == 0 || this.options.messageOrder.length != messageKeys.length) {
                this.options.messageOrder = messageKeys;
            }
            
            // perform the sort
            switch(this.options.sort) {
                case 'receivedDateDesc':
                    this.options.messageOrder.sort($.proxy(this._sortMessagesReceivedDateDesc, this));
                    break;
                case 'priority':
                    this.options.messageOrder.sort($.proxy(this._sortMessagesPriorityDesc, this));
                    break;
                case 'logicalDateAsc':
                    this.options.messageOrder.sort($.proxy(this._sortMessagesLogicalDateAsc, this));
                    break;
            }
        },
        _sortMessagesReceivedDateDesc : function(thisMsgId, thatMsgId) {
            if (this.messages[thisMsgId].receivedDate > this.messages[thatMsgId].receivedDate) {
                return -1
            } else if (this.messages[thisMsgId].receivedDate < this.messages[thatMsgId].receivedDate) {
                return 1;
            } 
            return 0;
        },
        _sortMessagesPriorityDesc : function(thisMsgId, thatMsgId) {
            if (this.messages[thisMsgId].priority > this.messages[thatMsgId].priority) {
                return -1
            } else if (this.messages[thisMsgId].priority < this.messages[thatMsgId].priority) {
                return 1;
            } 
            return 0;
        },
        _sortMessagesLogicalDateAsc : function(thisMsgId, thatMsgId) {
            // this sort operates on different date values depending on the message type
            var thisDate = this._chooseLogicalMessageDate(this.messages[thisMsgId]);
            var thatDate = this._chooseLogicalMessageDate(this.messages[thatMsgId]);
            if (thisDate < thatDate) {
                return -1
            } else if (thisDate > thatDate) {
                return 1;
            } 
            return 0;
        },
        
        /*
         * Return the date value that is the most logical single representation of this message
         * Depends on message type, data
         *
         * @param {object} message A message
         *
         * @returns {string} String date
         */
        _chooseLogicalMessageDate : function(message) {
            if (message.type == 'message') {
                return message.receivedDate;
            } else if (message.type == 'event' || message.type == 'shift') {
                return message.subTypes.event.date;
            } else if (message.type == 'todo') {
                // only some todo items have a due date
                if (typeof(message.subTypes.due) != 'undefined') {
                    return message.subTypes.due.date;
                } else {
                    return message.receivedDate;
                }
            }
        },
                
        _attachViewportEvents : function() {
            // filters
            $('.filters input', this.element).bind('change.productivityWidget', $.proxy(function(event) {
                // change current filter options
                var filters = {}
                $('.filters input', this.element).each(function() {
                    if ($(this).is(':checked')) {
                        filters[$(this).val()] = 1;
                    } else {
                        filters[$(this).val()] = 0;
                    }
                });

                this.filter(filters);
            }, this));
            
            // sorters
            $('.sorters select[name="sorter"]', this.element).bind('change.productivityWidget', $.proxy(function(event) {
                // change current filter option
                var sort = $('.sorters select[name="sorter"]', this.element).val();
                this.sort(sort);
            }, this));
            
            // Utility Tray buttons
            $('div.utility-tray-controls a.button', this.element).bind('click.productivityWidget', $.proxy(function(event) {
                event.preventDefault();
                
                if ($(event.currentTarget).hasClass('button-archive')) {
                    var action = 'archived';
                    var value = 1;
                } else if ($(event.currentTarget).hasClass('button-delete')) {
                    var action = 'deleted';
                    var value = 1;
                } else if ($(event.currentTarget).hasClass('button-unarchive')) {
                    var action = 'archived';
                    var value = 0;
                }
                            
                // only act if the number of selected messages is nonzero
                if (this.options.selectedMessages.length > 0) {
                    $.each(this.options.selectedMessages, $.proxy(function(i, messageId) {
                        // archive each message...
                        // @todo change this to a batch ajax request instead of many individual
                        if (action == 'archived') {
                            this.modifyMessage(messageId, { 'archived' : value });
                        } else if (action == 'deleted') {
                            this.modifyMessage(messageId, { 'deleted' : value });
                        }
                    }, this));
                    
                    // set the undo queue so this action may be undone
                    this.options.undoQueue[action + value] = this.options.selectedMessages;
                                        
                    // set a status message and bind undo event
                    $('div.utility-tray-status', this.element).html(this._renderMessageNotice(action, value, this.options.selectedMessages.length));
                    $('div.utility-tray-status .message-notice-' + action + value + ' a', this.element).bind('click.productivityWidget', $.proxy(function(event) {
                        event.preventDefault();
                        
                        // get the action
                        if ($(event.target).parent().hasClass('message-notice-archived1')) {
                            var action = 'archived';
                            var newValue = 0;
                        } else if ($(event.target).parent().hasClass('message-notice-deleted1')) {
                            var action = 'deleted';
                            var newValue = 0;
                        } else if ($(event.target).parent().hasClass('message-notice-archived0')) {
                            var action = 'archived';
                            var newValue = 1;
                        }
                        
                        // go through the undo queue for this action and perform undos
                        $.each(this.options.undoQueue[action + value], $.proxy(function(i, messageId) {
                            if (action == 'archived' && newValue == 0) {
                                this.modifyMessage(messageId, { 'archived' : 0 });
                            } else if (action == 'deleted' && newValue == 0) {
                                this.modifyMessage(messageId, { 'deleted' : 0 });
                            } else if (action == 'archived' && newValue == 1) {
                                this.modifyMessage(messageId, { 'archived' : 1 });
                            }
                        }, this));
                        
                        // render new status message
                        $('div.utility-tray-status', this.element).html(this._renderMessageNotice(action, newValue, this.options.undoQueue[action + value].length));
                        
                        // empty the undo queue
                        this.options.undoQueue[action + value] = []
                        
                    }, this));
                    
                    // clear out the selected array and disable buttons
                    this.options.selectedMessages = []
                    $('div.utility-tray-controls a.button', this.element).addClass('button-disabled');
                }
            }, this));
            
            // Attach events that are associated with the messages in the viewport, since message DOM has changed as well
            this._attachMessageEvents(null);
        },
        
        /*
         * Attach events to messages in the DOM. Can be passed with messageId = null to affect all messages
         */
        _attachMessageEvents : function(messageId) {
            if (typeof(messageId) == 'undefined') {
                messageId = null;
            }
            
            if (messageId == null) {
                var context = this.element;
            } else {
                var context = $('li#message-' + messageId, this.element);
            }
            
            // extra slots control
            $('span.message-shift-teaser-hidden-slots span', context).hide();
            $('span.message-shift-teaser-hidden-slots strong', context).click(function(event) {
                $(this).hide();
                $(this).next('span').show();
                
                event.preventDefault();
                event.stopPropagation();
            });
            
            // message selector control
            $('a.message-selector', context).bind('click.productivityWidget', $.proxy(function(event) {
                event.preventDefault();
                var messageId = $(event.target).closest('li.message').attr('id');
                messageId = messageId.replace('message-', '');
                
                // toggle selected status
                var key = $.inArray(messageId, this.options.selectedMessages)
                if (key == -1) {
                    // add to array
                    var checked = true;
                    this.options.selectedMessages.push(messageId);
                } else {
                    // remove from array
                    var checked = false;
                    this.options.selectedMessages.splice(key, 1);
                }
                
                // disable buttons if there are no selected Messages
                if (this.options.selectedMessages.length == 0) {
                    $('div.utility-tray-controls a.button', this.element).addClass('button-disabled');
                } else {
                    $('div.utility-tray-controls a.button', this.element).removeClass('button-disabled');
                }
            
                // replace the image
                $('img', event.currentTarget).replaceWith(this._renderCheckboxImage(checked, 'Selected', 'Select this message'));
            }, this));
            
            // message Delete control
            $('a.message-delete', context).bind('click.productivityWidget', $.proxy(function(event) {
                var messageId = $(event.target).closest('li.message').attr('id');
                messageId = messageId.replace('message-', '');
                this.modifyMessage(messageId, { 'deleted' : 1});
                
                event.preventDefault();
            }, this));
            
            // message todo Completion control
            $('a.message-todo-done', context).bind('click.productivityWidget', $.proxy(function(event) {
                var messageId = $(event.target).closest('li.message').attr('id');
                messageId = messageId.replace('message-', '');
                
                // toggle the existing value
                if (this.messages[messageId].subTypes.todo.done == 0) {
                    var done = 1;
                } else {
                    var done = 0;
                }
                this.modifyMessage(messageId, { 'todo' : {'done' : done } });
                
                event.preventDefault();
            }, this));
            
            // message View/edit link
            $('a.message-inner', context).bind('click.productivityWidget', $.proxy(function(event) {
                // load the message via AJAX
                event.preventDefault;
                var messageId = $(event.target).closest('li.message').attr('id');
                messageId = messageId.replace('message-', '');
                this.displayMessageForm(messageId);
                
                return false;
            }, this));
            
            // message Priority control
            $('a.message-priority', context).bind('click.productivityWidget', $.proxy(function(event) {
                event.preventDefault();
                
                var messageId = $(event.target).closest('li.message').attr('id');
                messageId = messageId.replace('message-', '');
                
                // @todo in the future priority may have multiple levels, but for now assume boolean that can be toggled
                this.modifyMessage(messageId, { 'priority' : 'toggle'});
            }, this));
            
            // If we are in one of the calendar styles, do message-controls slide up/down
            if (this.options.listFormat == 'calendar4' || this.options.listFormat == 'calendar1') {
                $('a.message-inner', context).parent().bind('mouseenter.productivityWidget', $.proxy(function(event) {
                    $('div.message-controls', $(event.target).closest('li.message')).slideDown();
                }, this));
                $('a.message-inner', context).parent().bind('mouseleave.productivityWidget', $.proxy(function(event) {
                    $('div.message-controls', $(event.target).closest('li.message')).slideUp();
                }, this));
           }
            
        },
        
        _attachMsgModalEvents : function() {
            // message-display modal HTML is rendered, attach events to it
            var modal = $('#productivityWidget-msgModal-' + this.hashId);
            
            // when modal is closed, we want to re-set its height and width
            $(modal).bind('dialogclose', function(event, ui) {
                var options = { 'width' : 500, 'height' :  'auto' }
                $(event.target).dialog('option', options);
            });
                        
            // message Delete control
            $('a.message-controls-delete', modal).bind('click.productivityWidget', $.proxy(function(event) {
                var modal = $(event.target).parents('.productivityWidget-msgModal');
                var messageId = $(modal).find('input[name="messageId"]').val();
                $(modal).dialog('close');
                this.modifyMessage(messageId, { 'deleted' : 1 });
                
                event.preventDefault();
            }, this));
            
            // message Archive control
            $('a.message-controls-archive', modal).bind('click.productivityWidget', $.proxy(function(event) {
                var modal = $(event.target).parents('.productivityWidget-msgModal');
                var messageId = $(modal).find('input[name="messageId"]').val();
                $(modal).dialog('close');
                
                // toggle the existing value
                if (this.messages[messageId].archived == 1) {
                    this.modifyMessage(messageId, { 'archived' : 0 });
                } else {
                    this.modifyMessage(messageId, { 'archived' : 1 });
                }
                
                event.preventDefault();
            }, this));
            
            // message Priority control
            $('a.message-priority', modal).bind('click.productivityWidget', $.proxy(function(event) {
                var modal = $(event.target).parents('.productivityWidget-msgModal');
                var messageId = $(modal).find('input[name="messageId"]').val();

                // toggle priority in form element
                var currentPriority = $('input[name="priority"]', modal).val();
                if (currentPriority == 1) {
                    var newPriority = 0;
                } else {
                    var newPriority = 1;
                }
                $('input[name="priority"]', modal).val(newPriority);
                
                // for existing message, modify immediately
                if (messageId != 'new') {
                    // modify the message
                    // @todo in the future priority may have multiple levels, but for now assume boolean that can be toggled
                    var message = this.modifyMessage(messageId, { 'priority' : 'toggle' });
                    var newPriority = message.priority;
                }
                
                // change the markup in the modal... event.target is often the IMG contained by the a.message-priority
                if ($(event.target).is('img')) {
                    $(event.target).parent().html(this._renderPriorityControl(newPriority));
                } else {
                    $(event.target).html(this._renderPriorityControl(newPriority));
                }
                
                event.preventDefault();
            }, this));
            
            // message Todo Completion control
            $('input[name="message-modal-todo-completed"]', modal).bind('change.productivityWidget', $.proxy(function(event) {
                var modal = $(event.target).parents('.productivityWidget-msgModal');
                var messageId = $(modal).find('input[name="messageId"]').val();
                
                // toggle the todo
                if ($(event.target).is(':checked')) {
                    var done = 1;
                } else {
                    var done = 0;
                }
                this.modifyMessage(messageId, { 'todo' : {'done' : done }});
            }, this));
            
            // todo date picker
            $('input[name="due-date"]', modal).datepicker({});
            
            // message Save handler
            $('form[name="productivityWidget-msgModal"]', modal).bind('submit.productivityWidget', $.proxy(function(event) {
                event.preventDefault();
                
                var modal = $(event.target).parents('.productivityWidget-msgModal');
                var messageId = $(modal).find('input[name="messageId"]').val();                
                
                // If the message's id == 'new', then it is a new message to be saved
                if ($('input[name="messageId"]', modal).val() == 'new') {
                    var data = {
                        'type' : $('input[name="messageType"]', modal).val(), 
                        'title' : $('input[name="title"]', modal).val(),
                        'priority' : parseInt($('input[name="priority"]', modal).val()),
                        'subTypes' : {}
                    }
                    
                    var bodyField = $('textarea[name="body"]', modal);
                    if (bodyField.length > 0) {
                        data.body = $(bodyField).val();
                    }
                    
                    if (data.type == 'todo') {
                        // set defaults
                        data.subTypes.todo = {
                            'done' : 0,
                            'notes' : ''
                        }
                    }
                    
                    // check for todo/notes
                    var notes = $('textarea[name="todo-notes"]', modal);
                    if (notes.length > 0) {
                        data.subTypes.todo = {
                            'notes' : $(notes).val(),
                            'done' : 0
                        }

                    }
                    
                    // check for due date
                    var due = $('input[name="due-date"]', modal);
                    if (due.length > 0) {
                        var dueString = $(due).val();
                        if (dueString != '') {
                            data.subTypes.due = {
                                'date' : dueString
                            }
                        }
                    }
                    
                    // check for event
                    // @todo
                    this.createMessage(data);
                    
                } else {
                    // modify existing message's properties
                    var data = {
                        'type' : $('input[name="messageType"]', modal).val(), 
                        'priority' : parseInt($('input[name="priority"]', modal).val())
                    }
                    
                    // check to see if basic parameters were edited
                    if ($('input[name="title"]', modal).length > 0) {
                        data.title = $('input[name="title"]', modal).val();
                    }
                    if ($('textarea[name="body"]', modal).length > 0) {
                        data.body = $('textarea[name="body"]', modal).val();
                    }

                    
                    // Todo Notes
                    if ($('textarea[name="todo-notes"]', event.target).length > 0) {
                        var notes = $('textarea[name="todo-notes"]', event.target).val();
                        data.todo = { 'notes' : notes }
                    }
                    
                    // check for due date
                    var due = $('input[name="due-date"]', modal);
                    if (due.length > 0) {
                        var dueString = $(due).val();
                        data.due = {
                            'date' : dueString
                        }
                    }
                    
                    // check for event
                    // @todo
                    
                    if (!$.isEmptyObject(data)) {
                        this.modifyMessage(messageId, data);
                    }
                }
                
                // close the dialog modal
                $(modal).dialog('close');
            }, this));
            
            // close message link
            $('a.message-modal-close', modal).bind('click.productivityWidget', $.proxy(function(event) {
                $(modal).dialog('close');
                event.preventDefault();
            }, this));
        },
        
        _renderLoadingStatus : function() {
            var html = 'Loading...';
            
            return html;
        },
        
        _renderMessageForm : function(message) {
            // set productiivtyWidget-msgModal context
            var modal = $('#productivityWidget-msgModal-' + this.hashId);
            
            // set message ID, priority and type
            $('input[name="messageId"]', modal).val(message.id);
            $('input[name="priority"]', modal).val(message.priority);
            $('input[name="messageType"]', modal).val(message.type);
            
            // should we display form buttons or not?
            var displaySubmit = false;
            
            // should we allow this user to modify message components?
            if (message.type == 'todo' && message.author == 'me') {
                var allowMessageEdit = true;
            } else {
                var allowMessageEdit = false;
            }
            
            // should we allow this user to modify messageDelivery components?
            if (message.author == 'me') {
                var allowDeliveryEdit = true;
            } else {
                var allowDeliveryEdit = false;
            }
            
            // set content/UI
            if (allowMessageEdit) {
                displaySubmit = true;
                $('h2.message-title span.message-title-title', modal).html('<label for="title">Title </label><input type="textfield" name="title" value="' + message.title + '" />');
            } else {
                $('h2.message-title span.message-title-title', modal).html(message.title);
                $('h2.message-author', modal).text('By: ' + message.author);
            }
            $('h2.message-title span.message-title-controls', modal).append('<a href="#" class="message-priority">' + this._renderPriorityControl(message.priority) + '</a>');
            $('h2.message-date span.message-date-date', modal).text(this._renderDate(message.receivedDate));
            if (message.body != '') {
                $('div.message-body', modal).html(message.body);
            }
            if (message.archived == 1) {
                $('a.message-controls-archive', modal).text('Unarchive'); // chagne this to an unarchive link
            } else if (message.id == 'new') {
                $('a.message-controls-archive', modal).parent().remove(); // hide archive link's LI if message already archived, or if new
            }
            if (message.id == 'new') {
                $('a.message-controls-delete', modal).parent().remove(); // no need to delete a new message
                $('a.message-modal-close', modal).text('Cancel'); // change label to cancel
            }
            // if message has a sub-type, add its specific controls/info
            switch(message.type) {
                case 'todo':
                    // due
                    if (allowMessageEdit) { 
                        // display editable due date field
                        if (typeof(message.subTypes.due) != 'undefined') {
                            var dueDefault = this._renderDate(message.subTypes.due.date, ['month', 'date', 'year'], '/'); // '03/08/2012';
                        } else {
                            var dueDefault = '';
                        }
                        var due = '<div class="message-todo-due"><strong>Due</strong> <input type="text" name="due-date" value="' + dueDefault + '"></div>';
                    } else if (typeof(message.subTypes.due) != 'undefined') {
                        // display due date uneditable
                        var due = '<div class="message-todo-due"><strong>Due: </strong>' + this._renderDate(message.subTypes.due.date) + '</div>';
                    }
                    $('div.message-subTypes', modal).append(due);
                   
                    // notes
                    if (allowDeliveryEdit) {
                        var notes = '<div class="message-todo-notes"><h3>Notes</h3><textarea name="todo-notes" cols="40" rows="4">' + message.subTypes.todo.notes + '</textarea></div>';
                        displaySubmit = true;
                    } else {
                        var notes = '<div class="message-todo-notes"><h3>Notes</h3><div>' + message.subTypes.todo.notes + '</div></div>';
                    }
                    
                    if (message.subTypes.todo.done == 1) {
                        var checked = 'checked="checked"';
                    } else {
                        var checked = '';
                    }
                    $('div.message-subTypes', modal).append(notes);
                    
                    // completion
                    if (message.id != 'new') {
                        // only add the completed checkbox if the message is an existing message
                        var completed = 'Done<input type="checkbox" name="message-modal-todo-completed" ' + checked + ' />';
                        $('span.message-title-controls', modal).prepend(completed);
                    }
                                        
                    // hide the message reeeived date
                    $('h2.message-date', modal).hide();
                    
                    break;
                case 'event':
                    // event date
                    var eventDate = '<div class="message-event-date"><strong>Date & Time: </strong>' + this._renderDate(message.subTypes.event.date) + ' ' + this._renderDate(message.subTypes.event.date, ['hour12', 'minute'], ':') + this._renderDate(message.subTypes.event.date, ['ampm'], '') + '</div>';
                    $('div.message-subTypes', modal).append(eventDate);
                    
                    // hide the message received date
                    $('h2.message-date', modal).hide();

                    break;
                case 'shift':
                    // hide all controls. Shifts are currently read-only
                    $('span.message-title-controls, ul.message-controls', modal).hide();
                    
                    // event date - commented out becase this is now formatted differently and included in the message.body returned from ajax
                    //var eventDate = '<div class="message-event-date"><strong>Date & Time: </strong>' + this._renderDate(message.subTypes.event.date) + ' ' + this._renderDate(message.subTypes.event.date, ['hour12', 'minute'], ':') + this._renderDate(message.subTypes.event.date, ['ampm'], '') + '</div>';
                    // $('div.message-subTypes', modal).append(eventDate);
                    
                    // hide the message received date, title and author
                    $('h2.message-date, h2.message-author, span.message-title-title', modal).hide();
                    
                    break;
                
            }
            
            // add submit button if we have form elements
            if (displaySubmit) {
                var submitButton = '<input type="submit" name="message-todo-save" value="Save" />';
                $('div.message-buttons', modal).html(submitButton);
                
                // move the cancel link up so it displays alongside the save button
                var msgTarget = $('div.message-buttons', modal);
                $('<span>&nbsp&nbsp&nbsp&nbsp</span>').prependTo(msgTarget);
                $('a.message-modal-close', modal).text('Cancel').css({'position' : 'static', 'display' : 'inline' }).prependTo(msgTarget);
            }
            
            // attach events
            this._attachMsgModalEvents();
            
            // mark the message as read if necessary
            if (message.read == 0) {
                this.modifyMessage(message.id, { 'read' : 1 });
            }
            
            // set title and open the dialog window
            $(modal).dialog('option', 'title', this.typeLabels[message.type]).dialog('open');
        },
        
        
        /*
         * Render a notice that replaces a message in the message list
         *
         * @param {string} property The property that is being changed that requires a notification
         * @param {string} value The new value of the property
         * @param {integer} number The number of items affected
         *
         * @return {string} html
         */
        _renderMessageNotice : function(property, value, number) {
            if (typeof(number) == 'undefined') {
                var number = 1;
            }
            
            if (number < 2) {
                var noun = 'this item';
            } else {
                var noun = number + ' items';
            }
            
            switch (property) {
                case 'deleted':
                    var text = 'You deleted ' + noun;
                    break;
                case 'archived':
                    var text = 'You archived ' + noun;
                    break;
            }
            
            var html = '<span class="message-notice-' + property + value + '">' + text + ' <a href="#">Undo</a></span>';
            
            // override in certian cases
            if (typeof(value) != 'undefined') {
                if ((property == 'archived' || property == 'deleted') && value == 0) {
                    // if we are in the archive, and the message was just UNdeleted, we need to say "returned to the archive"
                    // this text logic is not real bulletproof...
                    if (this.options.filters.archived == 1 && property == 'deleted') {
                        var placeNoun = 'Archive';    
                    } else {
                        var placeNoun = 'Inbox';
                    }
                    
                    if (number < 2) {
                        var hasVerb = 'has';
                    } else {
                        var hasVerb = 'have';
                    }
                    html = '<span class="message-notice-' + property + value + '">' + noun.substr(0, 1).toUpperCase() + noun.substr(1) + ' ' + hasVerb + ' been returned to the ' + placeNoun + '</span>';
                }
            }
            
            return html;
        },
                
        _renderList : function() {
            // todo start here: add calendar options (4 day and 1 day)
            var html = '';
            
            switch(this.options.listFormat) {
                case 'standard':
                    var widget = this; // get a copy of the widget's "this" so we can use its functions within $.each()
                    
                    // iterate through list
                    var list = '';
                    $.each(this.options.messageOrder, function(i, msgId) {
                        var message = widget.messages[msgId];
                        if (widget._checkFilters(message)) {
                            list = list + widget._renderMessage(message);
                        }
                    });
                    
                    if (list.length > 0) {
                        var html = '<ul class="message-list">' + list + '</ul>';
                    } else {
                        var html = '<div class="message-list-empty">No items were found matching your filters.</div>';
                    }
                    
                    break;
                
                case 'calendar1':
                    // prepare a list object with properties for two columns, 0 and 1
                    var list = { 0 : '', 1 : '' }
                    
                    // go through messages and assign them alternately to each column (first msg to column 0, second to col 1, third to col 0, etc.)
                    var currentCol = 0;
                    var today = new Date();
                    today.setHours(0);
                    today.setMinutes(0);
                    today.setSeconds(0);
                    today.setMilliseconds(0);
                    $.each(this.messages, $.proxy(function(i, message) {
                        if (this._checkFilters(message)) {
                            if (message.type == 'shift') {
                                // we assume shift messages are only fetched for today, no need to double check date
                                list[currentCol] = list[currentCol] + this._renderMessage(message);
                            } else if (message.type == 'todo' && typeof(message.subTypes.due) != 'undefined') {
                                // only show todos that have a due date and that date is TODAY
                                if (message.subTypes.due.date.getTime() == today.getTime())  {
                                    list[currentCol] = list[currentCol] + this._renderMessage(message);
                                } 
                            }
                        }
                        if (currentCol == 0) {
                            currentCol = 1;
                        } else {
                            currentCol = 0;
                        }
                    }, this));
                    
                    // go through the list object and construct HTML
                    // divine column width from widget width
                    // was 129
                    var colWidth = ((this.options.width - 100) / 2);
                    $.each(list, $.proxy(function(i, messages) {
                        html = html + '<div class="calendar-list-column" style="width: ' + colWidth + 'px;" ><ul class="message-list">' + messages + '</ul></div>';
                    }, this));
                    
                    // set title
                    var now = new Date();
                    var date = this._renderDate(now, ['monthName', 'date'], ' ');
                    html = '<h3 class="date-heading">Today: ' + date + '</h3>' + html;

                    break;
                
                case 'calendar4':
                    // prepare a list obect with properties for each of the four dates to be displayed
                    var list = { }
                    for (i = 0; i < 4; i++) {
                        var now = new Date();
                        now.setDate(now.getDate() + i);
                        list[this._renderDate(now, ['monthName', 'date'], ' ')] = '';
                    }
                    
                    // check through messages and find those that match filters, is a todo/event, and fall within the 4-day span
                    var today = new Date();
                    today.setHours(0);
                    today.setMinutes(0);
                    today.setSeconds(0);
                    var fourDays = new Date();
                    fourDays.setDate(fourDays.getDate() + 3);
                    fourDays.setHours(23);
                    fourDays.setMinutes(59);
                    fourDays.setSeconds(59);
                    $.each(this.messages, $.proxy(function(i, message) {
                        if (this._checkFilters(message)) {
                            if (message.type == 'todo' && typeof(message.subTypes.due) != 'undefined') {
                                // two equivalent dates will not satisfy the <= comparison (WTF?)
                                // so we add some seconds to fix it
                                message.subTypes.due.date.setSeconds(1);
                                if (message.subTypes.due.date >= today && message.subTypes.due.date <= fourDays) {
                                    var key = this._renderDate(message.subTypes.due.date, ['monthName', 'date'], ' ');
                                    list[key] = list[key] + this._renderMessage(message);
                                }
                            } else if (message.type == 'event' || message.type == 'shift') {
                                message.subTypes.event.date.setSeconds(1);
                                if (message.subTypes.event.date >= today && message.subTypes.event.date <= fourDays) {
                                    var key = this._renderDate(message.subTypes.event.date, ['monthName', 'date'], ' ');
                                    list[key] = list[key] + this._renderMessage(message);
                                }
                            }
                        }
                    }, this));
                    
                    // go through the list object and construct HTML
                    // divine column width from widget width
                    // was 129
                    var colWidth = ((this.options.width - 150) / 4);
                    var i = 1;
                    $.each(list, $.proxy(function(date, messages) {
                        html = html + '<div class="calendar-list-column" style="width: ' + colWidth + 'px;" ><h3 class="date-heading">' + date + '<br />';
                        if (i == 1) {
                            html = html + 'Today';
                        } else {
                            html = html + '&nbsp';
                        }
                        html = html + '</h3><ul class="message-list">' + messages + '</ul></div>';
                        i++;
                    }, this));
                    
                    break;
            }
            return html;
        },
        
        /*
         * Generate a date
        
        /*
         * Check a message against the system's filters
         *
         * @param {Object} message The Message to be checked
         *
         * @return {Boolean} True if the message matches the current filters
         */
        _checkFilters : function(message) {
            // only display if corresonding message-type filter is turned on
            // and message is NOT deleted
            // and message is either archived, or archived filter is OFF
            if (
                message.deleted == 0 &&
                this.options.filters[message.type] &&
                (
                    (message.archived == 1 && this.options.filters.archived == 1) ||
                    (message.archived == 0 && this.options.filters.archived == 0)
                )
            ) {
                return true;
            } else {
                return false;
            }
        },
        
        /*
         * Format a JS date in a consistent fashion
         * 
         */
        _renderDate : function(date, parts, glue) {
            if (typeof(parts) == 'undefined') {
                var parts = ['month', 'date', 'year']  // other options include 'monthName', 'dayName', 'hour', 'minute'
            }
            if (typeof(glue) == 'undefined') {
                var glue = '-';
            }
            
            var monthNames = [ "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December" ];
            var dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']

            var resultParts = [];
            $.each(parts, function(i, val) {
                switch(val) {
                    case 'month':
                        var thisPart = ('0' + (date.getMonth() + 1)).slice(-2);
                        break;
                    case 'monthName':
                        var thisPart = monthNames[date.getMonth()];
                        break;
                    case 'date':
                        var thisPart = ('0' + date.getDate()).slice(-2);
                        break;
                    case 'dayName':
                        var thisPart = dayNames[date.getDay()];
                        break;
                    case 'year':
                        var thisPart = date.getFullYear();
                        break;
                    case 'hour':
                        var thisPart = ('0' + date.getHours()).slice(-2);
                        break;
                    case 'hour12':
                        if (date.getHours() > 12) {
                            var thisPart = (date.getHours() - 12);
                        } else {
                            var thisPart = date.getHours();
                        }
                        if (thisPart == '00') {
                            thisPart = '12';
                        }
                        break;
                    case 'ampm':
                        if (date.getHours() > 11) {
                            var thisPart = 'pm';
                        } else {
                            var thisPart = 'am';
                        }
                        break;
                    case 'minute':
                        var thisPart = ('0' + date.getMinutes()).slice(-2);
                        break;
                }
                resultParts.push(thisPart);
            });
            
            return resultParts.join(glue);
        },
        
        _renderMessage : function(message) {
            var todoIsLate = false;
            var cssClasses = ['message'];
            if (message.read == 0) {
                cssClasses.push('unread');
            }
            // add classes needed by subTypes
            $.each(message.subTypes, function(type, values) {
                if (type == 'todo') {
                    cssClasses.push('todo');
                    // is this todo complete?
                    if (values.done == 1) {
                        cssClasses.push('todo-completed');
                    } else if (typeof(message.subTypes.due) != 'undefined') {
                        // check if the todo is OVERDUE and INCOMPLETE
                        var today = new Date();
                        today.setHours(0);
                        today.setMinutes(0);
                        today.setSeconds(0);
                        today.setMilliseconds(0);
                        if (message.subTypes.due.date.getTime() < today.getTime()) {
                            todoIsLate = true;
                            cssClasses.push('todo-late');
                        }
                    }
                } else if (type == 'event') {
                    cssClasses.push('event');
                } else if (type == 'due') {
                    cssClasses.push('todo-due');
                } else if (type == 'shift') {
                    cssClasses.push('shift-' + message.subTypes.shift.typeLabel.toLowerCase());
                    cssClasses.push('shift');
                }
            });
            cssClasses = cssClasses.join(' ');
            
            switch(this.options.listFormat) {
                case 'standard':
                    var html = '<li id="message-' + message.id + '" class="' + cssClasses + '">' + this._renderMessageSelector(message.id) + this._renderMessageControls(message) + '<a class="message-inner" href="#"><span class="message-title">' + message.title + '</span>';
                    if (message.type == 'todo') {
                        if (todoIsLate == true) {
                            html = html + '<span class="message-todo-late">Past due</span>';    
                        }
                    }
                    html = html + '<span class="message-teaser">' + message.teaser + '</span></a></li>';
                    break;
                case 'calendar4':
                case 'calendar1':
                    if (message.type == 'todo') {
                        var prefix = 'Due: ';
                    } else {
                        var prefix = '';
                    }
                    if (message.type == 'shift') {
                        var title = message.title
                        var teaser = '<div class="message-shift-teaser-misc">'  + message.teaser + '</div>';
                    } else {
                        var title = message.title;
                        var teaser = message.teaser;
                    }
                    var html = '<li id="message-' + message.id + '" class="' + cssClasses + '"><a class="message-inner" href="#"><span class="message-title">' + prefix + title + '</span><span class="message-teaser">' + teaser + '</span></a></li>';
                    break;
            }

            return html;
        },
        
        _renderMessageSelector : function(messageId) {
            if ($.inArray(messageId, this.options.selectedMessages) > -1) {
                var checked = true;
            } else {
                var checked = false;
            }
            var html = '<a class="message-selector" href="#">' + this._renderCheckboxImage(checked, 'Selected', 'Select this message') + '</a>';
            
            return html;
        },
        
        _renderMessageControls : function(message) {
            var html = '';
            
            //todo
            if (message.type == 'todo' ) {
                if (message.subTypes.todo.done == 1) {
                    var checkedClass = 'message-todo-done-checked';
                } else {
                    var checkedClass = '';
                }
                var checkedMarkup = this._renderCheckboxImage(message.subTypes.todo.done, 'Un-complete this To-do', 'Complete this To-do');
                html = html + '<a href="#" id="message-todo-done[' + message.id + ']" class="message-todo-done ' + checkedClass + '">Done ' + checkedMarkup + '</a>';
            }
            
             // priority
            html = html + '<a href="#" class="message-priority">' + this._renderPriorityControl(message.priority) + '</a>';
            //html = html + this._renderPriorityControl(message.priority);
           
            //delete -- commented out by request!
            //html = html + '<a href="#" class="message-delete"><img src="/images/icons/delete.png" title="Delete this message" alt="Delete this message" style="width:26px; height: 26px" /></a>';
            //html = html + '<img class="message-delete" src="/images/icons/delete.png" title="Delete this message" alt="Delete this message" style="width:26px; height: 26px" />';
            
            html = '<div class="message-controls">' + html + '</div>';
            
            return html;
        },
        
        _renderPriorityControl : function(priority) {
            if (priority == 0) {
                var icon = 'star_empty.png';
                var title = 'Prioritize this message';
            } else {
                var icon = 'star_full.png';
                var title = 'De-prioritize this message';
            }

            return '<img src="/images/icons/' + icon + '" title="' + title + '" alt="' + title + '" style="width:20px; height: 20px" />';
        },
        
        _renderCheckboxImage : function (checked, checkedText, uncheckedText) {
            if (typeof(checkedText) == 'undefined') {
                checkedText = 'Uncheck this';
            }
            if (typeof(uncheckedText) == 'undefined') {
                uncheckedText = 'Check this';
            }
            
            if (checked === true || checked == '1' || checked == 1) {
                return '<img src="/images/icons/checkbox_checked.png" title="' + checkedText + '" alt="' + checkedText + '" />';
            } else {
                return '<img src="/images/icons/checkbox.png" title="' + uncheckedText + '" alt="' + uncheckedText + '" />';
            }
        },
        
        _renderFilters: function() {
            var html = '<div class="filters">';
            var widget = this;

            $.each(this.options.filters, function(filter, status) {
                // check if this filter should be hidden
                if (widget.options.hideFilters[filter] == 0) {
                    if (status == 1) {
                        var checked = 'checked="checked"';
                    } else {
                        var checked = '';
                    }
                    html =  html + '<input type="checkbox" name="filter[' + filter + ']" value="' + filter + '" ' + checked + ' /> <label for="filter[' + filter + ']" class="filter-' + filter + '"> ' + widget.filterLabels[filter] + '</label> ';
                }
            });
            html = html + '</div>';
            
            return html;
        },
        
        _renderSorters: function() {
            var widget = this;
            var html = '<div class="sorters"><label for="sorter">Sort by</label> <select name="sorter">';
            $.each(this.sortLabels, function(sort, label) {
                html = html + '<option value="' + sort + '" ';
                if (widget.options.sort == sort) {
                    html = html + 'SELECTED="SELECTED" ';
                }
                html = html + '>' + label + '</option>';
            });
            
            html = html + '</select></div>';
            
            return html;
        },
        
        _renderUtilityTray : function() {
            var html = '<div class="utility-tray"><div class="utility-tray-controls">' + this._renderUtilityControls() + '</div><div class="utility-tray-status"></div></div>';
            
            return html;
        },
        
        _renderUtilityControls : function() {
            var html = '';
            // don't put the archive button in a viewport that has the archive filter enabled and hidden
            if (this.options.filters.archived == 0 && this.options.hideFilters.archived == 1) {
                html = html + '<a href="#" class="button button-archive button-disabled">Archive</a>';
            } else if (this.options.filters.archived == 1 && this.options.hideFilters.archived == 1) {
                html = html + '<a href="#" class="button button-unarchive button-disabled">Un-archive</a>';
            }
            html = html + '<a href="#" class="button button-delete button-disabled">Delete</a>';
            
            return html;
        },
                
        /*
         * Render the modal element and prep it for use by jquery.ui Dialog plugin
         */
        _renderModal : function() {
            var content = this._renderModalDefaultContent();
            
            $(this.element).append('<div id="productivityWidget-msgModal-' + this.hashId + '" class="productivityWidget-msgModal">' + content + '</div>');
            $("#productivityWidget-msgModal-" + this.hashId, this.element).dialog({
                    // maxHeight : 500,
                    maxWidth: 800,
                    minWidth: 500,
		    modal: true,
                    autoOpen : false,
                    close : $.proxy(function(event, ui) {
                        // return the modal content ot default state after close
                        // this way subtypes can append without worrying about prior state
                        $('#productivityWidget-msgModal-' + this.hashId).html(this._renderModalDefaultContent());
                    }, this)
		});
        },
        _renderModalDefaultContent : function() {
            var content = '<form name="productivityWidget-msgModal"><input name="messageId" type="hidden" value="" /><input name="priority" type="hidden" value="" /><input name="messageType" type="hidden" value="" /><div class="message-contents"><div class="message-meta"><h2 class="message-date">Sent: <span class="message-date-date"></span></h2><h2 class="message-author"></h2></div><h2 class="message-title"><span class="message-title-title"></span><span class="message-title-controls"></span></h2><div class="message-body"></div><div class="message-subTypes"></div><div class="message-buttons"></div></div><ul class="message-controls"><li><a href="#" class="message-controls-archive">Archive</a></li><li><a href="#" class="message-controls-delete">Delete</a></li></ul><a href="#" class="message-modal-close">Close</a></form>';
            
            return content;
        },
        /*
         * Compatability wrapper for Object.keys, which is only in JS5 and up
         */
        _objectKeys : function(obj) {
            if (!Object.keys) {
                Object.keys = function (obj) {
                    var keys = [],
                        k;
                    for (k in obj) {
                        if (Object.prototype.hasOwnProperty.call(obj, k)) {
                            keys.push(k);
                        }
                    }
                    return keys;
                };
                return Object.keys(obj);
            } else {
                return Object.keys(obj);
            }
        },
         
        // date from MySQL date column is a string in the format 2007-06-05 15:26:02
        _convertMysqlDate : function(date) {
            var regex=/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9]) (?:([0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?$/;
            var parts=date.replace(regex,"$1 $2 $3 $4 $5 $6").split(' ');
            return new Date(parts[0],parts[1]-1,parts[2],parts[3],parts[4],parts[5]);  
        },
        
        // date from the Zend datepicker form element is a string in the format 04/03/2012
        _convertDatePickerDate : function(date) {
            var dateParts = date.split('/');
            return new Date(dateParts[2], (parseInt(dateParts[0]) - 1), dateParts[1]);
        },
        
        /* FUNCTIONS FOR TESTING / DEVELOPMENT ONLY */
        _fakejax: function(requestName) {
            var response = {
                title : '',
                body : '',
                subTypes : {}
            }
            switch(requestName) {
                case 'messageView':
                    response.body = 'Body text body text body text body text. Body text body text body text body text. Body text body text body text body text. Body text body text body text body text. Body text body text body text body text. Body text body text body text body text. Body text body text body text body text. <strong>Body text</strong> <a href="http://www.google.com">and linke to google</a>.';
                    break;
            }
            
            return response;
        },
        _randDate: function(beforeDate, afterDate) {
            if (typeof(beforeDate) == 'undefined') {
                var beforeDate = 28;
            }
            var randDateNum = Math.floor(Math.random()*beforeDate);
            if (typeof(afterDate) != 'undefined') {
                if (randDateNum < afterDate) {
                    randDateNum = afterDate + 1;
                }
            }
            // 2012 = -2208917073922
            // 2012 =
            var aDate = new Date();
            aDate.setDate(randDateNum);
            aDate.setFullYear(2012);
            aDate.setMonth(2);
            
            return aDate;
            //return '19' + Math.floor(Math.random()*100) + '-0' + Math.floor(Math.random()*10) + '-0' + Math.floor(Math.random()*100) + ' 0' + Math.floor(Math.random()*10) + ':00:00';
        },
        
        testData: function() {
            // if a global set of test messages is available, use that. Otherwise generate some nonsense!
            if (typeof(window.messages) != 'undefined') {
                this.messages = window.messages;
            } else {
                // generate some test data for the demo
                var types = { 0 : 'message', 1 : 'todo', 2 : 'event' }
                var words = { 0 : 'John', 1 : 'Terry', 2 : 'Jenny', 3 : 'Melissa', 4 : 'sent', 5 : 'lent', 6 : 'said', 7 : 'read', 8 : 'a book', 9 : 'a ship', 10 : 'a shovel', 11 : 'a house' }
                
                for(i = 1; i < 25; i++) {
                    var type = types[Math.floor(Math.random()*3)];
                    
                    var title = '';
                    for (t = 1; t < (Math.floor(Math.random()*12) + 3); t++) {
                        if (t > 1) {
                            title = title + " ";
                        }
                        title = title + words[Math.floor(Math.random()*12)];
                    }
                    
                    var now = new Date();
                    
                    var message = {
                        id : i,
                        type : type,
                        title : title,
                        author : words[Math.floor(Math.random()*12)] + " " + words[Math.floor(Math.random()*12)],
                        receivedDate : this._randDate(now.getDate()),
                        teaser : 'blah blah blah blah...',
                        deleted: 0,
                        priority: Math.round(Math.random()),
                        read : 0,
                        archived : 0,
                        subTypes : {}
                    }
                    
                    switch(type) {
                        case 'todo':
                            message.subTypes.todo = {
                                done : 0,
                                notes : '',
                                todoId : 't' + i
                            }
                            message.subTypes.due = {
                                date : this._randDate(28, message.receivedDate.getDate()),
                                eventId : 'e' + i
                            }
                            break;
                        case 'event':
                            message.subTypes.event = {
                                date : this._randDate(28, message.receivedDate.getDate()),
                                eventId : 'e' + i
                            }
                            break;
                    }
                    
                    // add message to to the class's collection of messages
                    this.messages[message.id] = message;
                }
            }
        },
        
        
        // MAGIC METHODS
        _setOption: function(key, value) {
            // Use the _setOption method to respond to changes to options
            // calling the prototype actually sets the defaults
            $.Widget.prototype._setOption.apply(this,arguments);
            
            // @todo ajax save changes
                    
            // anything special we need to do dependent on options changing
            switch(key) {
                case "filters":
                    this.refreshViewport();
                    
                    // save change of filters to the backend
                    if (this.options.fisdapWidgetId != null) {
                        var viewportId = $(this.element).attr('id');
                        var params = {}
                        params[viewportId] = {
                                'filters' : this.options.filters
                            }
                        routeAjaxRequest(this.options.fisdapWidgetId, 'ajaxModifyWidgetSettings', params);
                    }
                    
                    break;
                
                case "sort":
                    // apply new sort to this.messages
                    this.refreshViewport();
                    
                    // save change of sort to the backend
                    if (this.options.fisdapWidgetId != null) {
                        var viewportId = $(this.element).attr('id');
                        var params = {}
                        params[viewportId] = {
                                'sort' : this.options.sort
                            }
                        routeAjaxRequest(this.options.fisdapWidgetId, 'ajaxModifyWidgetSettings', params);
                    }
                    
                    break;
            }
        },
        
        
        // DESTROY
        destroy: function() {
            // Use the destroy method to reverse everything your plugin has applied
            $.Widget.prototype.destroy.call(this);
        }
    });
})(jQuery);
