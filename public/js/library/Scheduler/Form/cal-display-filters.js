$(function() {

	// mkae sure we have what we need before continuing (fall back if all else fails)
	if ($("#cd-filters-wrapper").length > 0) {
		
		// first things first - put everything into a fancy filter
		$("#cd-filters-wrapper").find("input").addClass("do-not-filter");
		$("#cd-filters-wrapper").fancyFilter({
			width:902,
			closeOnChange: false,
			onFilterSubmit: function(e) {}
		});
		
	//	$("#cal-display-filters").css("top", ($("#filters_place_holder").offset().top));
		$("#cal-display-filters").css("top", "19em").fadeIn();
		$(".action-buttons").find("a").button();
		
		initSliders();
		initChosens();
		buildStudentObjects();
		
		$("#submit-filters-btn").click(function(e){
			e.preventDefault();
			submitFilters(true);
		});
				
		$("#reset-filters-btn").click(function(e){
			e.preventDefault();
			
			$.each(updatedFilters, function(i, v){resetChosen(v);});
			$.each(filters_cols, function(i, v){resetTopSwitches(v);});
			
			//$("#available_open_window_filters-element").find(".slider-button").removeClass("on");
			//$("#available_open_window_filters-element").find(".slider-button").text("off");
			$("#available_open_window_filters").removeAttr("checked");
			
			resetGrad("month");
			resetGrad("year");
			
			$(".disabled-chzn-option").removeClass("disabled-chzn-option");

			if (isStudent()) {
				setLoggedInStudent();
			}
			
			updateAllLabels();
		});
		
		var stus = getOptionValues($("#students_filters"));
		$("#students_filters").chosen().change(function(){addStudentLabel();});
		addOptionValAttrib($("#students_filters_chzn"), stus);
		
		var preceptors = getOptionValues($("#preceptors_filters"));
		$("#preceptors_filters").chosen().change(function(){addPreceptorLabel();});
		addOptionValAttrib($("#preceptors_filters_chzn"), preceptors);
		
		var bases = getOptionValues($("#bases_filters"));
		$("#bases_filters").chosen().change(function(){addBasesLabel();});
		addOptionValAttrib($("#bases_filters_chzn"), bases);
		
		$("#available_filters-label").find("label").click(function(e){e.preventDefault();e.stopPropagation();});
		$("#chosen_filters-label").find("label").click(function(e){e.preventDefault();e.stopPropagation();});
		
		$("#available_filters").change(function(){
			disablePeopleOptions($(this).attr("checked"), "available", $("#avail-blocker"));
			setSliderTitle($(this), "available");
		});
		$("#chosen_filters").change(function(){
			disablePeopleOptions($(this).attr("checked"), "chosen", $("#chosen-blocker"));
			setSliderTitle($(this), "chosen");
		});
		
		updateStudentList();
		updateAllLabels();
		
		$("body").click(function(e) {
			var el = $(e.target);
			if (el.hasClass("search-choice-close")) {updateAllLabels();}
		});
	
		$("#chosen_cert_filters").change(function(){updateStudentList();});
		$("#chosen_group_filters").change(function(){updateStudentList();});
		$("#grad_filters-month").change(function(){updateStudentList();});
		$("#grad_filters-year").change(function(){updateStudentList();});
		
		$("#bases_filters_chzn").find(".group-option").click(function(){
			var id = $(this).attr("id").split("chzn_o");
			var selectedElement = $("#bases_filters_chzn_c" + id[1]);
			var optGroupName = $(this).prevAll(".group-result").first().text();
			selectedElement.find("span").text(optGroupName + ": " + $(this).text());
		});
		
		
		// update filters!
		$.each(updatedFilters, function(i,v){
			
			var chzn = $("#" + v + "_filters_chzn");
			
			chzn.find(".search-field").find("input").blur(function(){
				if ($("#" + v + "_filters").val()) {$("." + v + "-chosen-text").remove();}
				else {
					if (v == "students") {addStudentLabel();}
					else if (v == "preceptors") {addPreceptorLabel();}
					else if (v == "bases") {addBasesLabel();}
					else {addStandardLabel(v)}
				}
			});
			
			
			chzn.find(".search-field").find("input").focus(function(){
				// we're focused and typing...remove all labels
				$("." + v + "-chosen-text").remove();
			});
			
		});
		
		var sites = getOptionValues($("#sites_filters"));
		
		/*
		 * What happens when site filters changes?
		 */
		$("#sites_filters").chosen().change(function(e){
			siteChangeListener($(this));
		});
	
		siteChangeListener($("#sites_filters"));
		updateFiltersText();
		
		$("#cal-display-filters").change(function(){
			
			$("#cd-filters-wrapper_filters-title-text").html("Filters: <i>Editing</i>");
			
		});
		
		
		$("#sites_filters_chzn").find(".search-field").find("input").focus(function(){
			// we're focused and typing...remove all labels
			$(".sites-chosen-text").remove();
		});
		
		$("#sites_filters_chzn").find(".search-field").find("input").blur(function(){
			addStandardLabel("sites");
		});
	}
	
	addOptionValAttrib($("#sites_filters_chzn"), sites);
	
	
	if (!$("#chosen_filters-element").find(".slider-button").hasClass("on")) {
		disablePeopleOptions(false, "chosen", $("#chosen-blocker"));
	}
	
	if (!$("#available_filters-element").find(".slider-button").hasClass("on")) {
		disablePeopleOptions(false, "available", $("#available-blocker"));
	}
	
	setTitle();
	setInteractions($("#cal-controls-inside-wrapper").find(".selected").text().toLowerCase());
	submitFilters(false);

});

