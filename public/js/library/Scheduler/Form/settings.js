$(function() {

    initPage();

    //Grab the form values when the page first loads
	var savedFormValues = $("form").serialize();

	window.onbeforeunload = function (e) {
		//Check to see if the original values match the current ones
		if (savedFormValues != $("form").serialize()) {
			var message = "You have unsaved changes on the form.",
			e = e || window.event;
			// For IE and Firefox
			if (e) {
			  e.returnValue = message;
			}

		  // For Safari
		  return message;
		}
	};

	function initPage(){
		initFlippyDivs();

        $("#sendStudentNotifications").sliderCheckbox({});

		// clicking the save button shouldn't submit the form, should initiate autosave
		$("#save-button").click(function(e){
			e.preventDefault();
			autosave();
		});
	}

	function autosave(){
		var throbber = $("<img src='/images/throbber_small.gif' class='throbber'>");
		$('#save-button').after(throbber);
		$.post(
			'/scheduler/settings/autosave',
			{ form:  $("#settings-wrapper :input").serializeArray() },
			function(response){
				//Re-grab the form values in JS now that they're saved in the DB
				savedFormValues = $("form").serialize();

				var notice = '<div class="success" style="width:95%">'+response+'</div>';
				//$('#control-buttons').css({ 'margin-bottom' : '0px'} );
				$('#settings-wrapper').find(".island").prepend(notice);
				throbber.hide();
				$('#save-button').hide();
				$('.success').fadeIn();

                // scroll the page to the top
                $('html,body').animate({scrollTop: $(".success").first().offset().top-55},'slow');

                setTimeout(function(){

                    show_save_button();

                }, 3000);

            }
		);
	}

});


var show_save_button = function()
{
    $('.success').slideUp("slow").delay(500).remove();
    $('#save-button').fadeIn();
};