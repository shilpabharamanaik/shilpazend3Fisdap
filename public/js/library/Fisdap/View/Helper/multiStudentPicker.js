var msp_initialized = false;
var msp_tableColumns = [];
window.msp_addtlData = {};
var filtersOpen = false;
$(function(){
	/*
	$('#msp_filters').accordion({
		collapsible: true,
		autoHeight: false,
	});
*/
	

	
	$("#filters-title").click(function(e){
		e.preventDefault();
		e.stopPropagation();
		if(filtersOpen){
			closeFilters();
		}
		else {
			$("#msp_filter_form").slideDown();
			$(this).parent().removeClass("bottomRoundedCorners");
			filtersOpen = true;
			$("#filters-title-icon").find("#plus").remove();
			$("#filters-title-icon").prepend("<img id='minus' src='/images/icons/minus_Gray.png'>");
		}
	});
	
	$('html').click(function(e) {
		var target = e.target;
		if($(target).attr("type") != "checkbox"
		   && $(target).attr("id") != "msp_filter_form"
		   && !$(target).is("select")
		   && !$(target).is("label")
		   && !$(target).hasClass("msp_section_title")
		   && !$(target).hasClass("certLevelWrapper")
		   && !$(target).hasClass("graduatingWrapper")
		   && !$(target).parent().hasClass("certLevelWrapper")
		   && !$(target).parent().hasClass("graduatingWrapper")
		   && !$(target).parent().hasClass("grid_6")){
			closeFilters();
		}
	 });
	
	function closeFilters(){
		$("#msp_filter_form").slideUp();
		$("#filters-title").parent().addClass("bottomRoundedCorners");
		if($("#filters-title-icon").find("#minus").length != 0){
			$("#filters-title-icon").find("#minus").remove();
			$("#filters-title-icon").prepend("<img id='plus' src='/images/icons/plus_Gray.png'>");
		}
		filtersOpen = false;
	}
	
	// Set up change listeners on all of the form elements...
	$('#msp_filter_form :input').change(function(){ msp_performFilter(); });
	
	// Add a click listener to the submit button...
	$('#msp_submit_students').click(function(){
		msp_submitStudents();
	});
	
	msp_performFilter();
	
	$('#' + msp_targetFormId).submit(msp_triggerAjaxCall);


});

msp_triggerAjaxCall = function(overrideData){
	msp_insertStudentList();
	
	// eliminate the form elements for any student that was NOT selected
	// this is done to limit the possibility of hitting PHP's max_input_vars setting,
	// which limits the number of form elements ($_POST variables) that can be submitted and processed by PHP
	// it can happen on long lists of students!
	$("#student-list-throbber").show();
	$('.msp_student_toggle_checkbox:not(:checked)').parents('tr.msp_student_row').remove();
	
	if(msp_isAjaxPost){
		data = $('#' + msp_targetFormId).serialize();
		
		
		for(x in overrideData){
			data[x] = overrideData[x];
		}
		
		if (msp_blockUi) {
			blockUi(true);
		}
		
		$.post(msp_ajaxPostURL, data, function(response){
			$('#' + msp_ajaxResultsContainer).empty().append(response);
			blockUi(false);
		});
		
		
		
		return false;
	}else{
		return true;
	}
}

msp_submitStudents = function(){
	studentIds = [];
	
	// Collect all of the student IDs from the list of selected students
	$('.msp_student_toggle_checkbox').each(function(i, el){
		if($(el).attr('checked')){
			studentIds.push($(el).val());
		}
	})
	
	// eliminate the form elements for any student that was NOT selected
	// this is done to limit the possibility of hitting PHP's max_input_vars setting,
	// which limits the number of form elements ($_POST variables) that can be submitted and processed by PHP
	// it can happen on long lists of students!
	$("#student-list-throbber").show();
	$('.msp_student_toggle_checkbox:not(:checked)').parents('tr.msp_student_row').remove();
}

msp_toggleFilters = function(){
	$('#msp_filters').toggle();
}