var filters_cols = ["chosen", "available"];
var filters_allStudents = {};
var updatedFilters = ["students", "preceptors", "bases", "sites", "available_cert", "available_group", "chosen_cert", "chosen_group"];
		
/*
* get value from chzan option
*/
function getValueFromChznOption(aSelect, text) {

   var val = -1;
   aSelect.find("option").each(function(){
	   if ($(this).text() == text) {
		   val = $(this).val();
	   }
   });

   return val;
}

/*
* update site dependent
*/
function updateSiteDependent(dependentName, sites) {
   var dChzn = $("#" + dependentName + "_filters_chzn");
   dChzn.find(".chzn-results").find(".group-result").each(function(){

	   var name = $(this).text();
	   if (sites.length == 0) {keep = 1;}
	   else {keep = $.inArray(name, sites);}

	   if (keep != -1) {
		   $(this).removeClass("hidden-group-result");
		   $(this).nextUntil(".group-result").each(function(){
			   if (!$(this).hasClass("result-selected")) {
				   $(this).removeClass("hidden-sd-option");
			   }

		   });
	   }
	   else {
		   $(this).addClass("hidden-group-result");
		   $(this).nextUntil(".group-result").each(function(){

			   if ($(this).hasClass("result-selected")) {
				   var chosenId = $(this).attr("id").split("_o_");
				   chosenId = chosenId[1];

				   $("#" + dependentName + "_filters_chzn_c_" + chosenId).remove();
				   $(this).removeClass("result-selected").addClass("active-result");
				   dChzn.trigger("change");
			   }

				   $(this).addClass("hidden-sd-option");

		   });
	   }
   });

   dChzn.find(".chzn-choices").effect("highlight", {}, 800);
   updateAllLabels();
}

/*
* reset top switches
*/
function resetTopSwitches(name) {
	
	if (!$("#" + name + "_filters-element").find(".slider-button").hasClass("on")) {
		$("#" + name + "_filters-element").find(".slider-button").trigger("click");
	}
		
 //  $("#" + name + "_filters-element").find(".slider-button").addClass("on").text("Show");
 //  $("#" + name + "_filters").attr("checked", "checked");
   disablePeopleOptions(true, name, $("#avail-blocker"));
   disablePeopleOptions(true, name, $("#chosen-blocker"));
}

/*
* reset chosens
*/
function resetChosen(elementName) {

   $("#" + elementName + "_filters").find("option").each(function(){
	   $(this).removeAttr("selected");
   });

   $("#" + elementName + "_filters_chzn").find(".result-selected").each(function(){
	   $(this).removeClass("result-selected").addClass("active-result");
   });

   $("#" + elementName + "_filters_chzn").find(".hidden-sd-option").removeClass("hidden-sd-option");
   $("#" + elementName + "_filters_chzn").find(".hidden-group-result").removeClass("hidden-group-result");
   $("#" + elementName + "_filters_chzn").find(".search-choice").remove();
}

