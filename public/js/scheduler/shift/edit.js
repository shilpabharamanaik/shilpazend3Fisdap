$(function(){
	if ($("#windows-table").length == 0) {
		$("h1").after("<div class='error'>You do not have permission to edit this shift. <a href='/scheduler'>Return to the calendar</a></div>");
	}
});
