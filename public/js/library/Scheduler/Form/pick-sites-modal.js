$(function(){
	
	$( "#pick-sites-modal" ).dialog({ modal: true,
								autoOpen: false,
								title: "Choose Sites",
								width: 800,
								open: function (){
									$("#save-site-modal").blur();
									// click button to add selected
									$("#add-selected").trigger('click');
								}
							});
});

function initPickSitesModal() {
	$("#picklist-control-buttons").find("a").button();
	$("#close-site-modal").button().blur();
	$("#save-site-modal").button().blur();
	
	// style for mac
	if (navigator.platform.indexOf('Mac') != -1) {
		$(".search-available-list input").css("width", "224px");
	}
	// style for windows firefox
	if ($.browser.mozilla){
		if (navigator.platform.indexOf('Mac') == -1) {
			$(".search-available-list").css("top", "34px");
		}
    }

    // if this is a mobile device, add special styling
    if (isWebkitMobile()) {
        $("#picklist-site-picker").addClass("mobile-multiselect");
        $(".mobile-multiselect select[multiple]").css({"height": "auto", "padding": "0.2em"});
        $("#picklist-control-buttons").css({"padding-top": 0});
    }

	
	// populate all-sites list to track filtering
	$('#available-list option').each(function(){
		$("#all-sites").append($(this).clone().removeAttr("selected"));            
	});
	
	// create buttonset
	$("#filter_sites").buttonset();
	$(".cupertino .ui-button").css('margin', '5px -3px');
	// buttonset functionality
	$(".ui-buttonset .ui-button").click(function() {
		var value = $(this).attr('for');
		var buttonset = $("#pdf_" + $(this).parent().attr('id'));
		$(buttonset).attr("value", value);
	});
	
	// filter the available sites by type
	$("#filter_sites .ui-button").click(function() {
		// clear the search box and the available list
		$(".search-list").val("");
		$('#available-list').find('option').remove();
		
		var filter = $(this).attr('for');
		if (filter == 'all') {
			$('#all-sites option').each(function(){
				addOption($(this));
			});
		}
		if (filter == 'clinical') {
			$('#all-sites option.clinical').each(function(){
				addOption($(this));           
			});
		}
		if (filter == 'field') {
			$('#all-sites option.field').each(function(){
				addOption($(this));
			});
		}
		if (filter == 'lab') {
			$('#all-sites option.lab').each(function(){
				addOption($(this));
			});
		}
	});
	
	function addOption(option) {
		var clone = option.clone();
		// disable this option if it's already on the chosen list
		$("#chosen-list").find("option").each(function(){
			if ($(this).val() == clone.val()) {
				disableOption(clone);
			}
		});
		$("#available-list").append(clone);
	}
	
	// fancy focus
	$("#picklist-site-picker select, #picklist-site-picker input").focus(function(){
		$(this).addClass("fancy-focus");
	});
	$("#picklist-site-picker select, #picklist-site-picker input").blur(function(){
		$(this).removeClass("fancy-focus");
	});
	
	// CANCEL
	$("#close-site-modal").click(function(e){
		e.preventDefault();
		$("#pick-sites-modal").dialog('close');
	});
	
	// CHOOSE
	$("#save-site-modal").click(function(e){
		
		e.preventDefault();
		var sites = {};
		var siteIds = '';
		
		$("#chosen-list").find("option").each(function(){
			sites[$(this).val()] = $(this).text();
			siteIds += $(this).val()+",";
		});
		
		// spit out the array
		//console.log(sites);
		
		// set the id input
		$("#pick-sites").attr("data-siteids", siteIds.substring(0,siteIds.length-1));
		
		$("#pick-sites-modal").dialog('close');
	});

	// searching stuff
	var sites = {};
	$("#available-list").find("option").each(function(){
		sites[$(this).val()+'_'+$(this).attr('class')] = $(this).text();
	});
	
	$(".search-list").keyup(function(){
		searchList($("#" + $(this).attr("data-listtosearch")), $(this).val());
	});
	
	function searchList(list, searchTerm) {
		list.find("option").each(function(){$(this).remove();});
		
		$.each(sites, function(i, value) {
			var siteInfo = i.split('_');
			var index = siteInfo[0];
			var type = siteInfo[1];
			var testedValue = value.toLowerCase();
			var testedSearchTerm = searchTerm.toLowerCase();
			
			if (testedValue.indexOf(testedSearchTerm) != -1){
				// add it
				list.append(createOption(index, value, type));
				$("#chosen-list").find("option").each(function(){
					if ($(this).attr("data-siteId") == index) {
						disableOption(list.find("option").last());
					}
				});
				
				list.find("option").sort(sortList).appendTo(list);
			}
		});
	}
	
	
	// sort the available options alphabetically
	$("#available-list").find("option").sort(sortList).appendTo($("#available-list"));
	
	function sortList(a, b) {
		
		if (a.innerHTML == 'NA') {
			return 1;   
		}
		else if (b.innerHTML == 'NA') {
			return -1;   
		}       
		return (a.innerHTML > b.innerHTML) ? 1 : -1;
		
	}
	
	// move the sites from one list to another
	$("#picklist-control-buttons a").click(function(e){
		e.preventDefault();
		var action = $(this).attr("data-controlfunction");
		
		if (action == "add" || action == "addAll") {
			moveOptions(action, $("#available-list"), $("#chosen-list"));
		}
		else if (action == "remove" || action == "removeAll") {
			moveOptions(action, $("#chosen-list"), $("#available-list"));
		}
		
	});
	
	function disableOption(option) {
		option.attr("disabled", "disabled").addClass("disabled");
	}
	
	function createOption(val, label, type) {
		return "<option class='" + type + "' value='" + val + "' data-siteId='" + val + "'>" + label + "</option>";
	}

	function moveOptions(action, fromList, toList) {
		fromList.find("option").each(function(){
			
			var addThis = false;
			if (action.indexOf("All") != -1 && $(this).attr("disabled") != "disabled") {addThis = true;}
			if ($(this).attr("selected")) {addThis = true;}
			
			if (addThis) {
				
				if (action.indexOf("add") != -1) {
					toList.append(createOption($(this).attr("data-siteId"), $(this).text(), $(this).attr('class')));
					toList.find("option").sort(sortList).appendTo(toList);
					disableOption($(this));
				}
				else {
					var changingOption = $(this);
					
					toList.find("option").each(function(){
						if (changingOption.attr("data-siteId") == $(this).attr("data-siteId")) {
							$(this).attr("disabled", false).removeClass("disabled");
						}
						
					});
					
					changingOption.remove();
				}
			}
		});
		
		toList.find("option:selected").removeAttr("selected");
		fromList.find("option:selected").removeAttr("selected");
		updateCount();
	}
	
	// update the count of chosen options
	function updateCount() {
		var count = $("#chosen-list").find("option").length;
		if (count == 1) {
			var phrase = " site selected";
		} else {
			var phrase = " sites selected";
		}
		$("#selected_count").html('<span id="num_selected">'+count+'</span>'+phrase);
	}
	
	// if they click on anything other than whats inside the picker, deselect any selected options
	$('html').click(function(e) {
		var target = e.target;
		if(!inPickerWrapper($(target))){
			$("select:not(.fancy-focus) option:selected").removeAttr("selected");
		}
	});
	
	// returns true if the element is within the active picker or the control buttons
	function inPickerWrapper(element){
		isInPickerWrapper = false;
		if(element.attr("id") == "picklist-control-buttons" ||
		   element.hasClass("fancy-focus")){
			isInPickerWrapper = true;
		}
		element.parents().each(function(){
			//console.log($(this));
			if($(this).attr("id") == "picklist-control-buttons" ||
			   $(this).hasClass("fancy-focus")){
				isInPickerWrapper = true;
			}
		});
		return false;
	}

}