/*
* reset grad
*/
function resetGrad(name) {
   $("#grad_filters-" + name).find("option").each(function(){

	   if ($(this).text() == "All " + name + "s") {
		   $(this).attr("selected", "selected");
	   }
	   else {
		   $(this).removeAttr("selected");
	   }

   });

   $("#grad_filters-" + name).trigger("change");
   $("#grad_filters_" + name + "_chzn").find(".chzn-single").find("span").text("All " + name + "s");
   $("#grad_filters_" + name + "_chzn").find(".result-selected").removeClass("result-selected");
   $("#grad_filters_" + name + "_chzn_o_0").addClass("result-selected");
}

/*
* get grad date description
*/
function getGradDate() {
	var gradDate = "";
	var month = $("#grad_filters_month_chzn").find(".result-selected").text();
	var year = $("#grad_filters_year_chzn").find(".result-selected").text();
	
	if (month != "All months") {
		gradDate += month;
	}
	
	if (year != "All years") {
		// if there was a month, add a space before the year
		if (gradDate) { gradDate += " "; }
		gradDate += year;
	}
	
	return gradDate;
}

/*
* get all sites for a given type
*/
function getAllSitesForType(chosen, type) {
   var sites = [];
   chosen.find(".group-result").each(function(){
	   if ($(this).text() == type){
		   $(this).nextUntil(".group-result").each(function(){
			   sites.push($(this).text());
		   });
	   }
   });
   return sites;
}

/*
* get sites
*/
function getSites() {
   var sites = [];
   $("#sites_filters_chzn").find(".result-selected").each(function(){
	   if ($(this).text() == "All Clinical Sites") {
		   $.each(getAllSitesForType($("#sites_filters_chzn"), "Clinical"), function(i, v){sites.push(v);});
	   }
	   else if ($(this).text() == "All Field Sites") {
		   $.each(getAllSitesForType($("#sites_filters_chzn"), "Field"), function(i, v){sites.push(v);});
	   }
	   else if ($(this).text() == "All Lab Sites"){
		   $.each(getAllSitesForType($("#sites_filters_chzn"), "Lab"), function(i, v){sites.push(v);});
	   }
	   else {
		   sites.push($(this).text());
	   }
   });
   return sites;
}

/*
* add the options value as an attribute to the chosen options
*/
function addOptionValAttrib(chzn, vals) {
   var count = 0;
   chzn.find(".chzn-results").find("li").each(function(){
	   if (!$(this).hasClass("group-result")) {
		   $(this).attr("data-optionVal", vals[count]);
		   count++;
	   }
   });

}

/*
* build an object that contains the value
*/
function getOptionValues(oSelect) {
   var returnObject = {};
   var count = 0;
   oSelect.find("option").each(function(){
	   returnObject[count] = $(this).val();
	   count++;
   });
   return returnObject;
}

/*
* Update student list - when it's filtered
*/
function updateStudentList() {

   certs = getCerts();
   groups = getGroups();
   
   month = getGradMonth();
   year = getGradYear();
   var newOptions = {};
   var newOptionsChosenIds = [];
   var newOptionsCount = 0;

   $.each(filters_allStudents, function(id, attribs){
	   var hasCert = $.inArray(attribs.cert, certs);
	   
	   var hasGroup = false;
	   if (groups == "All") {
		   hasGroup = true;
	   }
	   else {
		   $.each(attribs.groups, function(i, v){
			   if ($.inArray(v, groups) != -1) {
				   hasGroup = true;
			   }
		   });
	   }
	   

	   var hasMonth = false;
	   var hasYear = false;
	   if ((parseInt(month) == parseInt(attribs.gradMonth)) || month == "All months") {hasMonth = true;}
	   if ((parseInt(year) == parseInt(attribs.gradYear)) || year == "All years") {hasYear = true;}
	   if ((hasCert != -1) && (hasMonth) && (hasYear) && (hasGroup)) {
		   // it has everything, we can include it
		   newOptions[id] = attribs;
		   newOptionsChosenIds.push(attribs.chosenId);
		   newOptionsCount++;
       }
   });

   $("#students_filters_chzn").find(".chzn-results").find("li").addClass("hidden-sd-option");

   $.each(newOptions, function(id, attribs){
	   var chosenIdTxt = "students_filters_chzn_o_" + attribs.chosenId;
	   var selectedChosenIdTxt = "students_filters_chzn_c_" + attribs.chosenId;
	   if (!$("#" + chosenIdTxt).hasClass("result-selected")) {
		   $("#" + chosenIdTxt).removeClass("hidden-sd-option");
	   }
   });

   $("#students_filters_chzn").find(".result-selected").each(function(){

	   // if these aren't in our "newOptions" array, we need to remove them
	   var myChosenId = $(this).attr("id").split("_o_");
	   myChosenId = parseInt(myChosenId[1]);
	   var isThere = $.inArray(myChosenId,newOptionsChosenIds);

	   if (isThere == -1) {
		   // it's not there... delete it
		   $("#students_filters_chzn_c_" + myChosenId).remove();
		   $(this).removeClass("result-selected").addClass("active-result");
	   }

   });

   // for each of the selected ones, find out if they are in the list
   $("#students_filters_chzn").find(".chzn-choices").effect("highlight", {}, 800);
   addStudentLabel();
}



