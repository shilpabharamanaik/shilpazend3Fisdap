$(function(){
	// style sliders
	$(".slider-checkbox").each(function(){
		if ($(this).is('[disabled=disabled]')) {
			$(this).sliderCheckbox({
				onText: 'Active',
				offText: 'Inactive',
				width: 50,
				disabled: true
			});
		} else {
			$(this).sliderCheckbox({
				onText: 'Active',
				offText: 'Inactive',
				width: 50
			});
		}

	});
	
	// edit email
	$(".edit-recurring-email").button().click(function(event) {
    	event.preventDefault();
		var trigger = $(this);
		trigger.css({opacity: '0', display: 'none'}).parent().append("<div id='load-pdf-modal-throbber'><img src='/images/throbber_small.gif'></div>");
		
		var mode = 'edit';
		
		$.post("/scheduler/pdf-export/generate", {"email_id": trigger.attr("data-emailid")},
		function(resp) {
			$("#pdfDialog").html($(resp).html());
			initPdfModal(trigger.attr("data-emailViewType"), mode);
			$("#pdfDialog").dialog("open");
			$("#load-pdf-modal-throbber").remove();
			trigger.css({opacity: '1', display: 'block'});
		});
    });
	
	// delete email
	$(".delete-recurring-email").click(function(event) {
    	event.preventDefault();
		var trigger = $(this);
		trigger.css({opacity: '0', display: 'none'}).parent().append("<div id='load-delete-modal-throbber'><img src='/images/throbber_small.gif'></div>");
		
		$.post("/scheduler/emails/generate-delete-email", {"email_id": trigger.attr("data-emailid")},
		function(response) {
			$("#deleteRecurringEmailDialog").html(response);
			initDeleteEmailModal();
			$("#deleteRecurringEmailDialog").dialog("open");
			$("#load-delete-modal-throbber").remove();
			$(trigger).css({opacity: '1', display: 'block'});
		});
    });
	
	// click headers
	$("div.email-info").click(function(event) {
		var id = $(this).attr('id').substring(7);
		var infoRow = $("#info-"+id);
		var arrow = $("#arrow-"+id);
		var row = $(this).parent();
		
		if ($(infoRow).css('display') == 'none') {
			$(infoRow).slideDown();
			$(arrow).attr("src", "/images/accordion_arrow_down.png");
			$(row).addClass("accordion-header-selected");
		} else {
			$(infoRow).slideUp();
			$(arrow).attr("src", "/images/accordion_arrow_right.png");
			$(row).removeClass("accordion-header-selected");
		}
    });
	
	// active/inactive
	$("#email-table .slider-checkbox").change(function(){
		var email = $(this).attr('id').substring(7);
		var active = $(this).val();

		$.post("/scheduler/emails/update-active",
			{
				"email_id": email,
				"active": active
			},
		function(resp) {
			if (resp.user_updated == true) {
				$("#head-u-"+resp.email_id).html('from: ' + resp.instructor_first_name + ' ' + resp.instructor_last_name);
			}
		});
	});
	
	// click for details
	$(".clickable").click(function(event) {
		var list = $(this).find('ul');
		var sign = $(this).find('img.clickable');
		
		if ($(list).css('display') == 'none') {
			$(list).slideDown();
			$(sign).attr("src", "/images/icons/minus.png");
		} else {
			$(list).slideUp();
			$(sign).attr("src", "/images/icons/plus.png");
		}
    });
	
	// turn delete confirmation div into a modal
	$("#deleteRecurringEmailDialog").dialog({
		modal: true,
		autoOpen: false,
		resizable: false,
		width: 600,
		title: "Delete Recurring Email",
		open: function (){
		    $("#cancel-delete-btn").blur();
		}
	});
   
});

function initDeleteEmailModal() {
    $("#cancel-delete-btn").button().click(function(e){
		e.preventDefault();
		$("#deleteRecurringEmailDialog").dialog('close');
    });
	
    $("#confirm-delete-btn").button().click(function(e){
		e.preventDefault();
			
		var emailId = $(this).attr('data-emailid');
		var cancelBtn = $('#cancel-delete-btn').hide();
		var confirmBtn = $(this).hide();
		var throbber =  $("<img id='deleteModalThrobber' src='/images/throbber_small.gif'>");
		confirmBtn.parent().append(throbber);
		$.post("/scheduler/emails/process-delete-email",
			{ "email_id" : emailId },
			function (response) {
				if (response) {
					window.location = "/scheduler/emails";
					window.location.reload(true);
				} else {
					$("#delete-modal-content").html("<div class='alert'>You are not authorized to delete this email.</div>");
					cancelBtn.find(".ui-button-text").html("Ok");
					cancelBtn.show();
					$(throbber).remove();
				}
			}
		)
	});
}
