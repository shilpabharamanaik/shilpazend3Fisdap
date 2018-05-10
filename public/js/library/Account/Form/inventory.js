$(function(){
	$("#deleteGroup").click(function(e){
		if(getNumberOfCodesSelected() == 0){
			$("#noCodesSelectedError").slideDown();
		}
		else if(anyInstructorsSelected()){
			$("#instructorsSelectedError").slideDown();
		}
		else {
			$("#instructorsSelectedError").fadeOut();
			$("#cannotdeleteactivationcodeError").fadeOut();
			$("#noCodesSelectedError").fadeOut();
			if (confirm("Are you sure to delete the codes") == true) {
				var data = getGradGroupModalValues();
				var codes = getSelectedCodes();
				$.post(
					'/account/orders/delete-grad-groups',
					{ data: data,
					  codes: codes},
					function(response){
						if(response == 'activated'){						
							$("#cannotdeleteactivationcodeError").slideDown();
						}
						else{
							$("#successMessage").fadeIn();
							$("#successMessage").text("Your activation codes have been deleted.");
						}
						getNewList(getFormValues());
					}
				);
		}
		
		}
	});
    $("#searchButton").button();
    $("#email").button();
    $("#cert").button();
    $("#gradGroups").button();
    $("#sendButton").button();
    
	var inactive_groups;
	if ($("#group-optgroup-Inactive").length > 0) {
		inactive_groups = $("#group-optgroup-Inactive"); 
		$("#group-optgroup-Inactive").remove();
	}
	
	var active_groups;
	if ($("#group-optgroup-Active").length > 0) {
		active_groups = $("#group-optgroup-Active");
		$("#group-optgroup-Active").remove();
	}
	
	var any_group_option = $("#group").find("option[value='0']");
	$("#group").find("option[value='Any group']").remove();
	
	$("#group").append(any_group_option);
	$("#group").append(active_groups);
	$("#group").append(inactive_groups);
	$("#group").val("0");
	$("#group").chosen({disable_search_threshold: 5});
	
	$("#grad-month").chosen();
	$("#grad-year").chosen({disable_search_threshold: 8});
    
    // sort the product list
    if(!jQuery.browser.msie){
        $("#productsWrapper").find('.form-prompt').sort(sortAlpha).appendTo($("#productsWrapper"));
    }
    
    // set up some things that will help with the different search modes
    var searchingBy = 'filters';
    
    // load the initial inventory list
    getNewList(getFormValues());
    
    $("#checkAll").click(function(e){
        e.preventDefault();
       if($(this).text() == "Check all"){
            $(this).text("Uncheck all");
            $("#productsWrapper").find("input").each(function(){
                $(this).attr("checked", "checked");
            });
       }
       else {
            $(this).text("Check all");
            $("#productsWrapper").find("input").each(function(){
                $(this).removeAttr("checked");
            });
       }
    });
    
    // when the user clicks the search button
    // don't actually submit it, and get a new list with the form values
    $("#searchButton").click(function(e){
        e.preventDefault();
        clearSuccessMessages();
        clearErrorMessages();
        getNewList(getFormValues());
    });

    // when the user clicks the "search by filters" trigger
    $("#byFiltersTrigger").click(function(e){
        e.preventDefault();
        clearSuccessMessages();
        if(searchingBy != 'filters'){
            // open this
            openSearchMode($(this), $("#byFilters"), 'filters');
            // close the other guy
            closeSearchMode($("#byCodesTrigger"), $("#byCodes"));
        }
    })
    
    // when the user clicks the "search by specific activation codes" trigger
    $("#byCodesTrigger").click(function(e){
        clearSuccessMessages();
        e.preventDefault();
        if(searchingBy != 'codes'){
            // open this
            openSearchMode($(this), $("#byCodes"), 'codes');
            // close the other guy
            closeSearchMode($("#byFiltersTrigger"), $("#byFilters"));
        }
    })
    
    // opens a "search mode" div
    function openSearchMode(trigger, divToOpen, nowSearchingBy)
    {
        trigger.find("img").remove();
        trigger.append("<img id='open' src='/images/arrow_down.png'>");
        divToOpen.slideDown();
        searchingBy = nowSearchingBy;
    }
    
    // closes a "search mode" div
    function closeSearchMode(trigger, divToClose)
    {
        trigger.find("img").remove();
        trigger.append("<img id='closed' src='/images/arrow_left.png'>");
        divToClose.slideUp();
    }
    
    // if there are no errors in the email modal
    function noModalErrorsPresent()
	{
		validData = true;
		$("#emailDialog").find("tr").each(function(){
			var email = $(this).find("input").val();
			if(!validateEmail(email)){
				validData = false;
			}
		});
		
		return validData;
	}
	
    // get the email modal form values
	function getModalFormValues()
	{
		var data = [];
		var count = 0;
		$("#emailDialog").find("tr").each(function(){
			var email = $(this).find("input").val();
			var sn = $(this).find("input").attr("id");
			data[count] = {
				email: email,
				sn: sn
			}; 
			count++;
        });
        return data;
    }
		
    // the send button function for the email modal
    // (the rest of these functions are in a separate js file)
    // this function is here so it can update the list after submitting
    $("#sendButton").click(function(e){
         clearSuccessMessages();
         clearErrorMessages();
		$("#emailErrors").slideUp();
		e.preventDefault();
		$(this).hide();
		$("#sendButtonThrobber").show();
		if(noModalErrorsPresent()){
			var data = getModalFormValues();
			var message = $("#message").val();
			$.post(
            '/account/orders/send-emails',
            { data: data,
			  message: message},
            function(response){
				$("#emailDialog").dialog('close');
                getNewList(getFormValues());
                $("#successMessage").fadeIn();
                $("#successMessage").text("Your activation codes have been emailed.");
            }
			);
		}
		else {
			$("#emailErrors").slideDown();
			$(this).show();
			$("#sendButtonThrobber").hide();
		}
	});
    
    // the update button function for the edit grad/groups modal
    // (the rest of tehse functions are in a separate js file)
    // this function is here so it can update the list after submitting
    $("#updateGradGroup").click(function(e){
       e.preventDefault();
        clearSuccessMessages();
        clearErrorMessages();
       $(this).hide();
       $("#editGradGroupButtonThrobber").find("img").show();
       var data = getGradGroupModalValues();
       var codes = getSelectedCodes();
        $.post(
            '/account/orders/update-grad-groups',
            { data: data,
			  codes: codes},
            function(response){
				$("#gradGroupDialog").dialog('close');
                getNewList(getFormValues());
                $("#successMessage").fadeIn();
                $("#successMessage").text("Your activation codes have been updated.");
            }
		);
    });
    
    function clearSuccessMessages(){
        $("#successMessage").fadeOut();
    }
    
    function clearErrorMessages(){
        $("#activeCodesSelectedError").fadeOut();
        $("#noCodesSelectedError").fadeOut();
        $("#instructorsSelectedError").fadeOut();
        $("#cannotdeleteactivationcodeError").fadeOut();
    }
    
    function getGradGroupModalValues(){        
        if($("#editGroupsWrapper").css("display") == "block"){
            var sectionId = $("#edit_groups-id").val();
        }
        else {
            var sectionId = "do-not-change";
        }
        
        if($("#editGradWrapper").css("display") == "block"){
            var gradMonth = $("#edit_grad-month").val();
            var gradYear = $("#edit_grad-year").val();
        }
        else {
            var gradMonth = "do-not-change";
            var gradYear = "do-not-change";
        }
        
        var data = {
            sectionId: sectionId,
            gradMonth: gradMonth,
            gradYear: gradYear
        }
        
        return data;
    }
    
    function getSelectedCodes(){
        var codes = [];
        $("tr").each(function(){
			var checkbox = $(this).find("input:checkbox");
			if(checkbox.is(":checked")){
				codes.push(checkbox.val());
			}
		});
        return codes;
    }

    // perform the ajax request to get a new list
    // then append the results to the inventory list
    function getNewList(formValues)
    {
        $("#inventoryList").fadeOut();
        $("#inventoryList").empty();
        $("#throbber").show();
        $.post(
            '/account/orders/get-inventory',
            { formValues: formValues },
            function(response){
                $("#inventoryList").append(response);
                $("#inventoryList").slideDown();
                $("#throbber").hide();
                setRowFunctions();
                alignCells();
            }
        );
    }

    // steps through our form and returns an object with the values
    function getFormValues(){
        var lines = "";
        // if we're searching by activatin codes get the codes in the text area
        if(searchingBy == 'codes'){
            var lines = $("textarea#code").val().replace(/\r\n/g, "\n").split("\n");
        }
        var formValues = {
            productConfig: addUpProductConfig(),
            dateBegin: $("#dateBegin").val(),
            dateEnd: $("#dateEnd").val(),
            gradMonth: $("#grad-month").val(),
            gradYear: $("#grad-year").val(),
            section: $("#group").val(),
            certLevels: getCertLevels(),
			available: $("#available").is(":checked")?1:0,
            activated: $("#activated").is(":checked")?1:0,
            distributed: $("#distributed").is(":checked")?1:0,
            codes: lines
        };
        return formValues;
    }
    
    // add up the total product configuration by finding those checkboxes selected,
    // grabbing their id and adding it to a total value that we will return
    function addUpProductConfig()
    {
        var total = 0;
        $("#productsWrapper").find("input:checkbox").each(function(){
            if($(this).is(":checked")){
                var config = $(this).attr("id").split('_');
                var value = parseInt(config[1]);
                if(value){total += value;}
            }
        });
        return total;
    }
    
    // grab the certification levels selected
    function getCertLevels()
    {
        var certLevelsArray = new Array();
        $("#certLevelsWrapper").find("input:checkbox").each(function(){
            if($(this).is(":checked")){
                var id = $(this).attr("id").split('_');
                // becuase zend is silly sometimes, we need to pay attention to cases that had a dash in the cert name
                var value = id[1];
                if(id.length > 2){
                    value += "-" + id[2];
                }
                if(value){
                    // we found a checked box, add it to our array of cert levels
                    certLevelsArray.push(value);
                }
            }
        });
        return certLevelsArray;
    }
    
    // the hover/onclick functions for a row in the activatin code table
    function setRowFunctions(){
        var normalBg = "#fff";
        var selectedColor = "#eee";
        var hoverColor = "#eee";

        $("tr").each(function(){
            var checkbox = $(this).find("input:checkbox");

            checkbox.click(function(){
                clearSuccessMessages();
                if($(this).is(":checked")){$(this).removeAttr('checked');}
                else {$(this).attr('checked', 'checked');}
            });

           $(this).click(function(){
                clearSuccessMessages();
                if(checkbox.is(":checked")){
                    checkbox.removeAttr('checked');
                    $(this).css("background-color", normalBg);
                }
                else {
                    checkbox.attr('checked', 'checked');
                    $(this).css("background-color", selectedColor);
                }
           });
           
           $(this).hover(function(){$(this).css("background-color", hoverColor);},
           function(){
                if(!checkbox.is(":checked")){$(this).css("background-color", normalBg);}
                else {$(this).css("background-color", selectedColor);}
           });
        });
    }
    
    // validates an email address format
    function validateEmail(email) { 
		var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test(email);
	}
    
    // funny little sort for the product list
    // IE returns an object instead of an array from the split() function- need to handle it a bit differently.
    // If it's an array, do the same thing it did previously- otherwise cast the split object to a string and resplit that.
    function sortAlpha(a,b){
    	var pnamea = a.innerHTML.toLowerCase().split('class="optional">');
    	        
        if(pnamea[1]){
        	var sortA = pnamea[1].split('</label>');
        }else{
        	var sortA = String(pnamea).split('</label>');
        }
        
        var pnameb = b.innerHTML.toLowerCase().split('class="optional">');
        if(pnameb[1]){
        	var sortB = pnameb[1].split('</label>');
        }else{
        	var sortB = String(pnameb).split('</label>');
        }

        if(sortA[0] && sortB[0]){
        	return sortA[0] > sortB[0] ? 1 : -1;
        }else{
        	return sortA > sortB ? 1 : -1;
        }  
    };
    
    // do some quircky css fixes so that the aboslutely positioned table header has the same cell widths as the actual table
    function alignCells()
    {
        var cells = [];
        var count = 0;
        
        $("#code-table").find("tr:first").find("td").each(function(){
            cells[count] = $(this).width();
            count++;
        });

        cells[0] -= 1;  // checkbox
        cells[1] -= 3;  // number
        cells[2] -= 4;  // products
        cells[3] -= 2;  // available
        cells[4] -= 2;  // cert
        cells[5] -= 2;  // advanced
        cells[5] -= 1;  // order
        
        count = 0;
        var tdCount = 0;
        $("#theTopRow").find("tr:first").find("td").each(function(){
            $(this).css("width", cells[count]+"px");
            count++;
        });
    }
    function getNumberOfCodesSelected() {
		var codes = [];
		$("tr").each(function(){
			var checkbox = $(this).find("input:checkbox");
			if(checkbox.is(":checked")){
				codes.push(checkbox.val());
			}
		});
		
		return codes.length;
	}
    function anyInstructorsSelected() {
		var instructors = false;
		$("tr").each(function(){
			var checkbox = $(this).find("input:checkbox");

			if(checkbox.is(":checked")){
				var cert;
				var count = 0;
				$(this).find("td").each(function(){
					count++;
					if(count == 5){
						if($(this).text() == 'Instructor'){
							instructors = true;
						}
					}
				});
			}
		});
		
		return instructors;
	}

});
