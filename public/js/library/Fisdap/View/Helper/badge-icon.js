$(function() {
	// any icon anchor with a class of "no-click" should do nothing
    $("a.no-click").click(function(e) {
        e.preventDefault();
	});	

});