/*
 * update all labels
 */
var updateAllLabels;
updateAllLabels = function() {
	$(".search-field").find("input").addClass("white-text");

	addPreceptorLabel();
	addStudentLabel();
	addBasesLabel();

	$.each(updatedFilters, function(i, v){
		if (v != "preceptors" && v != "students" && v != "bases") {
			addStandardLabel(v);
		}
	});
}

/*
 * for the basic labels
 */
var addStandardLabel;
addStandardLabel = function(name) {
	$("." + name + "-chosen-text").remove();
	var text = "";
	if ($("#" + name + "_filters_chzn").find(".result-selected").length == 0) {

		var displayName = name;

		if (name == "available_cert" || name == "chosen_cert") {
			displayName = "certification levels";
		}
		else if (name == "available_group" || name == "chosen_group") {
			displayName = "student groups";
		}
		
		var item_count = getStandardListCount(name);
		
		if (item_count > 0) {
			if (item_count == 1) {
				text = "1 " + displayName.substring(0, displayName.length - 1) + "...";
			}
			else {
				text = "All " + item_count + " " + displayName + "...";
			}
		}
		else {
			text = "0 " + displayName;
		}

		$("#" + name + "_filters_chzn").find(".search-field").append("<div class='" + name + "-chosen-text'>" + text + "</div>");

	}
}

/*
 * add student label
 */
var addStudentLabel;
addStudentLabel = function() {
	// no students selected
	// do we have All certs/months/years?
	$(".students-chosen-text").remove();
	var text = "";
	
	if (($("#students_filters_chzn").find(".result-selected").length == 0)) {
		if (getStudentListCount() > 0) {
			var hasSelections = $("#students_filters_chzn").find(".chzn-choices").find(".search-choice").length;
			if (hasSelections == 0) {
				var item_count = getStudentListCount();
				
				if (item_count == 1) {
					text = "1 student...";
				}
				else {
					text = "All " + item_count + " students...";
				}
			}
			else {
				// the text should say 'All students matching above criteria'
				text = "All " + getStudentListCount() + " students matching above criteria...";
			}
		}
		else {
			text = "0 students match above criteria...";
		}

		$("#students_filters_chzn").find(".search-field").append("<div class='students-chosen-text'>" + text + "</div>");
	}
}

/*
 * add preceptor label
 */
var addPreceptorLabel;
addPreceptorLabel = function() {siteDependentLabel("preceptors");}

/*
 * add bases label
 */
var addBasesLabel;
addBasesLabel = function() {siteDependentLabel("bases");}

/*
 * site dependent label
 */
var siteDependentLabel;
siteDependentLabel = function(dependentName) {

	var dChzn = $("#" + dependentName + "_filters_chzn");
	var listCount = getStandardListCount(dependentName);

	$("." + dependentName + "-chosen-text").remove();
	if (dChzn.find(".result-selected").length == 0) {

		var text = "";
		var sites = getSites();

		if (sites.length == 0) {
			if (listCount == 1) {
				text = "1 " + dependentName.substring(0, dependentName.length-1) + "...";
			}
			else {
				text = "All " + listCount + " " + dependentName + "...";
			}
			
		}
		else {
			if (listCount > 0) {
				if (listCount == 1) {
					text = "1 " + dependentName.substring(0, dependentName.length-1) + " for selected sites...";
				}
				else {
					text = "All " + listCount + " " + dependentName + " for selected sites...";
				}
				
			}
			else {
				text = "0 " + dependentName + " for selected sites...";
			}
		}

		dChzn.find(".search-field").append("<div class='" + dependentName + "-chosen-text'>" + text + "</div>");
	}


}

/*
 * get standard list count
 */
