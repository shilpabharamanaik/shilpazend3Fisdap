//Javascript for /skills-tracker/shifts/index
var lastInstructorId = null;

$(document).ready(function(){
	
	if ($('#instructor-list').val() != 0) {
		lastInstructorId = $('#instructor-list').val();
	}
	
	$("#instructor-list").change(function() {
		blockUi(true);
		window.location = listBaseURL + $(this).val();
	});
	
	$("#go-btn").button().addClass('gray-button');
	
	$('#go-btn').click(function(e) {
		var throbber = $("<img src='/images/throbber_small.gif' style='float:right;'>");
		$(this).after(throbber);
		e.preventDefault();
		
		$.ajax({
			type: 'POST',
			url: "/ajax/instructor-picker-list",
			data: {"instructorSearch" : $("#instructorSearch").val(), "baseURL" : listBaseURL},
			success: function (response) {
				if($(response).html()){
					$("#instructor-content-container").hide();
					$("#instructor-results-container").show();
					$("#instructor-results").html($(response).hide().fadeIn(1000));
				}else{
					showModal("Your search for '" + $("#instructorSearch").val() + "' returned no instructors.");
				}
				throbber.remove();
			},
			error: function (request, status, error){
				if(error == 'timeout'){
					$("#instructor-content-container").hide();
					$("#instructor-results-container").hide();
					showModal("Your search for '" + $("#instructorSearch").val() + "' is taking a while to complete.  Please try limiting your search by being more specific.");
					$("#instructor-results").html();
				}
				throbber.remove();
			},
			timeout: 10000
		});
	});
	
	function showModal(text){
		$("<div>" + text + "</div>").dialog({
			modal: true,
			resizable: false,
			draggable: false,
			width: 600,
			buttons: {
				'Ok' : function() {
					$(this).dialog('close');
				}
			}
		});
	}
	
	//Show/hide stuff for advanced search
	var searchContainer = $('#advanced-search-container');
	
	$("#advanced-search-link").click(function(event){
		event.preventDefault();
		if (searchContainer.is(":visible")) {
			hideAdvancedInstructorSearch();
		} else {
			showAdvancedInstructorSearch();
		}
	});
	
	if ($.cookie('advancedInstructorSearch') == 'hide') {
		hideAdvancedInstructorSearch();
	} else {
		showAdvancedInstructorSearch();
	}
});

function showAdvancedInstructorSearch()
{
	$('#advanced-search-container').show();
	$.cookie('advancedInstructorSearch', 'show', {expires: 7, path: '/'});
	$("#arrow").attr('src', '/images/arrow_down.png');
}

function hideAdvancedInstructorSearch()
{
	$('#advanced-search-container').hide();
	$.cookie('advancedInstructorSearch', 'hide', {expires: 7, path: '/'});
	$("#arrow").attr('src', '/images/arrow_left.png');
}