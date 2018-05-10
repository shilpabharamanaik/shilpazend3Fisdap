//Javascript for Fisdap_Form_Element_Groups 

$(document).ready(function(){
	
	setStyles();
	var allSectionOptions = [];
	
	// first save all of the options
	$("#group-id option").each(function(){
		if($(this).val() != 0){
			allSectionOptions.push({
				id: $(this).val(),
				label: $(this).text()
				});
		}
	});
	
	emptySections();
	addToSections(allSectionOptions);
	
	$("#group-year").change(function(){
		// uppdate the section options
		var year = $(this).val();
		emptySections();
		var newOptions = [];
		if(year == 'all'){
			newOptions = allSectionOptions;
		}
		else {
			$.each(allSectionOptions, function(index, item) {
				var sectionYear = item['label'].substring(0, 4);
				if(sectionYear == year){
					newOptions.push({
						id: item['id'],
						label: item['label']
					});
				}
			});
		}
		addToSections(newOptions);

		$("#group-id").stop().css("background-color", "#d2ecf2")
		.animate({ backgroundColor: "#fff"}, 1500);
		
	});
	
	function emptySections(){
		$("#group-id").find('option').each(function(){
			if($(this).val() != 0){
				$(this).remove();
			}
		});
	}
	
	function addToSections(sectionsToAdd){
		$.each(sectionsToAdd, function(index, item) {
			$("#group-id").append('<option value="' + item['id'] + '">' + item['label'] + '</option>');
		});
	}
	
	function setStyles()
	{
		$("#group-id").css("borderTopWidth", "1px");
		$("#group-id").css("borderBottomWidth", "1px");
		$("#group-id").css("borderRightWidth", "1px");
		$("#group-id").css("borderLeftWidth", "1px");
		$("#group-id").css("borderColor", "#ddd");
		
		$("#group-year").css("borderTopWidth", "1px");
		$("#group-year").css("borderBottomWidth", "1px");
		$("#group-year").css("borderRightWidth", "1px");
		$("#group-year").css("borderLeftWidth", "1px");
		$("#group-year").css("borderColor", "#ddd");
		
		$("#group-id").css("width", "250px");
		
		if($.browser.msie){
			$("#group-year").css("marginRight", "5px");
			$("#groupsWrapper").css("marginTop", "0px");
		}
	}
});