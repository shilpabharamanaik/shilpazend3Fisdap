$(function(){
	var isEditing = false;
	
	var toggleEditing = function(){
		isEditing = !isEditing;
		
		if(isEditing){
			// Remove the paragraph holding the description, and add in the
			// text area and save button
			$('#student_description_display').hide();
			$('#student_description_display_edit').show();
			$('#edit_description_link').hide();
		}else{
			$('#student_description_display').show();
			$('#student_description_display_edit').hide();
			$('#edit_description_link').show();
		}
		
		return false;
	}
	
	$('#edit_description_link').click(toggleEditing);
	
	$('#save_description_button').click(function(){
		// Make the ajax call, toggle display on success...
		$.post(
			'/portfolio/index/save-description', 
			{description: $('#student_description').val()}, 
			function(){
				$('#student_description_display').text($('#student_description').val());
				toggleEditing();
			}
		);
	});
	
	// Always hide the editing tools first...
	$('#student_description_display_edit').hide();
});