msp_initializeTable = function(columnData){
	if(!msp_initialized){
		// Always add in the selector column.  This column houses the toggle for selecting the current student
		// and should have a "select all/none" option in the header.
		headRow = $('<tr class="headRow"></tr>');

		selectorHeader = $("<td class='checkboxCell'><button id='checkAll'>All</button></td>");

		headRow.append(selectorHeader);

		msp_tableColumns.push('id');
		
		$(columnData).each(function(i, el){
			if(el != "Name"){
				newHeader = $('<td class="centerMe">' + el + '</td>');
			}
			else {
				newHeader = $('<td id="nameHeader">' + el + '</td>');
			}
			headRow.append(newHeader);
			msp_tableColumns.push(el);
		});

		tableHeader = $('<thead></thead>').append(headRow);
		
		// Finally append the whole row into the student table...
		$('#msp_student_list').append(tableHeader);

	}
	
	setCheckAll();

}

function setCheckAll(){
	$("#checkAll").unbind("click");
	
	$("#checkAll").click(function(e){
	
		e.preventDefault();
		if($(this).text() == 'All'){
			$(this).text("");
			$("#msp_student_list").find("input").each(function(){
				//$(this).attr('checked','checked');
				selectRow($(this), $(this).parent().parent(), "#fff", "all");

			});
			//$('.msp_student_toggle_checkbox').attr('checked','checked');
			$(this).text("None");
		}
		else {
			$(this).text("");
			$("#msp_student_list").find("input").each(function(){
				selectRow($(this), $(this).parent().parent(), "#fff", "none");

			});
			
			$(this).text("All");
			//$('.msp_student_toggle_checkbox').attr('checked','');
		}
	});
}


/**
 * This function toggles the checkboxes in the student picker.
 *
 * @param expectedState Boolean containing true to make all checkboxes checked, false to uncheck them all. 
 */
msp_toggleSelectedStudents = function(expectedState){
	if(expectedState){
		$('.msp_student_toggle_checkbox').attr('checked','checked');
	}else{
		$('.msp_student_toggle_checkbox').attr('checked','')
	}
	
	return false;
}

msp_getFormData = function(){
	// I'm using a few custom elements while using the UserLegacy getAllStudentsByProgram filter, so need to remap some of the
	// form names
	data = {};
	
	// Need to collect the selected fields manually.  Can't use .serialize() since these inputs no 
	// longer live in a form tag.
	$('#msp_filter_form :input').each(function(i, el){
		jqel = $(el);
		
		// Gotta do some extra work if the form input is using the array syntax stuff...
		if(jqel.attr('name').indexOf('[]') >= 0){
			// These are typically checkboxes.  Will need to rework this if we use text inputs
			// or something else weird
			if(jqel.attr('checked')){
				realPropName = jqel.attr('name').replace('[]', '');
				
				if(data[realPropName] == undefined){
					data[realPropName] = [];
				}
				
				data[realPropName].push(jqel.val());
			}
		}else{
			data[jqel.attr('name')] = jqel.val();
		}
	});
	
	return data;
}

