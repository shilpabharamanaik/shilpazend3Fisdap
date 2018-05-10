$(function(){

	
	overRideContainsForSearching();
	disableEnterKey();
	
	initTabs();
	
	$("#tabs").find(".selected-tab").trigger("click");
	$("#merge-bases").button().button("disable");
	$("#merge-preceptors").button().button("disable");
		
	initBaseFormFunctions();
    initPreceptorFormFunctions();
    initSiteStaffFormFunctions();
	initAccreditationFormFunctions();

	// hide the activation toggle from the form
	$("#activateSite").hide();
	
	// toggle activation
	initSliderCheckboxes($("#toggle-active"));
	
	$("#toggle-active").change(function(){
		$("#inactive-warning").slideUp();
		
		var site_id = $(this).attr("data-siteid");
		if ($(this).attr("checked") == "checked") {
			var active = 1;
		} else {
			var active = 0;
		}
			
		$.post("/account/sites/toggle-active", {"site" : site_id, "active" : active },
			function(response) {
				// toggle the switch in the site info form, too
				if (response) {
					$("#siteInfoForm #active-slider-button").click();
					
					// if we just turned the site off, show that warning
					if (active == 0) {
						$("#inactive-warning").slideDown();
					}
				}
			}
		);
	});
	
	if($("#sharing_icon").length > 0){
		var sharing_icon = $("#sharing_icon");
		$("#sharing_icon").remove();
		$("#site-info").prepend(sharing_icon);
		$("#toggle-site-active").css("margin-right", "2em");
		$("#edit-site-info").css("left", "7em");
	}
	
	// open edit form
	$("#edit-site-info").click(function(e){
		e.preventDefault();
		$("#toggle-site-active").hide();
		
		$("#simple-info").slideUp();
		$("#edit-form").slideDown(400, function(){
			$("#toggle-site-active").show();
		});
	});
});

var overRideContainsForSearching = function()
{
	$.expr[":"].contains = $.expr.createPseudo(function(arg) {
	 return function( elem ) {
			return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
		};
	});	
}

var disableEnterKey = function()
{
	$('#main-content').bind("keyup keypress", function(e) {
		var code = e.keyCode || e.which; 
		if (code  == 13) {               
		  e.preventDefault();
		  return false;
		}
	});
}

var initTabs = function()
{
	$("#tabs").find("a").click(function(e){
		
		e.preventDefault();
		
		$(".open-content-div").hide();
		$(".selected-tab").removeClass("selected-tab").css("width", "");
		
		$(this).addClass("selected-tab");
		$("#" + $("#tabs").find(".selected-tab").attr("data-contentdiv")).addClass("open-content-div").fadeIn();
		
		// do a little browser/platform-specific styling
		// mac
		if (navigator.platform.indexOf('Mac') > -1) {
			$(this).css("width", "90.3%");
		}
	});
	
}

var initSliderCheckboxes = function(slider_checkbox)
{
	slider_checkbox.sliderCheckbox({onText: "Active", offText: "Inactive", "width": 55});
}

var updateAccordionTopElementBorder = function(accordion)
{	
	accordion.find(".first-accordion-element").removeClass("first-accordion-element");
	var found_first_visible = false;
	accordion.find(".accordion_element_header").each(function(){
		if ($(this).parent().css("display") != "none") {
			if (!found_first_visible) {
				$(this).addClass("first-accordion-element");
				found_first_visible = true;
				return;
			}
		}
	});
}

