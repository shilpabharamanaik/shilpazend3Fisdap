//Javascript for Fisdap_Form_Element_Groups 

$(document).ready(function(){
	
	setStyles();
	var allSectionOptions = [];
	
	// first save all of the options
	$("#edit_groups-id option").each(function(){
		if($(this).val() != 0){
			allSectionOptions.push({
				id: $(this).val(),
				label: $(this).text()
				});
		}
	});
	
	emptySections();
	addToSections(allSectionOptions);
	
	$("#edit_groups-year").change(function(){
		$("#groupsReset").slideDown();
		
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

		$("#edit_groups-id").stop().css("background-color", "#d2ecf2")
		.animate({ backgroundColor: "#fff"}, 1500);
		
	});
	
	function emptySections(){
		$("#edit_groups-id").find('option').each(function(){
			if($(this).val() != 0){
				$(this).remove();
			}
		});
	}
	
	function addToSections(sectionsToAdd){
		$.each(sectionsToAdd, function(index, item) {
			$("#edit_groups-id").append('<option value="' + item['id'] + '">' + item['label'] + '</option>');
		});
	}
	
	function setStyles()
	{
		$("#edit_groups-id").css("borderTopWidth", "1px");
		$("#edit_groups-id").css("borderColor", "#ddd");
		$("#edit_groups-year").css("borderTopWidth", "1px");
		$("#edit_groups-year").css("borderColor", "#ddd");
		$("#edit_groups-id").css("width", "250px");
	}
});