var getStandardListCount;
getStandardListCount = function(dependentName) {
	var count = 0;
	$("#" + dependentName + "_filters_chzn").find(".chzn-results").find("li").each(function(){
		if (!$(this).hasClass("group-result")) {
			if ($(this).css("display") != "none"){
				// don't count hte "All Clinical Sites", "All Field Sites" or "All lab sites"
				if ($(this).text().indexOf("All ") == -1) {
					count++;
				}
			}
		}
	});
	return count;
}

/*
 * get selected certs (for chosen shifts)
 */
var getCerts;
getCerts = function() {
	var certs = [];
	if ($("#chosen_cert_filters").val()) {
		$("#chosen_cert_filters").find("option").each(function() {
			$el = $(this);

			if ($el.attr("selected")) {
				certs.push($el.text());
			}
		});
	}
	else {
		$("#chosen_cert_filters").find("option").each(function() {
			$el = $(this);

			certs.push($el.text());
		});
	}
	return certs;
}

/*
 * get selected groups (for chosen shifts)
 */
var getGroups;
getGroups = function() {
	var groups = [];
	var selected_results = $("#chosen_group_filters_chzn").find(".chzn-results").find(".result-selected");
	if (selected_results.length > 0) {
		
		selected_results.each(function(){
			groups.push($(this).attr("data-optionval"));
		});
		
	}
	else {
		groups = "All";
	}
	
	return groups;
}

/*
 * create sliding checkboxes!
 */
var initSliders;
initSliders = function() {
	$("#available_filters").sliderCheckbox({onText: 'On', offText: 'Off'});
	$("#chosen_filters").sliderCheckbox({onText: 'On', offText: 'Off'});
	// set the titles, too
	setSliderTitle($("#available_filters"), "available");
	setSliderTitle($("#chosen_filters"), "chosen");
}

/*
 * build studnet object from the originial select box
 */
var buildStudentObjects;
buildStudentObjects = function() {
	var chosenId = 0;
	$("#students_filters").find("option").each(function(){

		var attribs = $(this).text().split("|");
		var name = attribs[0];
		var cert = attribs[1];
		var gradMonth = attribs[2];
		var gradYear = attribs[3];
		var groups = attribs[4].split(",");
		

		$(this).attr("data-certLevel", cert);
		$(this).attr("data-gradMonth", gradMonth);
		$(this).attr("data-gradYear", gradYear);

		$(this).text(name);

		filters_allStudents[$(this).val()] = {name:name,cert:cert,gradMonth:gradMonth,gradYear:gradYear,groups:groups,chosenId:chosenId};
		chosenId++;
	});
}

/*
 * init most of the chosens on hte page
 */
var initChosens;
initChosens = function() {
	$.each(filters_cols, function(i, v){

		var cVals = getOptionValues($("#" + v + "_cert_filters"));
		$("#" + v + "_cert_filters").chosen().change(function(){updateAllLabels();});
		addOptionValAttrib($("#" + v + "_cert_filters_chzn"), cVals);

		var gVals = getOptionValues($("#" + v + "_group_filters"));
		$("#" + v + "_group_filters").chosen().change(function(){updateAllLabels()});
		addOptionValAttrib($("#" + v + "_group_filters_chzn"), gVals);

	});

	$("#grad_filters-label").find("label").text("Graduation date");

	$("#grad_filters-month").find("option").first().text("All months");
	$("#grad_filters-month").css("width", "126px").chosen();
	$("#grad_filters_month_chzn").find(".chzn-drop").css("width", "124px");
	$("#grad_filters_month_chzn").find("input").css("width", "90px");

	$("#grad_filters-year").find("option").first().text("All years");
	$("#grad_filters-year").css("width", "126px").chosen();
	$("#grad_filters_year_chzn").find(".chzn-drop").css("width", "124px");
	$("#grad_filters_year_chzn").find("input").css("width", "90px");
}

/*
 * Adjuts selected options (For sites)
 */
