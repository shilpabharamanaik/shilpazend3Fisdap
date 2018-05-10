$(function(){
    $("body").delegate("input[name=evaluatorType]:radio", "change", function(){
        updateEvaluatorList(this);
    });
    $("#practiceDialog").dialog({
        autoOpen: false,
        width: 500,
        modal: true,
        resizable: false,
        draggable: false,
        open: function() {
            //Disable all elements if this eval is confirmed
            toggleElements(!parseInt($("#confirmed").val()));
            $("#practiceDialog").find("input").blur();
            
            //Add click function to handle unconfirming evals
            $("#unconfirm-toggle").click(function(e){
                e.preventDefault();
                unconfirmLink = $(this);
                blockUi(true);
                $.post("/skills-tracker/shifts/unconfirm-practice-item", {"practiceItemId" : $("#practiceItemId").val()}, function(response){
                    if (response == true) {
                        updatePracticeItemWidget($('#practiceDialog #shiftId').val());
                        toggleElements(true);
                        blockUi(false);
                        unconfirmLink.remove();
                    }
                }, "json");
            });
            
            $("#add-practice-item-save-btn").unbind();
            $("#add-practice-item-cancel-btn").unbind();
            
            $("#add-practice-item-save-btn").button();
            $("#add-practice-item-cancel-btn").button().css("color", "#666");

            $("#confirm-toggle").click(function(e){
                e.preventDefault();
                if($("#confirmToggle").val() == 0) {
                    $("#confirmToggle").val(1);
                } else {
                    $("#confirmToggle").val(0);
                }
                $("#confirmCredentials").slideToggle(800);
            });
            
            $("#add-practice-item-save-btn").click(function(e){
                e.preventDefault();
                
                //Don't do anything if the button is disabled
                if ($(this).attr('disabled')) {
                    return false;
                }
                
                saveBtn = $(this);
                
                saveBtn.css("opacity", "0").prop('disabled', true).button('refresh');
                
                $.post('/skills-tracker/shifts/validate-practice', $('#practiceDialog form').serialize(),
                        function(response) {
                            if (response === true) {
                                saveBtn.css("opacity", "1");
                                $('#practiceDialog').dialog('close');
                                // refresh practice item list
                                updatePracticeItemWidget($('#practiceDialog #shiftId').val());
                                
                                $('.widget-container').each(function(index, el){
                                    if($(el).attr('data-widget-id') > 0){
                                        reloadWidget($(el).attr('data-widget-id'));
                                    }
                                });
                                            
                            } else {
                                htmlErrors = '<div class=\'form-errors alert\'><ul>';
                                
                                $('label').removeClass('prompt-error');
                                
                                $.each(response, function(elementId, msgs) {
                                    $('label[for=' + elementId + ']').addClass('prompt-error');
                                    $.each(msgs, function(key, msg) {
                                        htmlErrors += '<li>' + msg + '</li>';
                                    });
                                });
                                
                                htmlErrors += '</ul></div>';
                                
                                $('.form-errors').remove();
                                $('#practiceDialog form').prepend(htmlErrors);
                                
                                saveBtn.css("opacity", "1").prop('disabled', false).button('refresh');
                            }
                        }
                );
                
                return true;
            });
            
            $("#add-practice-item-cancel-btn").click(function(e){
                e.preventDefault();
                
                //Don't do anything if the button is disabled
                if ($(this).attr('disabled')) {
                    return false;
                }
                
                $("#practiceDialog").dialog("close");
                return true;
            });
                
            $("#button-set-evaluatorType").find("label").each(function(){
                setUpLabelStyles($(this));
                $(this).css("width", "135px");
            });
            
            $("input[name='evaluatorType']").change(function(){
                var label = $("label[for='" + $(this).attr("id") + "']");
                setUpLabelStyles(label);
                $(this).parent().find("label").each(function(){
                    setUpLabelStyles($(this));
                });
                toggleConfirmDisplay($(this).val() == 1);
            });
            
            //On modal open, make sure the confirm is corrent shown or hidden
            toggleConfirmDisplay($("input[name='evaluatorType']:checked").val() == 1);
            
            $("#button-set-passed").find("label").each(function(){
                $(this).css("width", "135px");
            });
            
            function setUpLabelStyles(label){
                var labelFor = label.attr("for");
                
                if(labelFor == "evaluatorType-1"){label.attr("id", getLabelId("instructor", label));}
                else if(labelFor == "evaluatorType-2"){label.attr("id", getLabelId("student", label));}
            }
            
            function getLabelId(type, label){
                var whichInput = ":last";
                if(type=="instructor"){ whichInput = ":first"; }
                
                var checkedVal = label.parent().find("input" + whichInput).attr("checked");
                if(checkedVal){checkedVal = "-checked";}
                else {checkedVal = "";}
                return "" + type + "-evaltype-label" + checkedVal + "";
            }
            
            
        }
    });

    
});

/**
 * Disable the Evaluator select element and repopulate
 * the options based on the chosen evaluator type
 */
function updateEvaluatorList(formElement)
{
    if ($("#canConfirmEvalsElement").val() == 1 && $("input[name='evaluatorType']:checked").val() == 1) {
        $("#add-practice-item-save-btn").button( "option", "label", "Save & Confirm" );
    } else {
        $("#add-practice-item-save-btn").button( "option", "label", "Save" );        
    }
    
    $("#evaluatorId").attr("disabled", "disabled");
    $("#add-practice-item-save-btn").attr("disabled", "disabled");
    $.post("/skills-tracker/shifts/get-evaluator-list", {"evaluatorTypeId" : $(formElement).val(), "shiftId" : $("#shiftId").val()}, function(response) {
            var htmlOptions = "<option value='0'>Evaluator</option>";
            $(response).each(function(index, element) {
                htmlOptions += "<option value='" + element.id + "'>" + element.last_name + ", " + element.first_name + "</option>";
            });
            
            $("#evaluatorId").html(htmlOptions);
            $("#evaluatorId").removeAttr('disabled')
            $("#add-practice-item-save-btn").removeAttr('disabled')

        }, "json");
}

/**
 * Toggle to show or hide the username/password field
 * for confirming a practice item
 */
function toggleConfirmDisplay(showOrHide)
{
    $("#confirm-toggle").closest(".grid_12").toggle(showOrHide);
    if (showOrHide == false) {
        $("#confirmCredentials").slideUp(800);
    }
}

/**
 * Toggle all of the modal elements to enable or disable
 * and to hide the throbber chilling behind the save button
 */
function toggleElements(enable) {
    if (enable == true) {
    	//console.log($("#practiceDialog :ui-button"));
    	$("#practiceDialog :input").removeAttr('disabled');
    	$("#practiceDialog :ui-button").button('enable');
    	
        $("#add-practice-item-save-btn").show();
        $(".throbber").show();
    } else {
    	// disable the buttons
    	if($("#practice-modal-buttons a").is(':ui-button')){
    		$("#practice-modal-buttons a").button( "disable" );
    	}
    	
    	// disable the other form elements
    	$("#practiceDialog :input").attr('disabled', 'disabled');
    	
    	$("#add-practice-item-save-btn").hide();
        $(".throbber").hide();

    }
}
