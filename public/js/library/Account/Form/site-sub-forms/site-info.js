$(function(){
	
	$("#siteInfo #active").sliderCheckbox({onText: "Active", offText: "Inactive", "width": 55});
	$("#siteInfo #site-type").buttonset();
	$("#siteInfo #state").chosen();
	$("#siteInfo #save-button").button();
	$("#siteInfo #cancel-button").button();
	
	
	
	if ($("#contactPhone").hasClass("add-masking")) {
		$("#contactPhone").mask("999-999-9999? x9999");
	}
	
	if ($("#contactFax").hasClass("add-masking")) {
		$("#contactFax").mask("999-999-9999? x9999");
	}
	
	// formatting for IE
	if (navigator.userAgent.indexOf('MSIE') != -1) {
                $("#type-div div.form-desc").css({"top": "0px", "left": "0px"});
		$("#abbrev-div div.form-desc").css({"top": "-10px", "left": "36px"});
        }
	
	// formatting for firefox - mac
	 if (navigator.platform.indexOf('Mac') != -1) {
		$("#type-div div.form-desc").css({"top": "0px", "left": "0px"});
		$("#name").attr("size", "63");
		$("#address").attr("size", "63");
		$("#contactName").attr("size", "63");
	 }
	
	// when the user chooses a site type, update the hidden form input
	$("#site-type").click(function(){
		var type = $(this).find(".ui-state-active").attr("for");
		$("#type").val(type);
	});
	
	// cancel the form
	$("#siteInfo #cancel-button").click(function(e){
		e.preventDefault();
		
		// this function needs to be defined for each use of this form!!
		processSiteCancel();
	});
	
	// submitting the form
	$("#siteInfo #save-button").click(function(e){
		e.preventDefault();
		
		// remove old errors
        $('#form-errors').slideUp();
		$(".input-error").removeClass('input-error');
		
		// set up the throbbers
		showSubmitThrobber();
		
		var formValues = $("#siteInfoForm").serializeArray();
		$.post("/account/sites/save-site",
            formValues,
            function(response) {
				if (typeof response == 'number') {
					// this function needs to be defined for each use of this form!!
					processSiteSave(response);
				} else {
                    var htmlErrors = '<ul>';
                    $.each(response, function(elementId, msgs) {
                        if (elementId == "state") {
							$("#state_chzn a.chzn-single").addClass('input-error');
						} else if (elementId == "type") {
							$("#site-type").addClass('input-error');
						} else {
							$("#"+elementId).addClass('input-error');
						}
						
                        $.each(msgs, function(key, msg) {
                            htmlErrors += '<li>' + msg + '</li>';
                        });
                    });
                    htmlErrors += '</ul>';
                    $('#form-errors').html(htmlErrors);
                    $('#form-errors').slideDown();
					hideSubmitThrobber();
				}
            });
	});
	
});

function showSubmitThrobber() {
	$("#submitThrobber").show();
	$("#siteInfo #control-buttons").hide();
}

function hideSubmitThrobber() {
	$("#submitThrobber").hide();
	$("#siteInfo #control-buttons").show();
}