var adjustSelectedOptions;
adjustSelectedOptions = function(type) {

	chosen = $("#sites_filters");

	getAllOption(type).nextUntil(".group-result").each(function(){

		$(this).addClass("disabled-chzn-option");
		var id = $(this).attr("id").split("chzn_o");
		var selectedElement = $("#sites_filters_chzn_c" + id[1]);
		var optionText = selectedElement.find("span").text();

		if (selectedElement.length > 0) {

			selectedElement.remove();
			$(this).removeClass("result-selected").addClass("active-result");

			var val = null;

			$("#sites_filters").find("option").each(function(){
				if ($(this).text() == optionText){
					$(this).attr("selected", false);
					val = $(this).attr("value");
				}
			});

			var newVal = [];
			$.each(chosen.val(), function(index, value){
				if (value != val) {
					newVal[index] = value;
				}
			});

			chosen.val(newVal);
			updateAllLabels();
		};

	});
}
	
/*
 * site change listener
 */
var siteChangeListener;
siteChangeListener = function(chosen) {
	allClinical = false;
	allLab = false;
	allField = false;

	if (chosen.val()) {
		$.each(chosen.val(), function(index, value){
			if (value == "0-Clinical") {allClinical = true;}
			if (value == "0-Field") {allField = true;}
			if (value == "0-Lab") {allLab = true;}
		});
	}

	if (allClinical) {
		adjustSelectedOptions("Clinical");
	}
	else {
		if (getAllOption("Clinical")) {		
			getAllOption("Clinical").nextUntil(".group-result").removeClass("disabled-chzn-option");
		}
	}
	
	if (allLab) {
		adjustSelectedOptions("Lab");
	}
	else {
		if (getAllOption("Lab")) {		
			getAllOption("Lab").nextUntil(".group-result").removeClass("disabled-chzn-option");
		}
	}
	
	if (allField) {
		adjustSelectedOptions("Field");
	}
	else {
		if (getAllOption("Field")) {
			getAllOption("Field").nextUntil(".group-result").removeClass("disabled-chzn-option");
		}
	}

	updateAllLabels();

	var sites = getSites();
	updateSiteDependent("preceptors", sites);
	updateSiteDependent("bases", sites);
}

var submitFilters;
submitFilters = function(close_filters) {
	var data = getFilters();
	loadNewCalendar(getViewType(), getDate(), getEndDate(), data);
	if (close_filters) {
		$("#cd-filters-wrapper_filters-title").trigger("click"); // close filters
	}
	updateFiltersText();
}

/*
 * get all option
 */
var getAllOption;
getAllOption = function(type) {
	var option = null;
	$("#sites_filters_chzn").find(".group-option").each(function(){
		var phrase = "All " + type + " Sites";
		if ($(this).text() == phrase) {
			option = $(this);
		}
	});

	return option;
}

/*
 * disable options
 */
var disablePeopleOptions;
disablePeopleOptions = function(checked, type, blocker) {
	if (checked) {
		$("." + type + "-filters-elements").css("opacity", "1");
		blocker.hide();
	}
	else {
		$("." + type + "-filters-elements").css("opacity", "0.5");
		blocker.css("height", ($("." + type + "-filters-elements").height()+10) + "px");
		blocker.show();
	}
}

/*
 * set the title to display on hover for the sliders
 */
var setSliderTitle;
setSliderTitle = function(slider, type) {
	if ($(slider).attr("checked")) {
		var text = 'click to hide ' + type + ' shifts';
	} else {
		var text = 'click to show ' + type + ' shifts';
	}
	$("#"+type+"_filters-stage").attr('title', text);
}

/*
 * get student list count (which are available in hte drop down)
 */
var getStudentListCount;
getStudentListCount = function() {
	var count = 0;
	$("#students_filters_chzn").find(".chzn-results").find("li").each(function(){
		if ($(this).css("display") != "none"){
			count++;
		}
	});
	return count;
}

/*
 * set the logged in student to the only chosen student
 */
var setLoggedInStudent;
setLoggedInStudent = function(){
	var user_id = $(".student-presets").attr("data-userContextId");

	$('#students_filters').val('');
	$('#students_filters').val(user_id);
	
	$("#students_filters_chzn").find(".chzn-results").find("li").each(function(){
		if ($(this).attr("data-optionval") == user_id) {
			setSelectedForChosen($(this), $("#students_filters_chzn"), $('#students_filters'));
			return false;
		}
		return true;
	});
	
}

/*
 * manual select for chosens
 */