var initAccordion = function(accordion)
{
	if (accordion.find(".accordion_element").length > 0) {
		accordion.parent().parent().find(".hide-when-no-accordion").fadeIn();
		accordion.parent().parent().find(".show-when-no-accordion").hide();
		
		updateAccordionTopElementBorder(accordion);
		
		accordion.find(".accordion_element_header").each(function(){
			
			initSliderCheckboxes($(this).find(".accordion_active_checkbox_wrapper").find(".slider-checkbox"));
			
			$(this).unbind().click(function(e){
                // don't expand/contract the accordion if the checkbox is being clicked
                if($(e.target).is('.merge-base-checkbox') || $(e.target).hasClass("no-accordion-action")){
                    return;
                }

				var content = $(this).parent().find(".accordion_element_content");

                if (content.css("display") != "block") {
					content.slideDown("fast");
					$(this).addClass("selectedRow");
				} else {
                    content.slideUp("fast");
                    $(this).removeClass("selectedRow");
                }

			});

		});

	}
	else {
		accordion.parent().parent().find(".hide-when-no-accordion").hide();
		accordion.parent().parent().find(".show-when-no-accordion").fadeIn();
	}
}

/* ----------------------------------------------------------------------------------------------------------------------------------
* Will hide/show the "no results" message after each search attempt
* 	If it has found nothing, append a message, else remove the message
* ----------------------------------------------------------------------------------------------------------------------------------
*/
var toggleNoResultsMsg = function(accordion, msg)
{
	accordion.find(".error").remove();
	var count = 0;
	accordion.find(".accordion_element").each(function(){if ($(this).css("display") != "none") {count++;}});
	if (count == 0) {accordion.append("<div class='error'>" + msg + "</div>");accordion.find(".error").fadeIn();}
	
} // end toggleNoResultsMsg()


var initExpandCollapseAllLinks = function(expand_trigger, collapse_trigger, accordion)
{
	expand_trigger.click(function(e){
		e.preventDefault();
		accordion.find(".accordion_element_header").each(function(){
			// make sure the row is visible
			if ($(this).parent().css("display") == "block") {
				if ($(this).parent().find(".accordion_element_content").css("display") != "block") {
					$(this).trigger("click");
				}
			}
		});
	});
	
	collapse_trigger.click(function(e){
		e.preventDefault();
		accordion.find(".accordion_element_header").each(function(){
			// make sure the row is visible
			if ($(this).parent().css("display") == "block") {
				if ($(this).parent().find(".accordion_element_content").css("display") == "block") {
					$(this).trigger("click");
				}
			}
		});
	});
}

var initActivateDeactiveAllLinks = function(activate_trigger, deactivate_trigger, accordion)
{
	activate_trigger.click(function(e){
		e.preventDefault();
		accordion.find(".slider-button").each(function(){
			// make sure the row is visible
			if ($(this).parents(".accordion_element").css("display") == "block") {
				if (!$(this).hasClass("on")) {
					$(this).trigger("click");
				}
			}
		});
	});
	
	deactivate_trigger.click(function(e){
		e.preventDefault();
		accordion.find(".slider-button").each(function(){
			// make sure the row is visible
			if ($(this).parents(".accordion_element").css("display") == "block") {
				if ($(this).hasClass("on")) {
					$(this).trigger("click");
				}
			}
		});
	});
}


function processSiteCancel() {
	$("#toggle-site-active").hide();
	$("#simple-info").slideDown();
	$("#edit-form").slideUp(400, function(){
		$("#toggle-site-active").show();
		// reset the form
		$("#siteInfoForm")[0].reset();
	
		// update the state chosen
		// THIS IS A TOTALLY HACKY WAY TO DO THIS, sorry... neither liszt:updated nor chosen:updated would work
		var state = $("#state").val();
		$("#state_chzn").find(".active-result").each(function(){
			$(this).removeClass("result-selected");
			if ($(this).attr("data-option-val") == state) {
				$(this).addClass("result-selected");
				$("#state_chzn .chzn-single span").text($(this).text());
			}
		});
	});
}

function processSiteSave(siteId) {
	location.reload();
}

var getThrobber = function(id) {
	return "<img src='/images/throbber_small.gif' id='" + id + "'>";
}



var getCurrentSiteId = function()
{
	return  $("#site-form-wrapper").attr("data-siteid");
}
