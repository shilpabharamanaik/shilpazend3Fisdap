function saveWidgetData(widgetId, incomingData, callback){
    var toSave = {
        wid: widgetId,
        data: incomingData
    };

    return $.post('/my-fisdap/widget-ajax/update-widget-settings', toSave, function(results){
        if(callback !== undefined){
            callback(results);
        }
    });
}

/**
 * This function should be used to initialize a section.
 * @param sectionName String name of the section to initialize
 * @param containerId String ID of the dom element to put the widgets into
 * @returns
 */
function loadWidgets(sectionName, containerId, options, callback){

    $.ajax({
        type: 'POST',
        url: '/my-fisdap/widget-ajax/get-widget-list',
        data: {sname: sectionName, options:options},
        success: function(widgetIds){
            if(widgetIds['error'] != undefined){
                window.location = '/';
            }
            if(typeof widgetIds != "string"){
                for(widgetId in widgetIds){
                    loadWidgetInSection(containerId, widgetIds[widgetId], true, options, callback);
                }
            }
        }
    });
}


/**
 * This function should be used to initialize a section. We've backtracked on how we want to load widgets on
 * MyFisdap vs. the reports section. The easiest way to do this is to have the old and new versions of these
 * functions co-existing, called differently in each place it is used.
 * @param sectionName String name of the section to initialize
 * @param containerId String ID of the dom element to put the widgets into
 * @returns
 */
function loadWidgetsDeferred(sectionName, containerId, options, callback){

    // The defered object we're eventually going to resolve or reject
    var dfd = new jQuery.Deferred();

    // Get the list of widgets in this section and do not return the deferred, we're building our own deferred later
    $.ajax({
        type: 'POST',
        url: '/my-fisdap/widget-ajax/get-widget-list',
        data: {
            sname: sectionName,
            options:options
        }
    }).done(function(widgetIds){

        // An array of deferred objects
        var widgetDeferreds = [];

        if(widgetIds['error'] !== undefined){
            window.location = '/';
        }
        if(typeof widgetIds != "string"){

            // Load the widgets in this section, adding each deferred to an array of deferred objects
            for(var widgetId in widgetIds){
                widgetDeferreds.push(loadWidgetInSectionDeferred(containerId, widgetIds[widgetId], true, options, callback));
            }
        }

        // Only when all the widgets are all loaded do we resolve our deferred to send a resolution event up the chain
        return $.when.all(widgetDeferreds).done(function(promises){
            dfd.resolve(promises);
        });
    });

    return dfd.promise();
}


function reloadWidget(widgetId, options){
    return $.ajax({
          type: 'POST',
          url: '/my-fisdap/widget-ajax/render-widget/',
          data: {wid: widgetId, options: options},
          async: false,
          success: function(results){
                $('#' + 'widget_' + widgetId + '_container').replaceWith(results);
            }
    });
}

/**
 * Load all of the widgets in a section given
 * @param  String   containerId String containing the selector of a container to put results
 * @param  Integer   widgetId    Id of the widget data
 * @param  Boolean   asyncFlag   Load async or not?
 * @param  Object   options     optons to pass down
 * @param  Function callback  A callback to execute
 * @return Deferred              A deferred that gets resolved when all widgets are loaded
 */
function loadWidgetInSection(containerId, widgetId, asyncFlag, options, callback){
    if(asyncFlag == undefined){
        asyncFlag = false;
    }

    spinner = $("<div class='widget-container' style='text-align: center' id='spinner-" + widgetId + "'><img src='/images/throbber_small.gif' /></div>")

    $('#' + containerId).append(spinner);

    $.ajax({
        type: 'POST',
        url: '/my-fisdap/widget-ajax/render-widget/',
        data: {wid: widgetId, options: options},
        async: asyncFlag,
        success: function(results){
            //$('#' + containerId).append(results);
            $('#spinner-' + widgetId).replaceWith(results);

            // Only call the callback if it is defined
            if(typeof callback === 'function') {
                callback(results);
            }

        },
        error: function(results){
            $('#spinner-' + widgetId).remove();
        }
    });
}

/**
 * Load all of the widgets in a section given
 * We've backtracked on how we want to load widgets on
 * MyFisdap vs. the reports section. The easiest way to do this is to have the old and new versions of these
 * functions co-existing, called differently in each place it is used.
 * @param  String   containerId String containing the selector of a container to put results
 * @param  Integer   widgetId    Id of the widget data
 * @param  Boolean   asyncFlag   Load async or not?
 * @param  Object   options     optons to pass down
 * @param  Function callback  A callback to execute
 * @return Deferred              A deferred that gets resolved when all widgets are loaded
 */