var setSelectedForChosen;
setSelectedForChosen = function(chzn_option, chzn, html_select){
	
	chzn_option.addClass("result-selected").removeClass("active-result");
			
	var idPieces = chzn_option.attr("id").split("_");
	var searchChoiceId = idPieces[0] + "_" + idPieces[1] + "_" + idPieces[2] + "_c_" + idPieces[4];
	var chosenEl = '<li class="search-choice" id="' + searchChoiceId + '"><span>' + chzn_option.text() + '</span><a href="javascript:void(0)" class="search-choice-close custom-close"></a></li>';
	chzn.find(".chzn-choices").prepend(chosenEl);
	
	$(".custom-close").unbind();
	$(".custom-close").click(function(e){
		e.preventDefault();
		var chosenLi = $(this).parent();
		var cIdPieces = chosenLi.attr("id").split("_");
		var optId = cIdPieces[0] + "_" + cIdPieces[1] + "_" + cIdPieces[2] + "_o_" + cIdPieces[4];
		$("#" + optId).removeClass("result-selected").addClass("active-result");
		chosenLi.remove();
		html_select.val('');
	});
	
}

/*
 * update the filters bar text
 */
var updateFiltersText;
updateFiltersText = function() {
	var data = getFilters();
	
	filters_text = "";
	var normal_description = true;
	
	
	if (isStudent()) {
		
		// if we have a preset turn on use that desription, otherwise be normal

		// first check for "my shifts"
		if (hasMyShiftsPreset()) {
			normal_description = false;
			filters_text = "Filters: showing my shifts";
		}
		
		else if (hasAvailablePreset()) {
			
			var type = $("#sites_filters_chzn").find(".chzn-choices").find(".search-choice").first().text().split(" ");
			if (type[1]) {
				type = type[1].toLowerCase();
			}
			
			normal_description = false;
			filters_text = "Filters: showing all available " + type + " shifts";
		}
		
	}
	
	if (normal_description) {
		location_text = getLocationText();
		avail_text = getAvailText(data['show_avail']);
		chosen_text = getChosenText(data['show_chosen']);
		if (avail_text || chosen_text) {
			filters_text = "Filters: showing";
			if (!isStudent()) {
				filters_text += " shifts";
			}
			if (avail_text && chosen_text) {
				avail_text += ",";
			}
			filters_text = filters_text + location_text + avail_text + chosen_text;
		} else {
            filters_text = "Filters: no shifts";
        }
	}

	
	$("#cd-filters-wrapper_filters-title-text").text(filters_text);
}

/*
 * are the filters currently configured to match what owuld be one of the available presets?
 */
var hasAvailablePreset;
hasAvailablePreset = function(){
	
	var has_preset = true;
	
	if(!$("#available_filters-element").find(".slider-button").hasClass("on")){
		has_preset = false;
	}
	
	if($("#chosen_filters-element").find(".slider-button").hasClass("on")){
		has_preset = false;
	}
	
	if (chosenHasSelections($("#bases_filters_chzn"))) {
		has_preset = false;
	}

	if (chosenHasSelections($("#preceptors_filters_chzn"))) {
		has_preset = false;
	}
	
	return has_preset;
}

/*
 * are the filters currently configured to match what would be "my shifts"
 */
var hasMyShiftsPreset;
hasMyShiftsPreset = function(){
	
	var has_preset = true;
	
	if($("#available_filters-element").find(".slider-button").hasClass("on")){
		has_preset = false;
	}
	
	if(!$("#chosen_filters-element").find(".slider-button").hasClass("on")){
		has_preset = false;
	}
	
	// they are a student who cna view others schedules - make sure just they are selected
	if ($("#students_filters_chzn").length > 0) {
		var my_user_id = $(".student-presets").attr("data-userContextId");
		var selections_count = 0;
		var im_selected = false;
		$("#students_filters_chzn").find(".chzn-choices").find(".search-choice").each(function(){
			selections_count++;
			var idPieces = $(this).attr("id").split("_");
			var optId = idPieces[0] + "_" + idPieces[1] + "_" + idPieces[2] + "_o_" + idPieces[4];
			if($("#" + optId).attr("data-optionval") == my_user_id){
				im_selected = true;
				return false;
			}
			return true;
		});
		
		if (selections_count != 1) {
			has_preset = false;
		}
	
		if (!im_selected) {
			has_preset = false;
		}
	}
	
	// sites/bases/preceptors must all be blank
	
	if (chosenHasSelections($("#sites_filters_chzn"))) {
		has_preset = false;
	}
	
	if (chosenHasSelections($("#bases_filters_chzn"))) {
		has_preset = false;
	}

	if (chosenHasSelections($("#preceptors_filters_chzn"))) {
		has_preset = false;
	}
	
	return has_preset;
}

