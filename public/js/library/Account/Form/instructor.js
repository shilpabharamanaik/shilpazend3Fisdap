$(function() {
	$('#instructorForm :input:not(:hidden, :submit, :checkbox, select)').blur(function() {doValidation($(this).parents(".form-prompt").find('label').attr('for'))});
	$("#continuePC").button();
	$("#cancelPC").button();
	

	$(".permission-radio:even").each(function(){
		$(this).parent().css("margin-right", "5.2em");
		return;
	})

	var currentCheckbox;
	var checkMe = [];
	var displayMe = [];
	var uncheckMe = [];
	$("input[name='subRoles[]']").click(function(){
		currentCheckbox = $(this);
		
		checked = [];
		$("input[name='subRoles[]']:checked").each(function(i, el){
			checked.push($(el).val());
		});
		
		$(this).parent().append('<img id="roleThrobber" src="/images/throbber_small.gif">');
		
		$.post("/account/edit/get-role-permissions", {"roles" : checked},
		function(response){
			$("#addedPermissions").empty();
			$("#removedPermissions").empty();
			checkMe = [];
			displayMe = [];
			uncheckMe = [];
			$(".permission-checkbox").each(function(i, el){
				
				if(($(el).val() & response)){
					if(!$(el).attr("checked")){
						displayMe.push($(el).val());
						var adding = "<li><img src='/images/icons/add.png'>" + $(el).parent().text() + "</li>";
						$("#addedPermissions").append(adding);
					}

					checkMe.push($(el).val());
					
				}
				else{
					if($(el).attr("checked")){
						var removing = "<li><img src='/images/icons/delete.png'>" + $(el).parent().text() + "</li><div style='clear:both;'></div>";
						$("#removedPermissions").append(removing);
						uncheckMe.push($(el).val());
					}
				}
				$("#roleThrobber").remove();
				$("#dialog-confirm-permissions").dialog("open");

			});
			
			if(displayMe.length == 0){
				$("#addedPermissions").append("No new permissions will be added");
			}
			if(uncheckMe.length == 0){
				$("#removedPermissions").append("No permissions will be removed");
			}

		}, "json");
				
	});
	
	$( "#dialog-confirm-permissions" ).dialog({
		resizable: false,
		minHeight:200,
		width:740,
		modal: true,
		title: "Permission Change",
		autoOpen: false,
		open: function(){
			$("#cancelPC").blur();
			$("#continuePC").blur();
		},
		close: function(){

		}
	});
	
	$( "#dialog-confirm-delete" ).dialog({
		resizable: false,
		minHeight:200,
		width:400,
		modal: true,
		title: "Delete Instructor",
		autoOpen: false,
	});
	
	$("#delete-account").click(function(e) {
		e.preventDefault();
		var loc = $(this).attr("href");
		var dialog = $("#dialog-confirm-delete").dialog("option", "buttons", {
				"Cancel" : function() {
					$(this).dialog("close");
				},
				"Delete" : function() {
					window.location.href = loc;
				}
			});
		dialog.dialog("open");
	})
	
	$("#cancelPC").click(function(e){
		e.preventDefault();
		$("#dialog-confirm-permissions").dialog( "close" );
		if(currentCheckbox.attr("checked")){currentCheckbox.removeAttr("checked");}
		else {currentCheckbox.attr("checked", "checked");}
	});
	
	$("#continuePC").click(function(e){
		e.preventDefault();
		if(!currentCheckbox.attr("checked")){currentCheckbox.removeAttr("checked");}
		else {currentCheckbox.attr("checked", "checked");}
		$(".permission-checkbox").each(function(i, el){
			if(jQuery.inArray($(el).val(), checkMe) >= 0){
				$(el).attr('checked', 'checked');
			}
			else{
				$(el).removeAttr("checked");
			}
		});
		$("#dialog-confirm-permissions").dialog( "close" );
	});
	
});

function doValidation(id) {
	// do validation, but don't worry about the subroles
	if(id.indexOf("subRoles") == -1){
		var url = '/ajax/validate-instructor-form';
		var data = $('#instructorForm').serialize();
	
		$.post(url, data, function(resp) {
			$("#"+id).parent().find('.form-live-errors').remove();
			$("#"+id).parent().find('.form-validation-icon').remove();
	
			$("#"+id).parent().append(getErrorHtml(resp[id], id));			
	
		}, 'json');
	}
}

function getErrorHtml(formErrors , id) {
    if (formErrors) {
		var o = '<img src="/images/badinput.png" class="form-validation-icon">';
	} else {
		var o = '<img src="/images/check.png" class="form-validation-icon">';		
	}
	
	
	o += '<ul id="form-live-errors-' + id + '" class="form-live-errors">';
	
    for (errorKey in formErrors) {
        o += '<li>' + formErrors[errorKey] + '</li>';
    }
    o += '</ul>';
    return o;
}