msp_performFilter = function(){
	// I'm using a few custom elements while using the UserLegacy getAllStudentsByProgram filter, so need to remap some of the
	// form names
	data = msp_getFormData();
	
	
	// do we have additional data args initialized at this point?
	if ($.isEmptyObject(window.msp_addtlData)) {
		// Nope, it's empty, so check for the additionalQueryArgs element to see if there are arbitrary additional arguments we should add to the AJAX request
		var addtlDataJSON = $('input[name="msp-additionalQueryArgs"]').val();
		addtlDataJSON = $("<div/>").html(addtlDataJSON).text(); // decode the htmlentities-encoded string
		if (addtlDataJSON != '') {
			window.msp_addtlData = $.parseJSON(addtlDataJSON);
		}
	}
		
	// add additional arguments to the data variable, to be posted to server
	$.each(window.msp_addtlData, function(i, val) {
		data[i] = val;
	});
		
	var throbber = $("<img src='/images/throbber_small.gif' style='float:right;'>");
	$("#student-list-throbber").show();
	
	$('#msp_filter_form :input').attr('disabled', true);
	
	$.post(msp_ajaxSource, data, function(response){
		if(msp_canViewStudentNames){
			// Initialize the table- this only has to happen once.
			msp_initializeTable(response.columns);
			
			msp_initialized = true;
			
			studentList = $('#msp_student_list');
			studentListBody = $("<tbody></tbody>");
			
			// remove the current students...
			$('.msp_student_row').remove();
			
			$(response.students).each(function(i, el){
				newRow = $('<tr class="msp_student_row"></tr>');
				
				checkboxEl = $('<input class="msp_student_toggle_checkbox" type="checkbox" value="' + el.id + '" />');
				
				if($.inArray(parseInt(el.id), msp_selectedStudents) >= 0){
					checkboxEl.attr('checked', true);
				}
				
				checkboxColumn = $('<td class="checkboxCell"></td>').append(checkboxEl);
				
				newRow.append(checkboxColumn);

				seenId = false;
				
				for(pos in response.columns){
					prop = response.columns[pos];

					rowPropVal = el[prop];

					// If the row doesn't have a defined property for this column, display a blank
					// instead of 'undefined'
					if(rowPropVal != undefined){
						dataColumn = $('<td class="' + prop + '-cell productCell">' + el[prop] + '</td>');
					}else{
						dataColumn = $('<td class="productCell"></td>');
					}
					newRow.append(dataColumn);
				}
				
				//newRow.children(":not(.msp_student_toggle_checkbox)").click(function(e) {
				//	checkbox = $(this).parents("tr").find(".msp_student_toggle_checkbox");
				//	checkbox.attr("checked", !checkbox.attr("checked"));
				//});

				studentListBody.append(newRow);
				
						

			});
			
			studentList.append(studentListBody);
		}else{
			studentCount = response.students.length;
			
			$("#msp_student_list_container").hide();
			$(".msp_student_list_div").empty().append($('<div>Found ' + studentCount + ' student(s).</div>').css('height', 110));
			
			// Tuck in a hidden form element with the count so we can pull it out easily to test if any students have been "selected"...
			$(".msp_student_list_div").append($('<input type="hidden" id="msp_student_count" value="' + studentCount + '" />'));
			
			$('#' + msp_targetFormId).find("input").each(function(){
				if($(this).attr("name") == "studentIDs[]"){
					$(this).remove();
				}
			});
							
			$(response.students).each(function(index, el){
				$('#' + msp_targetFormId).append(msp_buildHiddenStudentIdElement(el.id));
			});
		}
		
		msp_update_filter_component(data);
		
		msp_postLoadCallback();
		
		$("#student-list-throbber").hide();
	});
}

/**
 * This function is just a collection of calls to update the filter component on the page.
 * @param data The post data that the filter form sent off initially
 */
msp_update_filter_component = function(data) {
	$('#msp_filter_form :input').attr('disabled', false);
	
	setRowFunctions();
	
	$("#checkAll").text("All");

	// update the filters text
	var newText = "Filters ";
	var addedSomething = false;
	var graduating = false;
	var status = false;
	var certLevel = false;
	var section = false;
	
	if(data['graduationMonth'] != 0){
		if(!addedSomething){
			newText += "-";
			addedSomething = true;
		}
		newText += " Graduating: " + $("select#graduationMonth :selected").text();
		graduating = true;
	}
	
	if(data['graduationYear'] != 0){
		if(!addedSomething){
			newText += "-";
			addedSomething = true;
			newText += " Graduating: " + data['graduationYear'];
			graduating = true;
		}
		newText += " " + data['graduationYear'];
	}
	
	if(data['graduationStatus']){
		status = true;
		
		if(!addedSomething){
			newText += "-";
			addedSomething = true;
		}
		
		if(graduating){
			newText += ",";
		}
		
		newText += " Status: ";
		$.each(data['graduationStatus'], function(key, value){
			if(value == 1){
				value = 'Active';
			}
			else if(value == 4){
				value = 'Left Program';
			}
			else if(value == 2){
				value = 'Graduated';
			}
			if(key > 0){
				newText += ",";
			}
			
			newText += " " + value;
		});

	}

	if(data['section'] != 0){
		section = true;

		if(!addedSomething){
			newText += "-";
			addedSomething = true;
		}
		
		if(graduating || status){
			newText += ",";
		}
		
		newText += " Section: " + $("select#section :selected").text();
	}
	
	$(".certLevelWrapper").find("input").each(function(){

		
		if($(this).attr("checked")){

			if(!addedSomething){
				newText += "-";
				addedSomething = true;
			}
			
			if((graduating || status || section) && !certLevel){
				newText += ",";
			}
			if(!certLevel){
				newText += " Certifications: " + $(this).parent().text();
			}
			else {
				newText += ", " + $(this).parent().text();
			}
			certLevel = true;

		}
	});
	
	$("#filters-title-text").text(newText);
	
	/*var width = $(".msp_student_row:first").find("td.Name-cell").width();
	$("td#nameHeader").css("width", (width + 1));*/
	
	// do some styling fixes
	// this is called twice, once after a delay. it's a hack! ugh, I know
	// Firefox does a page rendering shift right after the JS runs initially, which messes up the widths again. Hence the second run of this function.
	msp_fix_header_widths();
	setTimeout(msp_fix_header_widths, 1000);
	// also bind this to the event of the window/viewport being resized
	$(window).resize(function(e) {
		msp_fix_header_widths();
	});
	
	var theadHeight = $("thead").height() + 2;
	$(".msp_student_list_div").css("padding-top", (theadHeight + "px"));
}