var chosenHasSelections;
chosenHasSelections = function(chzn) {
	has_em = false;
	if (chzn.find(".chzn-choices").find(".search-choice").length > 0) {
		has_em = true;
	}
	
	return has_em;
}


/*
 * get the description of the current 'location' filters
 */
var getLocationText;
getLocationText = function(){
	
	location_text = "";
	
	var selected_bases = $("#bases_filters_chzn").find(".chzn-choices").find(".search-choice");
			
	if (selected_bases.length > 0) {
		location_text = " at ";
		
		if (selected_bases.length > 1) {
			location_text += selected_bases.length + " bases/departments";
		} else {
			location_text += selected_bases.first().find("span").text();
		}

	} else {
		var selected_sites = $("#sites_filters_chzn").find(".chzn-choices").find(".search-choice");
			
		if (selected_sites.length > 0) {
			location_text = " at ";
			
			if (selected_sites.length > 1) {
				location_text += selected_sites.length + " sites";
			} else {
				location_text += selected_sites.first().find("span").text();
			}
		}
	}
	
	var selected_preceptors = $("#preceptors_filters_chzn").find(".chzn-choices").find(".search-choice");
			
	if (selected_preceptors.length > 0) {
		location_text += " with ";
		
		if (selected_preceptors.length > 1) {
			location_text += selected_preceptors.length + " selected preceptors";
		} else {
			location_text += selected_preceptors.first().find("span").text();
		}

	}
	
	if (isStudent() && location_text) {
		location_text = " shifts" + location_text;
	}
	
	if (location_text) {
		location_text += ":";
	}
	
	return location_text;
}

/*
 * get the description of the current 'available' filters
 */
var getAvailText;
getAvailText = function(showAvail){
	
	avail_text = "";
	if (showAvail) {
		
		if (!isStudent()) {
			avail_text = " available to ";
			var selected_certs = $("#available_cert_filters_chzn").find(".chzn-choices").find(".search-choice");
			var selected_groups = $("#available_group_filters_chzn").find(".chzn-choices").find(".search-choice");
			var has_flag = $("#available_open_window_filters").attr("checked");
			
			if (selected_certs.length + selected_groups.length > 1) {
				avail_text += " specific students";
			}else if (selected_certs.length + selected_groups.length == 0) {
				avail_text += " any student";
			} else if (selected_certs.length == 1) {
				avail_text += " " + selected_certs.first().find("span").text() + " students";
			} else {
				avail_text += " students in " + selected_groups.first().find("span").text();
			}
			
			if (has_flag) {
				avail_text += " (hide invisible)";
			}
			
		} else {
			avail_text = " available shifts";
		}

	}
	
	return avail_text;
}

/*
 * get the description of the current 'chosen' filters
 */
var getChosenText;
getChosenText = function(showChosen){
	chosen_text = "";
	if (showChosen) {
		
		if (!isStudent()) {
			chosen_text = " chosen by ";
			var selected_certs = $("#chosen_cert_filters_chzn").find(".chzn-choices").find(".search-choice");
			var selected_groups = $("#chosen_group_filters_chzn").find(".chzn-choices").find(".search-choice");
			var selected_grad_date = getGradDate();
			var selected_students = $("#students_filters_chzn").find(".chzn-choices").find(".search-choice");
			
			// if there's more than one criterion
			if (selected_certs.length + selected_groups.length + (selected_grad_date.length > 0) + selected_students.length > 1) {
				chosen_text += " specific students";
			}
			// if there are no criteria
			else if (selected_certs.length + selected_groups.length + selected_grad_date.length + selected_students.length < 1) {
				chosen_text += " any student";
			} else if (selected_certs.length == 1) {
				chosen_text += " " + selected_certs.first().find("span").text() + " students";
			} else if (selected_groups.length == 1) {
				chosen_text += " students in " + selected_groups.first().find("span").text();
			} else if (selected_grad_date.length > 0) {
				chosen_text += " students graduating in " + selected_grad_date;
			} else {
				chosen_text += selected_students.first().find("span").text();
			}
			
		} else {
			chosen_text = " chosen shifts";
		}

	}
	
	return chosen_text;
}


/*
 * is the current user a student? we'll see their presets if they are...
 */
var isStudent;
isStudent = function(){
	var student_presets = $(".student-presets");
			
	if (student_presets.length > 0) {
		return true;
	}
	
	return false;
}
