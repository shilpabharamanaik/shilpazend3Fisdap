$(function(){
	var filter_type = "date-ranges-container";
	
	$("#date-ranges-container, #test-container, #contact-container").hide();
	

	
	$("#test-filters").fancyFilter({
		closeOnChange: true,
		clearFilters: true,
		onFilterSubmit: function(e){
			switch (filter_type) {
				case "date-ranges-container":
					var data = {
						"start_date": $("#start_date").val(),
						"end_date": $("#end_date").val()
					};
					break;
				case "test-container":
					var data = {
						"test_id": $("#test_id").val()	
					};
					break;
				case "contact-container":
					var data = {
						"contact_name": $("#contact_name").val()	
					};
					break;
				default:
					var data = "";
					break;
			}
			return $.post("/learning-center/index/filter-tests",
				data,
				function(response){
					$("#scheduled-test-list-container").html($(response).fadeIn(1000));
					updateText();
                                },
				"json").done();
                }
        });
	
	$("input#start_date, input#end_date").datepicker({});	

	if($("input#start_date").val() != "" || $("input#end_date").val() != ""){
		$("#date-ranges-container").fadeIn();
		$("input#filterOptions-date-ranges-container").attr("checked", "checked");
		updateText();
	}
	
	$("input[name='filterOptions']").click(function(){
		filter_type = $(this).val();
		
		//hide everyone else
		$("#date-ranges-container, #test-container, #contact-container").hide();
		
		id = $(this).val();
		//var manualOffset = { left : 230, top: 10 }; //$(this).parents("label").outerWidth();
		var parentDiv = $(this).closest('div');
		// we use a combination of the offset of the parent DIV (which is relative to the document) and the scrollTop() value of window (ie, the viewport)
		// because dialog()'s position is set relative to the viewport
		//$("#" + id).dialog("option", "position", [$(parentDiv).offset().left + manualOffset.left, $(parentDiv).offset().top + manualOffset.top - $(window).scrollTop()]);
		//$("#" + id).dialog("open");
		
		// open this guy
		$("#" + id).fadeIn();
	});
	
	
	function updateText()
	{
		var text = "";
		switch (filter_type) {
			case "date-ranges-container":
				if ($("#start_date").val()) {
					text += "Start Date - " + $("#start_date").val() + " ";
				}
				if ($("#end_date").val()) {
					text += "End Date - " + $("#end_date").val();
				}
				break;
			case "test-container":
				text = $("#test_id").find("option[value='" + $("#test_id").val() + "']").text();
				break;
			case "contact-container":
				text = $("#contact_name").find("option[value='" + $("#contact_name").val() + "']").text();
				break;
		}
		$("#filters-title-text").text("Filters: "+text);
	}

});