// the table header is positioned absolutely so that it "stays" while the user scrolls vertically
// however this de-couples the normal HTML rendering that makes sure header cells are the same width as data cells
// so we need to manually copy widths
msp_fix_header_widths = function() {
	// do some styling fixes
	//$("#nameHeader").css("width", $(".Name-cell").width() + "px");
	var widths = [];
	var scount = 0;
	// collect the widths
	// .eq(0) makes sure we only loops through one row
	$(".msp_student_row").eq(0).find("td").each(function(){
		widths[scount] = $(this).outerWidth() + 1;
		scount++;
	});
	
	// set them to the thead
	var count = 0;
	$("#msp_student_list thead").find("td").each(function(){
		$(this).css("width", widths[count] + "px");
		count++;
	});
		

}

msp_updateClassSections = function(){
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
		
		msp_performFilter();
		
	}, 'json');
}

// This should be called on submission of the parent form.
// It injects a slew of HTML elements that will be posted along with the form.
msp_insertStudentList = function(){
	// If this is false, the vars get added immediately to the form after the filter AJAX call comes back.
	if(msp_canViewStudentNames){
		// Get the list of selected student IDs and Insert that into a new hidden 
		// element and inject that element into the specified form
		$('.msp_student_toggle_checkbox').each(function(i, el){
			if($(el).attr('checked')){
				$('#' + msp_targetFormId).append(msp_buildHiddenStudentIdElement($(el).val()));
			}
		});
	}
}

msp_buildHiddenStudentIdElement = function(studentId){
	hiddenEl = $('<input type="hidden" name="studentIDs[]" value="' + studentId + '"/>');
	return hiddenEl;
}

function getRowBackgroundColor()
{
	return $(".msp_student_row").css("background-color");
}

function setRowFunctions(){
	var normalColor = getRowBackgroundColor();
	
	// first, deal with any students that are already selected
	var count = 0;
	var firstCheckedLocation = 0;
	$("#msp_student_list").find("input").each(function(){
		var selectedColor = "#f3f3f3";
		if($(this).attr("checked") && !$(this).parent().hasClass("productInnerCell")){
			if(count == 0){
				firstCheckedLocation = $(this).position().top-55;
			}
			$(this).parent().parent().css("background-color", selectedColor);
			count++;
		}
	});
	$('#msp_student_list_container').animate({scrollTop:firstCheckedLocation}, 'slow');

	$(".checkboxCell").parent().find("input:first").click(function(){
		selectRow($(this), $(this).parent().parent(), normalColor, "");
	});

	$(".checkboxCell").click(function(){selectRow($(this).parent().find("input:first"), $(this).parent(), normalColor, "");});
	$(".Name-cell").click(function(){selectRow($(this).parent().find("input:first"), $(this).parent(), normalColor, "");});

}

function selectRow(checkbox, row, normalColor, checkAll){
	if(!row.hasClass("headRow")){
		var selectedColor = "#f3f3f3";
		var hoverColor = "#eee";
		
		var selected = false;
		
		if(checkAll == "all"){
			selected = true;
			row.css("background-color", selectedColor);
			checkbox.attr("checked", "checked");	
		}
		else if(checkAll == "none"){
			selected = false;
			row.css("background-color", normalColor);
			checkbox.removeAttr("checked");
		}
		else {
			// we're deselecting it
			if(checkbox.attr("checked")){
				selected = false;
				row.css("background-color", normalColor);
				checkbox.removeAttr("checked");
			}
			else {
				selected = true;
				row.css("background-color", selectedColor);
				checkbox.attr("checked", "checked");		
			}
		}
		
		row.hover(function(){
			if(!selected){
				$(this).css("background-color", hoverColor);
			}
			else {
				$(this).css("background-color", selectedColor);
			}
		}, function(){
			if(!selected){
				$(this).css("background-color", normalColor);
			}
			else {
				$(this).css("background-color", selectedColor);
			}
		});
	}

}