function loadWidgetInSectionDeferred(containerId, widgetId, asyncFlag, options, callback){
    var dfd = jQuery.Deferred();

    if(asyncFlag === undefined){
        asyncFlag = false;
    }

    spinner = $("<div class='widget-container' style='text-align: center' id='spinner-" + widgetId + "'><img src='/images/throbber_small.gif' /></div>");

    $('#' + containerId).append(spinner);

    $.ajax({
        type: 'POST',
        url: '/my-fisdap/widget-ajax/render-widget/',
        data: {wid: widgetId, options: options},
        async: asyncFlag
    }).done(function(results){

        $('#' + containerId).append(results);
        $('#spinner-' + widgetId).replaceWith(results);

        if(typeof callback === 'function') {
            callback(results);
        }
        dfd.resolve(results);
    }).fail(function(){
        $('#spinner-' + widgetId).remove();
    });

    return dfd.promise();
}

function addWidget(sectionName, widgetId){
    var data = {
        sname: sectionName,
        order: $('#' + sectionName + '-section').children().size() + 1,
        wdef_id: widgetId
    };

    return $.post('/my-fisdap/widget-ajax/add-widget/', data, function(results){
        if(results.error !== undefined){
            alert(results.error);
        }else{
            loadWidgetInSection(sectionName + '-section', results.id);
        }
    });
}

function routeAjaxRequest(widgetId, functionName, incomingData, successCallback, elementId){
    var data = {
        wid: widgetId,
        fcn: functionName,
        data: incomingData
    };

    if (elementId !== undefined) {
        blockUi(true, $("#"+elementId));
    }

    $.post('/my-fisdap/widget-ajax/reroute-ajax-request', data, function(returnData){
        if(successCallback !== undefined){
            successCallback(returnData);
        }

        if (elementId !== undefined) {
            blockUi(false, $("#"+elementId));
        }
    });
}

function toggleMinMax(widgetId)
{
    if ($('#minimize_widget_' + widgetId + '_maximized').is(':hidden')) {
        return maximizeWidget(widgetId);
    } else {
        return minimizeWidget(widgetId);
    }
}

function minimizeWidget(widgetId){
    $('#minimize_widget_' + widgetId + '_maximized').hide();
    $('#minimize_widget_' + widgetId + '_minimized').show();

    $('#widget_' + widgetId + '_render').hide("blind", {}, "slow");

    return toggleCollapse(widgetId);
}

function maximizeWidget(widgetId){
    $('#minimize_widget_' + widgetId + '_maximized').show();
    $('#minimize_widget_' + widgetId + '_minimized').hide();

    $('#widget_' + widgetId + '_render').show("blind", {}, "slow");

    return toggleCollapse(widgetId);
}

function toggleCollapse(widgetId){
    return $.ajax({
          type: 'POST',
          url: '/my-fisdap/widget-ajax/toggle-collapse',
          data: {wid: widgetId},
          async: true
    });
}

function deleteWidget(widgetId){
    //Find the original widget container
    var widgetContainer = $('#widget_' + widgetId + '_container');
    var widgetTitle = $(widgetContainer).find(".widget-title").text();

    // Create a new placeholder "undo" div and give it the same ID of the widget container being removed
    var newDiv = $('<div id="widget_' + widgetId + '_container" class="undo-widget-box">The "' + widgetTitle + '" panel was removed. <a href="#" id="undo-delete-' + widgetId + '" onclick="undeleteWidget(' + widgetId + '); return false;">Undo!</a><div class="timer-msg">(this message will disappear in <span id="widget_' + widgetId + '_timer">12</span> seconds)</div></div>');
    widgetContainer.replaceWith(newDiv);

    //Remove the widget after 12 seconds
    $(newDiv).delay(12000).fadeOut(1000);

    return $.post('/my-fisdap/widget-ajax/remove-widget', {wid: widgetId});
}

function undeleteWidget(widgetId){
    return $.post('/my-fisdap/widget-ajax/undelete-widget', {wid: widgetId}).done(function(){
        reloadWidget(widgetId);
    });
}

// Improve on the basic $.when by passing the arguments for each resolve or fail
if (jQuery.when.all===undefined) {
    jQuery.when.all = function(deferreds) {
        var deferred = new jQuery.Deferred();
        $.when.apply(jQuery, deferreds).then(
            function() {
                deferred.resolve(Array.prototype.slice.call(arguments));
            },
            function() {
                deferred.fail(Array.prototype.slice.call(arguments));
            });

        return deferred;
    };
}
