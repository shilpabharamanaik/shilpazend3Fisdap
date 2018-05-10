$(function(){
	$("#retrieve").button();
	$("#errorWrapper").hide();
	$("#retrieveThrobber").hide();
	
	$.browser.chrome = /chrome/.test(navigator.userAgent.toLowerCase()); 

	if(!$.browser.chrome){
		$("#retrieveThrobber").css("margin-top", "0em");
	}

	
	var currentlyOpen;
	
	//If we have preset tests, block the UI, the student picker will unblock the UI
	if ($("#test_id").val() && $("#test_id").val().length) {
		blockUi(true);
	}
	
	// initialize the select box element thing
	initTestAccordian();
	
	function initTestAccordian(){
		$("select#test_id").find("optgroup").each(function(){
			var output = "<div class='section'>";
			output += "<div class='accordianHeader'><div class='imgWrapper'><img src='/images/icons/plus_Gray.png'></div>";
			output += this.label;
			output += "</div>";
			output += "<div class='accordianOptions'>";
			
			$(this).find("option").each(function(){
				output += "<div class='option' id='" + $(this).val() + "'>";
				output += this.label;
				output += "</div>";
			});
			
			output += "</div>";
			
			$("#testAccordian").append(output);
		});
	}
	
	$(".accordianOptions").each(function(){
		var numberOfOptions = 0;
		$(this).find(".option").each(function(){
			numberOfOptions++;
		});
		var height = numberOfOptions * 25;
		$(this).css("height", height + "px");
	});

	$(".accordianHeader").each(function(){
		$(this).click(function(){
			if(currentlyOpen){
				closeCurrent();

				// it's not us!
				if(currentlyOpen.text() != $(this).text()){
					openCategory($(this));
				}
				else {
					// we've closed ourself, set currently open to null
					currentlyOpen = null;
				}
			}
			else {
				openCategory($(this));
			}
		})
	});
	
	function updateHiddenSelect(value, remove){
		$("select#test_id").find("option").each(function(){
			if($(this).attr("value") == value){
				if(remove){
					$(this).attr("selected", false);
				}
				else {
					$(this).attr("selected", true);
				}
			}
		});
	}
	

	function updateLanguage(){
		$("#currentlySelectedTests").empty();
		var newText = "";
		if($("select#test_id").find("option:selected").length != 0){
			newText = "Currently selected tests (<button class='clearAllButton' id='clearAll'>clear all</button>): ";
			var count = 1;
			$("select#test_id").find("option:selected").each(function(){
				if(count != 1){
					newText += ", ";
				}
				
				newText += $(this).text();
				
				count++;
			});
		}
		else {
			newText = "No tests have been selected yet. Use the box above to select one or more.";
		}
		
		$("#currentlySelectedTests").append(newText);
		
		$("#clearAll").click(function(e){
			e.preventDefault();
			$(".option").each(function(){
				deselect($(this));
			});
			closeCurrent();
		});
	

	}
	
	function deselect(option){
		option.removeClass("selectedOption");
		option.css("background-color", "#fff");
		updateHiddenSelect(option.attr("id"), true);
		updateLanguage();
	}
	
	$(".option").each(function(){
		$(this).click(function(){
			if($(this).hasClass("selectedOption")){
				deselect($(this));
			}
			else {
				$(this).addClass("selectedOption");
				$(this).css("background-color", "#eee");
				updateHiddenSelect($(this).attr("id"), false);
				updateLanguage();
			}
		});
		
		$(this).hover(function(){
			if(!$(this).hasClass("selectedOption")){
				$(this).css("background-color", "#eee");
			}
			
		}, function(){
			if(!$(this).hasClass("selectedOption")){
				$(this).css("background-color", "#fff");
			}
		});
	});
	
	function openCategory(category){
		currentlyOpen = category;
		category.addClass("selectedCategory");
		category.parent().find(".accordianOptions").slideDown();
		category.find(".imgWrapper").empty();
		category.find(".imgWrapper").append("<img src='/images/icons/minus_Gray.png'>");
	}
	
	function closeCategory(category){
		category.removeClass("selectedCategory");
		category.parent().find(".accordianOptions").slideUp();
		category.find(".imgWrapper").empty();
		category.find(".imgWrapper").append("<img src='/images/icons/plus_Gray.png'>");
	}
	
	function closeCurrent() {
		if(currentlyOpen){
			closeCategory(currentlyOpen);
		}
	}

	$("#retrieve").click(function(e){
		$("#retrieveThrobber").show();
		
		$("#errorWrapper").hide();
		$("#errorWrapper").empty();
		$("#test_results").slideUp();
		
		var tests = false;
		var students = false;

		// make sure we have at least one exam

		if($("select#test_id").find("option:selected").length != 0){
				// something is selected
				tests = true;
		}

		if(msp_canViewStudentNames){
			// make sure we have at least one student selected
			$("#msp_student_list_container").find("input").each(function(){
				if($(this).attr("checked")){
					students = true;
				}
			});
		}else{
			if($('#msp_student_count').val() > 0){
				students = true;
			}
		}
		
		if(!tests || !students){
			$("#errorWrapper").append("Please select a");

			if(!tests){
				$("#errorWrapper").append(" test");
			}
			
			if(!students){
				if(!tests){
					$("#errorWrapper").append(" and a");
				}
				$("#errorWrapper").append(" student");
			}
			
			$("#errorWrapper").append(" from the list above.");
			
			$("#errorWrapper").fadeIn();
			$("#retrieveThrobber").hide();

			e.preventDefault();
		}
	});
	

});

function checkForPresetStudent() {
	if ($("#test_id").val() && $("#test_id").val().length) {
		$("#test_id").val().forEach(function(el) {
			$("#" + el).click();
		});
		$("#retrieve").click();
	}
}

