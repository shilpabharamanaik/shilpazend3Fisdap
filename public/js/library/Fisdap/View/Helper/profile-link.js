$(function(){

    function headerSearchExpand() {
        var mglass = $('.magnifying-glass');
        var form = $('.header-search-box');
        mglass.click(function() {
            if (form.is(':hidden')) {
                form.css({"padding-left":"3px"});
                form.show().focus();
            }
            form.animate({
                'width': form.width() == 100 ? '0px' : '100px'
            }, 'fast', function() {

                if (form.width() == 0) {
                    form.hide();
                }
            });
        });
    }

	$('#staff_program').chosen();
	
	$("#staff_program").change(function(){
		blockUi(true);
		$.post("/ajax/change-program",
			   {'id': $(this).val()},
			   function(response){
				location.reload();
				},
				"json"
		);
	});

	$("#staff-settings").click(function(e){
		e.preventDefault();
		$("#staff-links").slideToggle();
	});

    headerSearchExpand();


    /**** Notification Center Popup Code ****/

    var nContainer = $(".notification-popup-container");
    var nDelete = $("a.notification-popup-delete");

    //open user-entered hyperlinks in a new tab
    $(".notification-popup-container-main").on("click", "div.notification-popup-message > a", function(e) {
        e.preventDefault();
        window.open(this.href);
    });

    //notification popup fade in on page load
    $(function() {
        nContainer.fadeIn("slow");
    });

    //delete popup on click of X, post to controller action
    nDelete.click(function(e) {
        e.preventDefault();
        var nViewedId = $(this).data("userviewid");
        $(this).closest(nContainer).remove();
        $.post("/ajax/mark-notification-viewed",
            {'userViewId': nViewedId},
            function(response) {
                if (response != true) {
                    blockUi(true);
                    window.reload();
                }
            },
            "json"
        );
    });


});

function postSearchForm(){

    if( $('#search-string').val()) {
        $.post("/account/edit/set-student-search-session",
            {'search-string': $('#search-string').val()},
            function (response) {
                window.location = "/account/edit/student-search";
            },
            "json"
        );
    }
}