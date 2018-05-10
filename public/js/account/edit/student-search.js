$(function(){
	
	var global_emails = [];
	
	$("#updateDisplay").button();
	$("#searchButton").button().parent();
	$("#displayOptionsTrigger").button();
	$("#searchThrobber").hide();
	
	$("#msg_type_buttonset").buttonset();
	

	
	/*
	$.post("/account/edit/get-students-from-search-cache",
		{},
		function(response){
			displayResults(response);
		}, "json").done();
		*/
	
	$("#email-modal").dialog({
        modal:true,
        autoOpen:false,
        resizable:false,
        width:650,
        title:"Message students",
		open: function(){
			
			// reset the form
			$("#subject").val("");
			$("#message").val("");
			$("label [for='email_msg_type']").trigger("click");
			
			$("#no-email-url-feature").remove();
			$("#email-url-too-long").remove();
			$(".info").remove();
			$("#mailto-options").find('p').show();
			
			$("#subject").trigger("focus");
			if ($("#mailto-options").css("display") == "block") {
				$("#mailto-options").hide();
			}

		}
    });
	
	$("#preview-email").click(function(e){
		e.preventDefault();
		if ($(this).hasClass("preview-email-expanded")) {
			$(this).removeClass("preview-email-expanded");
			$("#preview").slideUp();
		}
		else {
			$(this).addClass("preview-email-expanded");
			$("#preview").slideDown();
		}
		
	});
	
	$("#outlook").click(function(e){$("#email-modal").dialog("close");});
	$("#other").click(function(e){$("#email-modal").dialog("close");});
	
	$("#send-email-modal").click(function(e){
		
		
		
		var user_ids = $("#user_ids").val();
		var emails = $("#emails").val();
		var msg_type = $("#msg_type_buttonset").find(".ui-state-active").prev().attr("value");
		var subject = $("#subject").val();
		var message = $("#message").val();
		var cc_self = 0;
		
		if ($("#ccSelf").attr("checked")) {
			cc_self = 1;
		}
		
		// validate the data
		var errors = "";
		if (subject == "") {
			errors += "<li>Please enter a subject.</li>";
		}
		
		if (message == "") {
			errors += "<li>Please enter a message.</li>";
		}
		
		if (errors != "") {
			// display errors
			var slide = true;
			if ($("#email-modal").find(".error").length > 0) {
				slide = false;
				$("#email-modal").find(".error").remove();
			}
			$("#email-modal").prepend("<div class='error' style='display:none'>" + errors + "</div>");
			
			if (slide) {
				$("#email-modal").find(".error").slideDown();
			}
			else {
				$("#email-modal").find(".error").fadeIn();
			}
			
			return;
		}
		else {
			//$("#email-modal").find(".error").fadeOut();
			$(this).css("opacity", "0");
			$(this).parent().append("<img id='send-email-throbber' src='/images/throbber_small.gif'>");
		}
		
		$.post(
			'/account/edit/send-student-message',
			{user_ids: user_ids,
			emails: emails,
			msg_type: msg_type,
			subject: subject,
			message: message,
			cc_self: cc_self},
			function(response){
				$("#successful_msg").slideDown();
				$("#send-email-throbber").remove();
				$("#send-email-modal").css("opacity", 1);
				$("#email-modal").dialog("close");
			}	
		);
		
	});
	
	$("#close-email-modal").click(function(e){
		e.preventDefault();
		$("#email-modal").dialog("close");
	});
	
	$("#show-student-list").click(function(e){
		e.preventDefault();
		var toList = $("#to-list");
		if (toList.css("display") == "none") {
			// show it
			toList.slideDown();
			$(this).text("Hide list");
		}
		else {
			toList.slideUp();
			$(this).text("Show list");
		}
		
	});
	
	$("#bottom-buttons").find("a").button();
	
	
	$("#msg_type_buttonset").change(function(){
		
		
		if ($(this).find(".ui-state-active").attr("for").indexOf("email") != -1) {
			$("#personal-email").slideDown("fast");
		}
		else {
			$("#personal-email").slideUp("fast");
		}
		
	});
	
	$("#personal-email").click(function(){
		
		if ($("#mailto-options").css("display") == "block") {
			$("#mailto-options").slideUp("fast");
		}
		else {
			updateMailToLinks();
			$("#mailto-options").slideDown("fast");
		}
		
	});
	
	$("#subject").focus(function(){$(this).addClass("fancy-input-focus");});
	$("#subject").blur(function(){$(this).removeClass("fancy-input-focus");});
	$("#message").blur(function(){$(this).removeClass("fancy-input-focus");});
	$("#message").focus(function(){$(this).addClass("fancy-input-focus");});
	
	$("#searchString").focus(function(){
		$(this).addClass("fancy-input-focus");
		$("#search-placeholder-text").hide();
	});
	
	$("#searchString").blur(function(){
		$(this).removeClass("fancy-input-focus");
		if ($(this).val() == ""){
			$("#search-placeholder-text").fadeIn();
		}
	});
	
	$('#searchString').keyup(function(e) {
		if (e.which == 13) {
			e.preventDefault();

			if ($(this).val() == ""){
				$("#search-placeholder-text").fadeIn();
			}
			$(this).delay(100).blur();
			$("#searchButton").trigger("click");
		}
	});	
	
	$("#showAllStudents").button();
	$("#email").button();
	
	$("#email").click(function(e){
		e.preventDefault();
		
		var emails = [];
		var user_ids = [];
		var names = [];
		
		$("#table-holder").find("tbody").find("tr").each(function(){
			
			emails.push($(this).find(".email").text());
			names.push($(this).find(".name").text());
			user_ids.push($(this).attr("data-userId"));
			
		});
		
		$("#emails").val(emails.join());
		$("#user_ids").val(user_ids.join());
		
		updateMailToLinks();
		updateStudentCountText(names, names.length);
		
		global_emails = emails;
		
		$("#email-modal").dialog("open");
	});
	

	
	function updateStudentCountText(names, to_count) {
		$("#to-list").empty().append(names.join(", "));
		
		if (to_count == 1) {
			$("#student-count").text(names[0]);
			$("#show-student-list").hide();
			$("#email-modal").dialog( "option", "title", "Message student");
		}
		else {
			$("#show-student-list").show();
			$("#student-count").text(to_count + " students");
			$("#email-modal").dialog( "option", "title", "Message students");
		}
	}
	
	function updateMailToLinks() {
		// outlook has ';'
		var url = "mailto:";
		
		$("#no-email-url-feature").remove();
		$("#email-url-too-long").remove();
		$(".info").remove();
		$("#mailto-options").find('p').show();
		
		var too_long_email_level = "<div id='no-email-url-feature'>Select a smaller list of students to use this feature.</div>";
		var too_long_subject_level = "<div class='info' id='email-url-too-long'>The subject and message will not be included.</div>";
		var too_long_msg_level = "<div class='info' id='email-url-too-long'>The message will not be included.</div>";
		
		var outlook_url = "mailto:" + global_emails.join(";");
		var other_url = "mailto:" + global_emails.join(",");
		
		var body = $("#message").val().replace(/\s/g,"%20");
		var subject = $("#subject").val().replace(/\s/g,"%20");
		
		// see if we can include the subject
		if (outlook_url.length > 2000) {
			$("#mailto-options").prepend(too_long_email_level);
			$("#mailto-options").find('p').hide();
		}
		else {
			
			var url_length = parseInt(outlook_url.length);
			var subject_length = parseInt(subject.length);
			
			if (url_length+subject_length > 2000) {
				$("#mailto-options").prepend(too_long_subject_level);
				$("#email-url-too-long").show();
			}
			else {
				outlook_url += "?subject=" + subject;
				other_url += "?subject=" + subject;
				
				url_length = parseInt(outlook_url.length);
				body_length = parseInt(body.length);
				
				// now see if we can include the body
				if (url_length+body_length > 2000) {
					if ($("#email-url-too-long").length > 0) {
						$("#email-url-too-long").text("The message will not be included.");
					}
					else {
						$("#mailto-options").prepend(too_long_msg_level);
						$("#email-url-too-long").show();
					}
				}
				else {
					$("#email-url-too-long").slideUp().remove();
					outlook_url += "&body=" + body;
					other_url += "&body=" + body;
				}
			}
		}
		
		$("#outlook").attr("href", outlook_url);
		$("#other").attr("href", other_url);
		
	}
	
	$("#subject").keyup(function(){updateMailToLinks()});
	$("#message").keyup(function(){updateMailToLinks()});
	
	function studentEmailClickHandler() {
		$(".student-email-trigger").unbind();
		
		$(".student-email-trigger").click(function(){
			var row = $(this).parent().parent();
			email = $(this).text();
			name = row.find(".name").text();
			user_id = row.attr("data-userId");
			
			$("#emails").val(email);
			$("#user_ids").val(user_id);
			updateMailToLinks();
			//$("#to-list").empty().append(name);
			updateStudentCountText([name], 1);

            global_emails = [email];

			$("#email-modal").dialog("open");
		});
	}
	
	$("#search_area").fancyFilter({
	width: 525,
	closeOnChange: true,
	onFilterSubmit: 
		function(e) {		
			return $.post("/account/edit/get-students-from-search",
			getFiltersPostData(),

			function(response){
				displayResults(response);
			}, "json").done();
		}
		});
	
	var inactive_groups;
	if ($("#groups-optgroup-Inactive").length > 0) {
		inactive_groups = $("#groups-optgroup-Inactive").clone();
		$("#groups-optgroup-Inactive").remove();
	}
	
	var active_groups;
	if ($("#groups-optgroup-Active").length > 0) {
		active_groups = $("#groups-optgroup-Active").clone();
		$("#groups-optgroup-Active").remove();
	}
	
	var any_group_option = $("#groups").find("option[value='Any group']").clone();
	$("#groups").find("option[value='Any group']").remove();
	
	$("#groups").append(any_group_option);
	$("#groups").append(active_groups);
	$("#groups").append(inactive_groups);
	
	var non_selected = true;
	$("#groups").find("option").each(function(){
		if($(this).is(':selected')){
			non_selected = false;
		}
	});
	
	if (non_selected) {
		$("#groups").find("option").first().attr("selected", "selected");
	}
	
	$("#groups").chosen();
	
	
	$("#grad-month").css("width", "92px").chosen();
	$("#grad-year").css("width", "92px").chosen();
	
	$("#displayOptionsTrigger").click(function(e){
	e.preventDefault();
	if($(".student-search-table").length > 0){
		$("#updateDisplay").show();
	}
	else {
		$("#updateDisplay").hide();
	}
	
	$("#displayOptions").dialog("open");

	});
	
	function getFiltersPostData() {
		var postdata = {
		searchString: $("#searchString").val(),
		graduationMonth: $("#grad-month").val(),
		graduationYear: $("#grad-year").val(),
		section: $("#groups-element").find(":selected").val(),
		}
		
		 cert_array = [];
		 $("input[name='certificationLevels[]']").each(function(i,e){
			if($(e).is(":checked"))
			cert_array.push($(e).val());
		 }
		)
		
		if (cert_array.length > 0) {
			postdata["certificationLevels"] = cert_array;			
		}
		
		status_array = [];
		$("input[name='status[]']").each(function(i,e){
			if($(e).is(":checked"))
			status_array.push($(e).val());
		 }
		)
		
		if (status_array.length > 0) {
			postdata["gradStatus"] = status_array;			
		}
		
		return postdata;
	}
	
	function processSearchParams(e) {
		
		var data = getFiltersPostData();
		data.searchString =  $("#searchString").val();
		return $.post("/account/edit/get-students-from-search",
			data,
			
			function(response){
				displayResults(response);
				
			}, "json").done();
	}
		
	$("#updateDisplay").click(function(){
		updateColumns(false);
		$("#displayOptions").dialog("close");
	});
	
	$("#displayOptions").dialog({ modal: true, autoOpen: false, width: 350, title: "Columns" });
	
	pdfAction($("#export-student-list-links .pdfLink"));
	$("label[for='email_msg_type']").trigger("click");

	
	function pdfAction(trigger) {
		trigger.click(function(e){
			e.preventDefault();

			if ($("#results_area").find(".island").width() > 950) {
				if ($("#pdf_orientation_type").length > 0) {
					$("#pdf_orientation_type").attr("value", "landscape");
				}
				else {
					$("#search-result-options").append("<input id='pdf_orientation_type' value='landscape' type='hidden'>");
				}
			}
			else {
				if ($("#pdf_orientation_type").length > 0) {
					$("#pdf_orientation_type").attr("value", "portrait");
				}
				else {
					$("#search-result-options").append("<input id='pdf_orientation_type' value='portrait' type='hidden'>");
				}		
			}
			
			var no_cell_count = 0;
			
			$("#table-holder").find("th").each(function(){
				if ($(this).css("display") == "none"){
					$(this).addClass("no-pdf");
					no_cell_count++;
				}
				else {
					$(this).removeClass("no-pdf");
				}
			});
			
			$("#table-holder").find("td").each(function(){
				if ($(this).css("display") == "none"){
					$(this).addClass("no-pdf");
					no_cell_count++;
				}
				else {
					$(this).removeClass("no-pdf");
				}
			});
			
			var divContents = $("#table-holder").clone();
	
			createPdf(divContents, "fisdap-student-search", "export-student-list-links");
		});
	}
	
	//
	// EXPORT THE TABLE
	//
	$('#export-student-list-links').on('click','.csvLink',function(event){
		event.preventDefault();
		
		var csvName = "StudentList";
		
		// format the list
		var studentList = $("#student-table").clone();
		// replace product images with text
		$(studentList).find("td.product-access").each(function(){
			$(this).text($(this).attr("data-products"));
		});
		// remove hidden elements
		$(studentList).find("th[style$='display: none;']").remove();
		$(studentList).find("td[style$='display: none;']").remove();
		
		// add the student list to the list of tables to export
		var tables = {};
		tables["Student List"] = studentList;

		createCsv(tables, csvName, "export-student-list-links");
		
		return false;
	});
	
	$("#searchString").keypress(function(e){
		if(e.which == 13) {
			runSearch();
		}
	});
	
	function runSearch(e) {		
		clearResults();
		processSearchParams(e);
	}
	
	$("#searchButton").click(function(e){
		$(this).hide();
		$(this).parent().append("<img id='go-throbber' src='/images/throbber_small.gif'>");
		$("#go-throbber").fadeIn();
		runSearch(e)	 
	});
	
		
	// Set up an ajax call to go out that will pull in the most recently searched for students IF there is a search string - odn't do it if they used a 'show all'
	if ($("#searchString").val()) {
		$("#searchString").trigger("focus").trigger("blur");
		$("#searchButton").trigger("click");
	}
	
	function clearResults() {
		$("#searchThrobber").show();
		$("#search-result-options").fadeOut("fast");
		$("#results_area").fadeOut("slow");
		$("#results_area").empty();
	}
	
	$("#showAllStudents").click(function(e){
		$(this).hide();
		$(this).parent().parent().append("<img id='all-throbber' src='/images/throbber_small.gif'>");
		$("#all-throbber").fadeIn();
		
		clearResults();
		
		// reset the filters
		$("#search_area_filter-options-wrapper").find("input[type='checkbox']").removeAttr("checked");
		resetChosen("groups");
		resetChosen("grad-month");
		resetChosen("grad-year");
		
		// clear the search string
		$("#searchString").val("").trigger("focus").trigger("blur");
		
		return $.post("/account/edit/get-students-from-search",
			null,
			
			function(response){
				displayResults(response);
			}, "json").done();
		
	});
	
	function resetChosen(select_id) {
		$("#" + select_id.replace(/-/g, '_') + "_chzn").remove();
		$("#" + select_id).removeClass("chzn-done");
		$("#" + select_id).find("option").first().attr("selected", "selected");
		$("#" + select_id).chosen();
	}
	
	function displayResults(response) {
		
		$("#searchButton").show();
		$("#showAllStudents").show();
		$("#go-throbber").remove();
		$("#all-throbber").remove();
		
		$("#results_area").html(response.table);
		
		setMasq();
		$("#results_area").fadeIn();
		$("#student-table").tablesorter();
		
		updateColumns(false);
		updateFilterHeader();
		studentEmailClickHandler();
		
		if ($("#no-students-island").length > 0) {
			// disable the export and email buttons
			$("#export-student-list-links .pdfLink").button("disable");
			$("#export-student-list-links .csvLink").button("disable");
			$("#email").button("disable");
		}
		else {
			$("#export-student-list-links .pdfLink").button("enable");
			$("#export-student-list-links .csvLink").button("enable");
			$("#email").button("enable");
		}
		$("#search-result-options #hint_box").hide();
		$("#search-result-options").removeClass("island");
		$("#search-result-options").css("top",$("#results_area").find(".section-header").offset().top).delay(200).fadeIn();
	}
	
	function updateColumns(animate){
		var count = 0;
		$("#displayOptions").find("input:checkbox").each(function(){
			if($(this).attr("checked")){
				showCell($(this).val(), animate);
				count++;
			}
			else {
				hideCell($(this).val(), animate);
			}
		});
		
		if(count > 6){
			var newWidth = 130 * count;
			if(newWidth < 910){
				newWidth = 1020;
			}
			else if(newWidth > 1325){
				newWidth = 1250;
			}
		}
		else {
			var newWidth = 910;
		}
		
		$(".extraLong").css("width", newWidth + "px");
		
	//	pdfAction($("#pdfLink"));
	 }
	 
	 function showCell(className, animate){
		$("." + className).each(function(){
			if(animate){
				$(this).fadeIn();
			}
			else {
				$(this).show();
			}
		});
	 }

	 function hideCell(className, animate){
		$("." + className).each(function(){
			if(animate){
				$(this).fadeOut();
			}
			else {
				$(this).hide();
			}
		});
	 }
	 
	 function setMasq() {
		$(".masq").each(function(){
			$(this).hover(function(){
				$(this).find(".imgWrapper").find("img").show();
			}, function(){
				$(this).find(".imgWrapper").find("img").hide();
			});
		});
	 }
	 
function updateFilterHeader() {
	
	var postdata = getFiltersPostData();
	
	// Showing active AEMT/EMT/Paramedic students in EMT Day Class graduating in May 2012 
	
	var certs = postdata.certificationLevels;
	var cert_descriptions = [];

	if (certs) {
		var length = certs.length;
		for (var i = 0; i < length; i++) {
			$("input[name='certificationLevels[]']").each(function(){
				if ($(this).attr("value") == certs[i]) {
					cert_descriptions.push($(this).parent().text() + "s");
				}
			});
			
		}
	}
	
	/// cert level descriptions
	var cert_txt = "";
	if (cert_descriptions.length > 0) {
		cert_txt = cert_descriptions.join("/");
	}
	else {
		cert_txt = "students";
	}

	var statuses = postdata.gradStatus;
	var status_descriptions = [];

	if (statuses) {
		var st_length = statuses.length;
		for (var i = 0; i < st_length; i++) {
			$("input[name='status[]']").each(function(){
				if ($(this).attr("value") == statuses[i]) {
					status_descriptions.push($(this).parent().text());
				}
			});
			
		}
	}
	
	/// status descriptions
	var status_txt = "";
	if (status_descriptions.length > 0) {
		status_txt = status_descriptions.join("/");
	}
	else {
		status_txt = "all";
	}
	
	var gradLevelDescriptions = getGradLevelDescriptions();
	var sectionDescriptions = getSectionDescriptions();
	var newText = status_txt + " " + cert_txt + " " + sectionDescriptions + " " + gradLevelDescriptions;
	$("#search_area_filters-title-text").text("Filters: " + newText);
}

function getSectionDescriptions() {

	var text = "";
	var section = $("#groups option:selected").text();

	if (section != "Any group") {
		text += "in " + section;
	}

	return text;

}

function getGradLevelDescriptions() {
	var month = $("#grad-month option:selected").text();
	var year = $("#grad-year option:selected").text();
	var text = "graduating in ";

	if (month != "Month") {
		text += month + " ";
	}

	if (year != "Year") {
		text += year;
	}

	if (text == "graduating in ") {
		text = "";
	}

	return text;
}

function getCheckboxDescriptions(inputName, plural) {
	var hasSomeChecked = false;
	var checkedVals = [];
	var finalText = "";

	$('input[name="' + inputName + '[]"]:checked').each(function(){

		hasSomeChecked = true;
		var label = "";
		var searchingFor = inputName + "-" + $(this).val();
		
		$(this).parent().find("label").each(function(){
			if ($(this).attr("for") == searchingFor) {
				label = $(this).text();
				if (plural) {
					label += "s";
				}
			}
		});
		
		if (label != "EMTs" && label != "AEMTs") {
			label.toLowerCase();
		}
		
		checkedVals.push(label);
	});

	if (hasSomeChecked) {
		var count = 0;
		$.each(checkedVals, function(index, value) {
			if (count != 0) {
				finalText += "/";
			}
			finalText += value;
			count++;
		});
	}

	if (finalText == "") {
		finalText = "students";
	}

	return finalText;
}

	
});