//Javascript for /skills-tracker/shifts/index
var lastStudentId = null;

$(document).ready(function(){
	
	if ($('#student-list').val() != 0) {
		lastStudentId = $('#student-list').val();
	}
	
	$("#go-btn").button().addClass('gray-button');

	$("#shift-list-messages").hide().fadeIn(1000);
	

	performFilter = function(){
		var throbber = $("<img src='/images/throbber_small.gif' style='float:right;'>");
		
		$("#student-list").hide();
		$("#student-list").after(throbber);
		
		$.post("/ajax/generate-student-list", $("#advanced-search-form").serialize(),
			function(response) {
				var html = '';
	
				// Chrome/Google claims there is no javascript spec for order of properties to an object (array)
				// and so will re-order objects whose keys are numeric. This messes up student order.
				// So, we manually re-sort to alphabetical order
				
				// Create a new array of the values to sort (lower-cased names)
				// keys are appended to names in case of identical name values, separated by /
				var orderNames = [];
				$.each(response, function(id, name) {
					orderNames.push(name.toLowerCase() + '/' + id);
				});
				orderNames.sort();
				
				// go through sorting array to build output
				$.each(orderNames, function(i, sortedName) {
					var studentKey = sortedName.split('/').pop();
					if (studentKey > 0 && typeof(response[studentKey]) != 'undefined') { // basic sanity test
						html += "<option value='" + studentKey + "'";
						if (lastStudentId == studentKey) {
							html += " selected='selected' ";
						}
						html += ">" + response[studentKey] + "</option>";
					}
				});
				$('#student-list').html(html);
				
				throbber.remove();
				$("#student-list").show();
			 });
	}
	
	//Filter student list based on graduation date
	$("#graduation-month, #graduation-year, #section").change(performFilter);
	
	$('#go-btn').click(function(e) {
		var throbber = $("<img src='/images/throbber_small.gif' style='float:right;'>");
		$(this).after(throbber);
		e.preventDefault();
		
		$.ajax({
			type: 'POST',
			url: "/ajax/student-picker-list",
			data: {"studentSearch" : $("#studentSearch").val(), "baseURL" : listBaseURL},
			success: function (response) {
				if($(response).html()){
					$("#student-content-container").hide();
					$("#student-results-container").show();
					$("#student-results").html($(response).hide().fadeIn(1000));
				}else{
					showModal("Your search for '" + $("#studentSearch").val() + "' returned no students.");
				}
				throbber.remove();
			},
			error: function (request, status, error){
				if(error == 'timeout'){
					$("#student-content-container").hide();
					$("#student-results-container").hide();
					showModal("Your search for '" + $("#studentSearch").val() + "' is taking a while to complete.  Please try limiting your search by being more specific.");
					$("#student-results").html();
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
			hideAdvancedStudentSearch();
		} else {
			showAdvancedStudentSearch();
		}
	});
	
	if ($.cookie('advancedStudentSearch') == 'show') {
		showAdvancedStudentSearch();
	} else {
		hideAdvancedStudentSearch();
	}
});

function showAdvancedStudentSearch()
{
	$('#advanced-search-container').show();
	$.cookie('advancedStudentSearch', 'show', {expires: 7, path: '/'});
	$("#arrow").attr('src', '/images/arrow_down.png');
}

function hideAdvancedStudentSearch()
{
	$('#advanced-search-container').hide();
	$.cookie('advancedStudentSearch', 'hide', {expires: 7, path: '/'});
	$("#arrow").attr('src', '/images/arrow_left.png');
}

function updateClassSections(){
	selectedValue = $("#sectionYear option[value='" + $("#sectionYear").val() + "']").text();
	
	postObj = {};
	
	if(selectedValue == "All Years"){
		postObj.year = 0;
	}else{
		postObj.year = selectedValue;
	}
	
	var throbber = $("<img src='/images/throbber_small.gif' style='float:right;'>");
	
	//Add the throbber gif and disable the filter inputs
	sectionElement = $("#section");
	
	$("#sectionYear").after(throbber);
	
	sectionElement.hide();
	
	$.post('/skills-tracker/shifts/filter-class-sections', postObj,
	function(response) {
		sectionElement.show();
		throbber.remove();
		
		$("#section").empty();
		
		for(index in response){
			$("#section").append("<option value='" + index + "'>" + response[index] + "</option>");
		}
		
		performFilter();
		
	}, 'json');
}